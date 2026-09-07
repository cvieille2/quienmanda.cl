<?php

namespace App\Http\Controllers;

use App\Enums\GatewayProcessingResult;
use App\Enums\PaymentGateway;
use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Models\PaymentGatewayEvent;
use App\Models\Profile;
use App\Models\SupportTransaction;
use App\Services\ClaimOutbidService;
use App\Services\FeatureFlagsService;
use App\Services\ModerationService;
use App\Services\PaymentApprovalService;
use App\Services\PaymentLimitsService;
use App\Services\Payments\MercadoPagoWebhookSignature;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\RankingPeriodService;
use App\Services\RankingProjectionService;
use App\Services\SupportTransactionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentGatewayInterface $gw,
        private PaymentLimitsService $limits,
        private PaymentApprovalService $approval,
        private SupportTransactionService $transactions,
        private RankingPeriodService $periods,
        private FeatureFlagsService $flags,
        private ModerationService $moderation,
        private ClaimOutbidService $claimOutbid,
        private RankingProjectionService $projection,
    ) {}

    public function start(Request $request)
    {
        // target_position is the new preferred way; amount_clp is deprecated (kept for backward compatibility).
        $validated = $request->validate([
            'profile_id' => ['required', 'exists:profiles,id'],
            'target_position' => ['required_without:amount_clp', 'nullable', 'integer', 'min:1'],
            // @deprecated amount_clp – will be removed once all frontends migrate to target_position.
            'amount_clp' => ['required_without:target_position', 'nullable', 'integer', 'min:1'],
            'supporter_name' => ['nullable', 'string', 'max:64'],
            'is_anonymous' => ['nullable', 'boolean'],
            'age_declared_18' => ['required', 'accepted'],
            'terms_accepted' => ['required', 'accepted'],
            'checkout_token' => ['required', 'string', 'min:8'],
        ]);

        $period = $this->periods->activePeriod();
        $profile = Profile::findOrFail((int) $validated['profile_id']);

        // ── Amount resolution ──────────────────────────────────────────────
        // New flow: derive amount from target_position via projection service.
        // Legacy flow: trust the frontend-supplied amount_clp.
        if (! empty($validated['target_position'])) {
            $projection = $this->projection->projectProfileMove(
                profileId: (int) $validated['profile_id'],
                rankingPeriodId: $period?->id,
                targetPosition: (int) $validated['target_position'],
            );

            $amount = $projection['required_amount'];

            if ($amount <= 0) {
                return response()->json(['error' => 'profile_already_at_or_above_target'], 422);
            }
        } else {
            // Legacy path – amount_clp supplied by frontend (deprecated).
            $amount = (int) $validated['amount_clp'];
        }
        // ── End amount resolution ──────────────────────────────────────────

        if (! $this->flags->isEnabled('payments_enabled')) {
            return response()->json(['error' => 'checkout_disabled'], 503);
        }

        if ($profile->status !== ProfileStatus::Active) {
            return response()->json(['error' => 'profile_not_available'], 422);
        }

        $this->transactions->assertAmountWithinLimits($amount, $period);

        $identity = $this->limits->resolvePayerIdentity($request);
        $this->limits->assertCanCheckout($request, $amount);

        // NOTE: If the ranking changed between page-load and checkout the
        // server-calculated amount may differ from what the frontend originally
        // displayed.  Per the spec (price_change_behavior) we use the
        // authoritative server value; the frontend will reflect the correct
        // amount in the receipt.
        $tx = $this->transactions->createCheckout(
            $profile->id,
            $amount, // always server-authoritative
            $identity,
            [
                'supporter_name' => $this->sanitizeSupporterName($validated['supporter_name'] ?? null),
                'is_anonymous' => (bool) $request->boolean('is_anonymous', true),
                'terms_version' => config('legal.terms.version'),
                'terms_accepted_at' => now(),
            ]
        );

        $txw = $this->gw->create($amount, $tx->external_reference, $tx->id.'-'.uniqid(), [
            'subject' => $profile->display_name,
            'payment_gateway' => PaymentGateway::MercadoPago->value,
        ]);

        // BLOCKER-002 (TASK-PROD-001): el token de la preferencia NO es un payment id.
        // Se guarda como provider_order_id; el payment id llega por webhook/return.
        $tx->update([
            'provider_transaction_id' => $txw['token'],
            'provider_order_id' => $txw['token'],
        ]);

        audit('payment_created', 'support_transaction', $tx->id, ['amount_clp' => $amount, 'profile_id' => $profile->id]);

        return response()->json(['checkout_url' => $txw['url'], 'external_reference' => $tx->external_reference, 'period_code' => $period?->code ?? '', 'receipt_id' => 'RCH-' . strtoupper(substr($tx->external_reference, 0, 12))]);
    }

    /** Retorno del navegador tras el checkout (UX únicamente; la verdad la da el webhook). */
    public function return(Request $request)
    {
        $paymentId = (string) $request->input('payment_id');
        $externalReference = (string) $request->input('external_reference');

        $tx = SupportTransaction::query()
            ->where(function ($q) use ($paymentId, $externalReference) {
                if ($paymentId !== '') {
                    $q->where('provider_payment_id', $paymentId)->orWhere('provider_transaction_id', $paymentId);
                }
                if ($externalReference !== '') {
                    $q->orWhere('external_reference', $externalReference);
                }
            })
            ->firstOrFail();

        if ($paymentId !== '' && ! $tx->provider_payment_id) {
            $tx->update(['provider_payment_id' => $paymentId]);
        }

        return redirect()->route('payments.resultado', [
            'payment_id' => $paymentId !== '' ? $paymentId : $tx->provider_transaction_id,
        ]);
    }

    /** Página amigable de resultado (success/pending/failure) para el navegador. */
    public function result(Request $request): View
    {
        $paymentId = (string) $request->input('payment_id');
        $tx = $paymentId !== '' ? SupportTransaction::query()
            ->where('provider_payment_id', $paymentId)
            ->orWhere('provider_transaction_id', $paymentId)
            ->first() : null;

        $receipt = null;
        if ($tx && $tx->status === SupportTransactionStatus::Approved) {
            $receipt = $this->claimOutbid->generateReceipt($tx);
        }

        return view('payments.resultado', [
            'transaction' => $tx,
            'slug' => $tx?->profile->slug,
            'receipt' => $receipt,
        ]);
    }

    /**
     * Webhook server-to-server: ÚNICA fuente de confirmación definitiva.
     * BLOCKER-005 (TASK-PROD-003): valida firma HMAC cuando hay secret configurado.
     */
    public function webhook(Request $request)
    {
        $raw = $request->getContent();
        $payloadHash = hash('sha256', $raw);
        $notification = $request->json()->all();
        $gatewayEvent = (string) ($notification['data']['id'] ?? '');
        $gatewayEvent = $gatewayEvent ?: (string) ($notification['id'] ?? '');
        $topic = (string) ($notification['type'] ?? $request->input('topic') ?? '');

        if ($gatewayEvent === '') {
            return response('', 400);
        }

        $signatureVerifier = new MercadoPagoWebhookSignature((string) config('mercadopago.webhook_secret'));
        if (! $signatureVerifier->verify($request, $gatewayEvent)) {
            audit('payment_webhook_invalid_signature', 'payment_gateway_event', null, [
                'gateway_event_id' => $gatewayEvent,
                'topic' => $topic,
            ], 'gateway');

            return response('', 403);
        }

        $handledTopics = config('mercadopago.webhook_topics', ['payment', 'merchant_order']);
        $matched = $topic !== '' ? in_array($topic, $handledTopics, true) : true;

        if (! $matched) {
            PaymentGatewayEvent::firstOrCreate(
                ['payment_gateway' => PaymentGateway::MercadoPago->value, 'gateway_event_id' => 'unhandled:'.$gatewayEvent.':'.($topic ?: '')],
                [
                    'provider_transaction_id' => $gatewayEvent,
                    'event_type' => 'topic_unhandled',
                    'payload_hash' => $payloadHash,
                    'received_at' => now(),
                    'processed_at' => now(),
                    'processing_result' => GatewayProcessingResult::Ignored,
                ]
            );

            return response('OK', 200);
        }

        $killSwitchDown = ! $this->flags->isEnabled('payments_enabled');

        $gwEvent = PaymentGatewayEvent::firstOrCreate(
            ['payment_gateway' => PaymentGateway::MercadoPago->value, 'gateway_event_id' => $gatewayEvent],
            [
                'provider_transaction_id' => $gatewayEvent,
                'external_reference' => $notification['external_reference'] ?? null,
                'event_type' => 'notification',
                'payload_hash' => $payloadHash,
                'received_at' => now(),
            ]
        );

        if ($gwEvent->processed_at !== null || $gwEvent->processing_result === GatewayProcessingResult::Processed) {
            return response('OK', 200);
        }

        // BLOCKER-002/003 (TASK-PROD-001): data.id de topic=payment ES payment_id.
        // Con topic=merchant_order data.id es el id de la orden (provider_order_id).
        $tx = SupportTransaction::where('provider_payment_id', $gatewayEvent)->first();
        if (! $tx) {
            $tx = SupportTransaction::where('provider_transaction_id', $gatewayEvent)->first();
        }
        if (! $tx && $gwEvent->external_reference) {
            $tx = SupportTransaction::where('external_reference', $gwEvent->external_reference)->first();
        }

        if (! $tx) {
            $gwEvent->update(['processing_result' => GatewayProcessingResult::Ignored, 'processed_at' => now()]);

            return response('', 404);
        }

        $identifierColumn = $topic === 'payment' ? 'provider_payment_id' : 'provider_order_id';
        if (! $tx->{$identifierColumn}) {
            $tx->update([$identifierColumn => $gatewayEvent]);
        }

        if ($killSwitchDown) {
            $gwEvent->update(['processing_result' => GatewayProcessingResult::Ignored, 'processed_at' => now()]);
            audit('payment_kill_switch', 'support_transaction', $tx->id, ['note' => 'payments_enabled=false']);

            return response('OK', 200);
        }

        $updated = $this->approval->approve($tx, $gwEvent);
        $isTerminal = in_array($updated->status, [
            SupportTransactionStatus::Approved,
            SupportTransactionStatus::Failed,
        ], true);

        $gwEvent->update([
            'processing_result' => $isTerminal ? GatewayProcessingResult::Processed : GatewayProcessingResult::Pending,
            'processed_at' => $isTerminal ? now() : null,
        ]);

        return response('OK', 200);
    }

    private function sanitizeSupporterName(?string $name): ?string
    {
        if (! $name || trim($name) === '') {
            return null;
        }

        return $this->moderation->sanitizeName($name);
    }
}
