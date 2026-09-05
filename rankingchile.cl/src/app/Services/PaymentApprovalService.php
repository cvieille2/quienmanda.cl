<?php

namespace App\Services;

use App\Models\PaymentGatewayEvent;
use App\Models\SupportTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class PaymentApprovalService
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
        private ShareService $shares,
        private SupportTransactionService $transactions,
        private DeltaRecalculateService $delta,
    ) {}

    /**
     * Marca una transacción como approved de forma atómica e idempotente (doble webhook).
     * Aplica la máquina de estados + asignación de periodo por ranking_qualified_at (half-open D-041).
     * $gwEvent, si se pasa, es el registro append-only del webhook (idempotencia extra).
     *
     * Policy D-042 (delta recalc): un pago approved cuyo ranking_qualified_at cae en un periodo
     * cerrado/snapshotted PERTENECE a ese periodo y dispara delta recalc si ya hay snapshot.
     */
    public function approve(SupportTransaction $tx, ?PaymentGatewayEvent $gwEvent = null): SupportTransaction
    {
        return DB::transaction(function () use ($tx, $gwEvent) {
            // Defensa de idempotencia: si el gateway event ya se procesó, no reprocesar.
            if ($gwEvent && ($gwEvent->processed_at !== null || $gwEvent->processing_result === 'processed')) {
                return $tx;
            }

            $locked = SupportTransaction::whereKey($tx->id)->lockForUpdate()->first();

            // Idempotente: si ya no está pending, no reprocesar.
            if ($locked->status !== SupportTransaction::STATUS_PENDING) {
                return $locked; // doble webhook / retorno duplicado
            }

            // Guard de idempotencia por external_reference (ULID) y offset … si el webhook trae
            // una external_reference distinta a la creada en checkout, NO aprobar.
            if ($gwEvent?->external_reference && $locked->external_reference
                && $locked->external_reference !== $gwEvent->external_reference) {
                audit('payment_mismatch', 'support_transaction', $locked->id, [
                    'reason'                    => 'external_reference_mismatch',
                    'webhook_external_reference' => $gwEvent->external_reference,
                    'tx_external_reference'      => $locked->external_reference,
                ]);
                return $locked;
            }

            // Validar contra la pasarela (source of truth server-to-server).
            $conf = $this->gatewayFor($locked)->confirm($locked->provider_transaction_id);
            if (! $this->gatewayFor($locked)->isAuthorized($conf)
                || (int) $conf['amount'] !== $locked->amount_clp) {
                $locked->update([
                    'status'         => SupportTransaction::STATUS_FAILED,
                    'gateway_status' => $conf['status'] ?? null,
                ]);
                audit(\App\Models\AuditLog::EVT_PAYMENT_FAILED, 'support_transaction', $locked->id, [
                    'reason' => 'gateway_failure',
                ]);
                return $locked;
            }

            // Elegir ranking_qualified_at por fallback (sección 3.6).
            $qualifiedAt = $this->resolveQualifiedAt($locked, $conf);

            // Asignar periodo por ranking_qualified_at (half-open). Si no existe, lo crea 'scheduled'
            // desde los defaults (multiduración por diseño; MVP semanal).
            $period = $this->periods->periodFor($qualifiedAt);

            // Validar el monto contra la configuración CONGELADA del periodo de pertenencia final
            // (nunca contra ranking_settings para un periodo ya existente).
            $this->transactions->assertAmountWithinLimits($locked->amount_clp, $period);

            $locked->update([
                'status'               => SupportTransaction::STATUS_APPROVED,
                'provider_approved_at' => $conf['approved_at'] ?? $locked->provider_approved_at ?? now(),
                'webhook_received_at'  => $locked->webhook_received_at ?? now(),
                'ranking_qualified_at' => $qualifiedAt,
                'ranking_period_id'    => $period->id,
                'gateway_status'       => 'AUTHORIZED',
            ]);
            $locked->refresh();

            $closed = in_array($period->status, [
                \App\Enums\RankingPeriodStatus::Closed,
                \App\Enums\RankingPeriodStatus::Snapshotted,
            ], true);

            audit(\App\Models\AuditLog::EVT_PAYMENT_APPROVED, 'support_transaction', $locked->id, [
                'ranking_qualified_at' => (string) $qualifiedAt,
                'ranking_period_id'    => $period->id,
                'period_code'          => $period->code,
                'period_closed'        => $closed,
            ]);

            // Si el periodo de pertenencia está cerrado/snapshotted, aplicar delta recalc.
            if ($closed) {
                $this->delta->handleApprovedForClosedPeriod($locked, $period);
            }

            // Fuera de decisión de aprobación -> share + analytics (idempotente por tx).
            $this->shares->generateFor($locked);
            analytics('pago_confirmado', [
                'transaction_id' => $locked->id,
                'profile_slug'   => $locked->profile?->slug,
                'amount_clp'     => $locked->amount_clp,
                'period_code'    => $period->code,
            ]);
            $this->ranking->invalidateCache();

            return $locked;
        });
    }

    private function gatewayFor(SupportTransaction $tx): \App\Services\Payments\PaymentGatewayInterface
    {
        return app(\App\Services\Payments\MercadoPagoGateway::class); // UNA sola pasarela en MVP (D-30)
    }

    /** Política de fallback oficial de ranking_qualified_at (3.6). */
    private function resolveQualifiedAt(SupportTransaction $tx, array $conf): CarbonImmutable
    {
        $approvedRaw = $conf['approved_at'] ?? $tx->provider_approved_at ?? null;
        if ($approvedRaw && $this->isValidProviderDate($approvedRaw, $tx)) {
            return CarbonImmutable::parse($approvedRaw);
        }
        $webhook = $tx->webhook_received_at ?? now();
        if ($webhook) {
            return CarbonImmutable::parse($webhook);
        }
        return CarbonImmutable::now();
    }

    private function isValidProviderDate($date, SupportTransaction $tx): bool
    {
        if (! $date) return false;
        try {
            $d = CarbonImmutable::parse($date);
            if ($d->gt(now()->addMinutes(5))) return false;        // no futura
            if ($tx->checkout_created_at && $d->lt($tx->checkout_created_at->subHours(2))) return false; // no previa irreal
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}