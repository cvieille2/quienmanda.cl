<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\PaymentGateway;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\RankingPeriod;
use App\Models\SupportTransaction;
use Illuminate\Support\Str;

class SupportTransactionService
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingSettingsService $settings,
        private PaymentLimitsService $limits,
    ) {}

    public function createCheckout(
        int $profileId,
        int $amountClp,
        object $identity,
        array $attributes = [],
    ): SupportTransaction {
        $this->assertAmountWithinLimits($amountClp);

        return SupportTransaction::create(array_merge([
            'profile_id'            => $profileId,
            'amount_clp'            => $amountClp,
            'currency'              => Currency::CLP,
            'type'                  => SupportTransactionType::Real,
            'status'                => SupportTransactionStatus::Pending,
            'payment_gateway'       => PaymentGateway::MercadoPago,
            'external_reference'    => (string) Str::ulid(),
            'checkout_created_at'   => now(),
            'gateway_payer_id'      => $identity->gatewayPayerId,
            'payer_reference_hash'  => $identity->payerReferenceHash,
            'payer_email_hash'      => $identity->emailHash,
            'signed_random_cookie'  => $identity->signedCookie,
            'payer_age_declared_18' => true,
            'is_payer_identity_resolved' => (bool) $identity->payerReferenceHash,
        ], $attributes));
    }

    public function assertAmountWithinLimits(int $amount, ?RankingPeriod $period = null): void
    {
        $cfg = $period?->configuration ?? $this->fallbackLimits();

        $min = (int) ($cfg['minimum_support_clp'] ?? PaymentLimitsService::MIN_CLP);
        $max = (int) ($cfg['maximum_support_clp'] ?? PaymentLimitsService::MAX_CLP);

        if ($amount < $min || $amount > $max) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], [], ['amount_clp' => 'monto fuera de rango (' . $min . '-' . $max . ' CLP)'])
            );
        }
    }

    private function fallbackLimits(): array
    {
        $s = $this->settings->defaults();

        return [
            'minimum_support_clp' => $s->minimum_support_clp,
            'maximum_support_clp' => $s->maximum_support_clp,
        ];
    }
}
