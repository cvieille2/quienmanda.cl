<?php

namespace App\Http\Controllers;

use App\Enums\ProfileStatus;
use App\Enums\PaymentGateway;
use App\Models\PaymentGatewayEvent;
use App\Models\Profile;
use App\Models\SupportTransaction;
use App\Services\FeatureFlagsService;
use App\Services\ModerationService;
use App\Services\PaymentApprovalService;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\PaymentLimitsService;
use App\Services\RankingPeriodService;
use App\Services\SupportTransactionService;
use Illuminate\Http\Request;

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
    ) {}

    public function start(Request $request)
    {
        $validated = $request->validate([
            'profile_id'      => ['required', 'exists:profiles,id'],
            'amount_clp'      => ['required', 'integer', 'min:1'],
            'supporter_name'  => ['nullable', 'string', 'max:64'],
            'is_anonymous'    => ['nullable', 'boolean'],
            'age_declared_18' => ['required', 'accepted'],
            'checkout_token'  => ['required', 'string', 'min:8'],
        ]);

        $profile = Profile::findOrFail((int) $validated['profile_id']);
        $amount  = (int) $validated['amount_clp'];

        if (! $this->flags->isEnabled('payments_enabled')) {
            return response()->json(['error' => 'checkout_disabled'], 503);
        }

        if ($profile->status !== ProfileStatus::Active) {
            return response()->json(['error' => 'profile_not_available'], 422);
        }

        $this->transactions->assertAmountWithinLimits($amount, $this->periods->activePeriod());

        $identity = $this->limits->resolvePayerIdentity($request);
        $this->limits->assertCanCheckout($request, $amount);

        $tx = $this->transactions->createCheckout(
            $profile->id,
            $amount,
            $identity,
            [
                'supporter_name' => $this->sanitizeSupporterName($validated['supporter_name'] ?? null),
                'is_anonymous'   => (bool) $request->boolean('is_anonymous', true),
            ]
        );

        $txw = $this->gw->create($amount, $tx->external_reference, $tx->id . '-' . uniqid(), [
            'subject' => $profile->display_name,
            'payment_gateway' => PaymentGateway::MercadoPago->value,
        ]);
        $tx->update(['provider_transaction_id' => $txw['token']]);

        audit('payment_created', 'support_transaction', $tx->id, ['amount_clp' => $amount, 'profile_id' => $profile->id]);

        return response()->json(['checkout_url' => $txw['url'], 'external_reference' => $tx->external_reference]);
    }

    /** Retorno (confirma la creación del checkout; la aprobación definitiva es el webhook). */
    public function return(Request $request)
    {
        $paymentId = $request->input('payment_id');
        $tx = SupportTransaction::where('provider_transaction_id', $paymentId)->firstOrFail();

        return redirect()->route('payments.pending', ['slug' => $tx->profile->slug]);
    }

    /**
     * Webhook server-to-server: ÚNICA fuente de confirmación definitiva.
     */
    public function webhook(Request $request)
    {
        $raw         = $request->getContent();
        $payloadHash = hash('sha256', $raw);
        $notification = $request->json()->all();
        $gatewayEvent = (string) ($notification['data']['id'] ?? '');
        $gatewayEvent = $gatewayEvent ?: (string) ($notification['id'] ?? '');
        $topic        = (string) ($notification['type'] ?? $request->input('topic') ?? '');

        $handledTopics = config('mercadopago.webhook_topics', ['payment', 'merchant_order']);
        $matched = $topic !== '' ? in_array($topic, $handledTopics, true) : true;

        if (! $matched) {
            PaymentGatewayEvent::firstOrCreate(
                ['payment_gateway' => PaymentGateway::MercadoPago->value, 'gateway_event_id' => 'unhandled:' . $gatewayEvent . ':' . ($topic ?: '')],
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

        $killSwitchDown = ! $this->flags->isEnabled('payments_enabled');

        $gwEvent = PaymentGatewayEvent::firstOrCreate(
            ['payment_gateway' => PaymentGateway::MercadoPago->value, 'gateway_event_id' => $gatewayEvent],
            [
                'gateway_transaction_id' => $gatewayEvent,
                'external_reference'     => $notification['external_reference'] ?? null,
                'event_type'             => 'notification',
                'payload_hash'           => $payloadHash,
                'received_at'            => now(),
            ]
        );

        if ($gwEvent->processed_at !== null || $gwEvent->processing_result === 'processed') {
            return response('OK', 200);
        }

        $tx = SupportTransaction::where('provider_transaction_id', $gatewayEvent)->first();
        if (! $tx) {
            $gwEvent->update(['processing_result' => 'ignored', 'processed_at' => now()]);
            return response('', 404);
        }

        if ($killSwitchDown) {
            $gwEvent->update(['processing_result' => 'ignored', 'processed_at' => now()]);
            audit('payment_kill_switch', 'support_transaction', $tx->id, ['note' => 'payments_enabled=false']);

            return response('OK', 200);
        }

        $this->approval->approve($tx, $gwEvent);
        $gwEvent->update(['processing_result' => 'processed', 'processed_at' => now()]);

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
