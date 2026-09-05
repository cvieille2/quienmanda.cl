<?php

namespace App\Http\Controllers;

use App\Models\PaymentGatewayEvent;
use App\Models\Profile;
use App\Models\SupportTransaction;
use App\Services\PaymentApprovalService;
use App\Services\PaymentLimitsService;
use App\Services\Payments\MercadoPagoGateway;
use App\Services\RankingPeriodService;
use App\Services\SupportTransactionService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private MercadoPagoGateway $gw,
        private PaymentLimitsService $limits,
        private PaymentApprovalService $approval,
        private SupportTransactionService $transactions,
        private RankingPeriodService $periods,
    ) {}

    public function start(Request $request)
    {
        $request->validate([
            'profile_id'      => ['required', 'exists:profiles,id'],
            'amount_clp'      => ['required', 'integer', 'min:1'], // rango real: configuration CONGELADA del periodo
            'supporter_name'  => ['nullable', 'string', 'max:64'],
            'is_anonymous'    => ['nullable', 'boolean'],
            // Personas v1.2.0: edad mínima del pagador = 18 (declaración checkbox).
            'age_declared_18' => ['required', 'accepted'],
            'checkout_token'  => ['required', 'string', 'min:8'], // CSRF/anti-CSRF propio
        ]);

        $profile = Profile::findOrFail($request->profile_id);
        $amount  = (int) $request->amount_clp;

        // (v4) FINANCIAL KILL SWITCH + feature flag: si payments_enabled=false, NO se crean
        // nuevos checkouts (gobernanza v1.0.0, cumple NO_GO_004). Se verifica en runtime.
        if (! app(\App\Services\FeatureFlagsService::class)->isEnabled('payments_enabled')) {
            return response()->json(['error' => 'checkout_disabled'], 503);
        }

        // Suspensión: el checkout debe RE-CHAZAR antes de crear la orden.
        if ($profile->status !== Profile::STATUS_ACTIVE) {
            return response()->json(['error' => 'profile_not_available'], 422);
        }

        // Monto contra los límites de la configuration CONGELADA del periodo activo (D-005/D-048);
        // nunca se hardcodea min/max configurables (CHECK en BD solo amount_clp > 0).
        $this->transactions->assertAmountWithinLimits($amount, $this->periods->activePeriod());

        // Identidad por hashes + umbral diario (anti-fraude) antes de crear la orden.
        $identity = $this->limits->resolvePayerIdentity($request);
        $this->limits->assertCanCheckout($request, $amount);

        $tx = $this->transactions->createCheckout(
            $profile->id,
            $amount,
            $identity,
            [
                'supporter_name' => $this->sanitizeSupporterName($request->input('supporter_name')),
                'is_anonymous'   => (bool) $request->boolean('is_anonymous', true),
            ]
        );

        $txw = $this->gw->create($amount, $tx->external_reference, $tx->id . '-' . uniqid(), [
            'subject' => $profile->display_name,
        ]);
        $tx->update(['provider_transaction_id' => $txw['token']]);

        audit('payment_created', 'support_transaction', $tx->id, ['amount_clp' => $amount, 'profile_id' => $profile->id]);

        return response()->json(['checkout_url' => $txw['url'], 'external_reference' => $tx->external_reference]);
    }

    /** Retorno (confirma la creación del checkout; la aprobación definitiva es el webhook). */
    public function return(Request $request)
    {
        $paymentId = $request->input('payment_id'); // id de pago de Mercado Pago (retorno frontend)
        $tx = SupportTransaction::where('provider_transaction_id', $paymentId)->firstOrFail();
        // No marca approved aquí. Solo informamos "Pago en proceso de confirmación".
        return redirect()->route('payments.pending', ['slug' => $tx->profile->slug]);
    }

    /**
     * Webhook server-to-server: ÚNICA fuente de confirmación definitiva.
     * Orden obligatorio (Master Spec + regla de idempotencia):
     *   1) registrar el evento crudo en payment_gateway_events (append-only) con payload_hash
     *   2) si ya se procesó con el mismo gateway_event_id/transaction_id -> responder 200 SIN duplicar
     *   3) recién entonces delegar a PaymentApprovalService::approve()
     */
    public function webhook(Request $request)
    {
        $raw          = $request->getContent();                       // body crudo JSON
        $payloadHash  = hash('sha256', $raw);
        // Mercado Pago envía una notificación con data.id = id de la operación (pago u orden).
        // El TIPO de notificación/topic es configurable por .env (MP_WEBHOOK_TOPIC) porque depende
        // del producto MP confirmado en TASK-001 (DECISION_REQUIRED-003): payment | merchant_order | orders.
        $notification = $request->json();
        $gatewayEvent = (string) ($notification['data']['id'] ?? ''); // payment_id / merchant_order_id según topic
        $gatewayEvent = $gatewayEvent ?: (string) ($notification['id'] ?? '');
        $topic        = (string) ($notification['type'] ?? $request->input('topic') ?? '');

        // Topics que MANEJAN el flujo de aprobación. Se comparan contra la lista configurada.
        $handledTopics = config('mercadopago.webhook_topics', ['payment', 'merchant_order']);
        // Si el body no trae 'type', se acepta el config primary como válido (defensivo).
        $matched = $topic !== '' ? in_array($topic, $handledTopics, true) : true;

        if (! $matched) {
            // Otro tipo de notificación: registrar y responder OK sin procesar (auditoría).
            PaymentGatewayEvent::firstOrCreate(
                ['payment_gateway' => 'mercadopago', 'gateway_event_id' => 'unhandled:' . $gatewayEvent . ':' . ($topic ?: '')],
                [
                    'gateway_transaction_id' => $gatewayEvent,
                    'event_type'             => 'topic_unhandled',
                    'payload_hash'           => $payloadHash,
                    'received_at'            => now(),
                    'processed_at'           => now(),
                    'processing_result'      => 'ignored',
                ]
            );
            return response('OK', 200);
        }

        // (v4) KILL SWITCH: si payments_enabled=false no se procesa funcionalmente ningún pago nuevo.
        // El registro append-only igual se guarda para auditoría.
        $killSwitchDown = ! app(\App\Services\FeatureFlagsService::class)->isEnabled('payments_enabled');

        // 1) Registro append-only del evento crudo (auditoría + base de idempotencia).
        $gwEvent = PaymentGatewayEvent::firstOrCreate(
            ['payment_gateway' => 'mercadopago', 'gateway_event_id' => $gatewayEvent],
            [
                'gateway_transaction_id' => $gatewayEvent,
                'external_reference'     => $notification['external_reference'] ?? null,
                'event_type'             => 'notification',
                'payload_hash'           => $payloadHash,
                'received_at'            => now(),
            ]
        );

        // 2) Idempotencia: si el evento ya se procesó, responder 200 sin duplicar.
        if ($gwEvent->processed_at !== null || $gwEvent->processing_result === 'processed') {
            return response('OK', 200); // duplicado: no reprocesar ni volver a sumar al ranking
        }

        // NOTA TASK-001 (DECISION_REQUIRED-003): si el topic confirmado es 'merchant_order'/'orders',
        // $gatewayEvent = merchant_order id y hay que resolver los payment ids vía
        // GET /merchant_orders/{id} (colección payment_ids) antes de buscar SupportTransaction.
        $tx = SupportTransaction::where('provider_transaction_id', $gatewayEvent)->first();
        if (! $tx) {
            $gwEvent->update(['processing_result' => 'ignored', 'processed_at' => now()]);
            return response('', 404);
        }

        // (v4) KILL SWITCH activo: no se procesa funcionalmente ningún pago nuevo.
        if ($killSwitchDown) {
            $gwEvent->update(['processing_result' => 'ignored', 'processed_at' => now()]);
            audit('payment_kill_switch', 'support_transaction', $tx->id, ['note' => 'payments_enabled=false']);
            return response('OK', 200);
        }

        // 3) Procesar de forma idempotente (la aprobación valida estado y monto server-to-server).
        $this->approval->approve($tx, $gwEvent);

        $gwEvent->update(['processing_result' => 'processed', 'processed_at' => now()]);

        return response('OK', 200);
    }

    private function sanitizeSupporterName(?string $name): ?string
    {
        if (! $name || trim($name) === '') return null;
        return app(\App\Services\ModerationService::class)->sanitizeName($name);
    }
}
