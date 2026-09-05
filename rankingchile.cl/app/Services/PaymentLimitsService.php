<?php

namespace App\Services;

use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\SupportTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentLimitsService
{
    public const MIN_CLP = 1000;
    public const MAX_CLP = 500000;
    public const DAILY_THRESHOLD_CLP = 1000000;

    public function assertCanCheckout(Request $request, int $amount): void
    {
        $identity = $this->resolvePayerIdentity($request);
        $todayTotal = (int) SupportTransaction::where('payer_reference_hash', $identity->payerReferenceHash)
            ->where('type', SupportTransactionType::Real->value)
            ->where('status', SupportTransactionStatus::Approved->value)
            ->where('checkout_created_at', '>=', now()->startOfDay())
            ->sum('amount_clp');

        if (($todayTotal + $amount) > self::DAILY_THRESHOLD_CLP) {
            audit('payment_daily_threshold_exceeded', 'support_transaction', null, [
                'payer_reference_hash' => $identity->payerReferenceHash,
                'amount'               => $amount,
                'today_total'          => $todayTotal,
            ]);
            throw new \Illuminate\Validation\ValidationException(
                validator([], [], ['amount_clp' => 'daily_threshold_exceeded'])
            );
        }
    }

    public function resolvePayerIdentity(Request $request): object
    {
        $sessionId = $request->session()->getId() ?: (string) Str::uuid();
        $gatewayPayerId = $request->input('payer_id') ?: (string) $sessionId;

        return (object) [
            'gatewayPayerId' => $gatewayPayerId,
            'payerReferenceHash' => hash('sha256', 'payer:' . $gatewayPayerId . ':' . config('app.key')),
            'emailHash' => $request->input('payer_email')
                ? hash('sha256', strtolower(trim($request->input('payer_email'))))
                : null,
            'signedCookie' => (string) $sessionId,
        ];
    }
}
