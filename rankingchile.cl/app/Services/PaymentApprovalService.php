<?php

namespace App\Services;

use App\Enums\RankingPeriodStatus;
use App\Enums\SupportTransactionStatus;
use App\Models\PaymentGatewayEvent;
use App\Models\SupportTransaction;
use App\Services\Payments\PaymentGatewayInterface;
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
        private PaymentGatewayInterface $gateway,
    ) {}

    public function approve(SupportTransaction $tx, ?PaymentGatewayEvent $gwEvent = null): SupportTransaction
    {
        return DB::transaction(function () use ($tx, $gwEvent) {
            if ($gwEvent && ($gwEvent->processed_at !== null || $gwEvent->processing_result === 'processed')) {
                return $tx;
            }

            $locked = SupportTransaction::whereKey($tx->id)->lockForUpdate()->first();

            if ($locked->status !== SupportTransactionStatus::Pending) {
                return $locked;
            }

            if ($gwEvent?->external_reference && $locked->external_reference
                && $locked->external_reference !== $gwEvent->external_reference) {
                audit('payment_mismatch', 'support_transaction', $locked->id, [
                    'reason'                    => 'external_reference_mismatch',
                    'webhook_external_reference' => $gwEvent->external_reference,
                    'tx_external_reference'      => $locked->external_reference,
                ]);

                return $locked;
            }

            $conf = $this->gateway->confirm($locked->provider_transaction_id);
            if (! $this->gateway->isAuthorized($conf)
                || (int) $conf['amount'] !== $locked->amount_clp) {
                $locked->update([
                    'status'        => SupportTransactionStatus::Failed,
                    'gateway_status' => $conf['status'] ?? null,
                ]);
                audit(\App\Models\AuditLog::EVT_PAYMENT_FAILED, 'support_transaction', $locked->id, [
                    'reason' => 'gateway_failure',
                ]);

                return $locked;
            }

            $qualifiedAt = $this->resolveQualifiedAt($locked, $conf);
            $period = $this->periods->periodFor($qualifiedAt);
            $this->transactions->assertAmountWithinLimits($locked->amount_clp, $period);

            $locked->update([
                'status'               => SupportTransactionStatus::Approved,
                'provider_approved_at'  => $conf['approved_at'] ?? $locked->provider_approved_at ?? now(),
                'webhook_received_at'   => $locked->webhook_received_at ?? now(),
                'ranking_qualified_at'  => $qualifiedAt,
                'ranking_period_id'     => $period->id,
                'gateway_status'        => 'AUTHORIZED',
            ]);
            $locked->refresh();

            $closed = in_array($period->status, [
                RankingPeriodStatus::Closed,
                RankingPeriodStatus::Snapshotted,
            ], true);

            audit(\App\Models\AuditLog::EVT_PAYMENT_APPROVED, 'support_transaction', $locked->id, [
                'ranking_qualified_at' => (string) $qualifiedAt,
                'ranking_period_id'    => $period->id,
                'period_code'          => $period->code,
                'period_closed'        => $closed,
            ]);

            if ($closed) {
                $this->delta->handleApprovedForClosedPeriod($locked, $period);
            }

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
        if (! $date) {
            return false;
        }

        try {
            $d = CarbonImmutable::parse($date);
            if ($d->gt(now()->addMinutes(5))) {
                return false;
            }
            if ($tx->checkout_created_at && $d->lt($tx->checkout_created_at->subHours(2))) {
                return false;
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
