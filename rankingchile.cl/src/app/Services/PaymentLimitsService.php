<?php

namespace App\Services;

use App\Models\SupportTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Límites financieros (regla oficial + NO_GO) y resolución de identidad del pagador.
 * - Umbral diario 1.000.000 CLP por identidad agregada (flag_for_review).
 * - El rango por pago (min 1.000 / max 500.000 CLP) lo valida SupportTransactionService contra
 *   la configuration CONGELADA del periodo activo — constantes aquí solo como fallback (D-048).
 * - Edad mínima 18 (personas v1.2.0), declarada en checkout.
 * - Identidad del ranking usa HASHES (payer_reference_hash / payer_email_hash), NUNCA email crudo (D-33).
 */
class PaymentLimitsService
{
    public const MIN_CLP = 1000;   // fallback default (configuration congelada del periodo es la fuente)
    public const MAX_CLP = 500000; // fallback default
    public const DAILY_THRESHOLD_CLP = 1000000;

    public function assertCanCheckout(Request $request, int $amount): void
    {
        // Umbral diario por identidad agregada (hash del pagador).
        $identity = $this->resolvePayerIdentity($request);
        $todayTotal = (int) SupportTransaction::where('payer_reference_hash', $identity->payerReferenceHash)
            ->where('type', SupportTransaction::TYPE_REAL)
            ->where('status', SupportTransaction::STATUS_APPROVED)
            ->where('checkout_created_at', '>=', now()->startOfDay())
            ->sum('amount_clp');

        if (($todayTotal + $amount) > self::DAILY_THRESHOLD_CLP) {
            // Excede umbral diario -> flag_for_review manual (anti-fraude/uso indebido).
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

    /**
     * Resuelve la identidad agregada del pagador en forma de hashes.
     * Persistencia de sesión vía cookie firmada; si no hay sesión robusta se usa un uuid de sesión.
     */
    public function resolvePayerIdentity(Request $request): object
    {
        $sessionId = $request->session()->getId() ?: (string) Str::uuid();
        $gatewayPayerId = $request->input('payer_id') ?: (string) $sessionId;

        return (object) [
            'gatewayPayerId'   => $gatewayPayerId,
            // Hash SHA-256 del identificador del pagador (identidad RAW nunca se usa para supporter_count).
            'payerReferenceHash' => hash('sha256', 'payer:' . $gatewayPayerId . ':' . config('app.key')),
            'emailHash'        => $request->input('payer_email')
                ? hash('sha256', strtolower(trim($request->input('payer_email'))))
                : null,
            'signedCookie'     => (string) $sessionId,
        ];
    }
}
