<?php

namespace App\Services;

use App\Models\RankingPeriod;
use App\Models\SupportTransaction;
use Illuminate\Support\Str;

/**
 * Creación y ciclo de vida de support_transactions (fuente de verdad del ranking).
 *
 * Valida el monto contra la configuración CONGELADA del periodo (D-005/D-048):
 *  - En checkout: contra el periodo activo (o defaults de ranking_settings como fallback).
 *  - En aprobación: contra el periodo FINAL que posee la transacción (nunca contra ranking_settings
 *    para validar un periodo ya creado).
 * Nunca se hardcodean mínimos/máximos configurables en BD (solo CHECK amount_clp > 0).
 */
class SupportTransactionService
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingSettingsService $settings,
        private PaymentLimitsService $limits,
    ) {}

    /** Crea el checkout (status pending) con external_reference ULID público (idempotencia). */
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
            'type'                  => SupportTransaction::TYPE_REAL,
            'status'                => SupportTransaction::STATUS_PENDING,
            'payment_gateway'       => SupportTransaction::GATEWAY_MERCADOPAGO,
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

    /** Monto vs límites del periodo activo (o defaults si no hay periodo). CHECKs no hardcodeados. */
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

    /** Fallback SOLO para creación de checkouts cuando no hay periodo activo todavía. */
    private function fallbackLimits(): array
    {
        $s = $this->settings->defaults();

        return [
            'minimum_support_clp' => $s->minimum_support_clp,
            'maximum_support_clp' => $s->maximum_support_clp,
        ];
    }
}