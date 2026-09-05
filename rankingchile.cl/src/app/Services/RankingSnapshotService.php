<?php

namespace App\Services;

use App\Enums\RankingPeriodStatus;
use App\Models\RankingPeriod;
use App\Models\RankingSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Generación de snapshots en el settlement (D-042).
 *
 * - El snapshot BASE (revision=0) se genera al ejecutarse el settlement window,
 *   NUNCA al cruzar ends_at.
 * - Append-only e inmutable: los deltas (D-042) insertan revision+1, jamás reescriben.
 * - Idempotente: si el periodo ya tiene snapshot base, no genera duplicados.
 */
class RankingSnapshotService
{
    public function __construct(private RankingService $ranking) {}

    /**
     * Transición closed -> snapshotted generando el snapshot base (revision 0).
     * Lock sobre la fila del periodo para serializar settlement concurrentes.
     */
    public function generateBaseAndMarkSnapshotted(RankingPeriod $period, ?CarbonImmutable $at = null): void
    {
        DB::transaction(function () use ($period, $at) {
            $locked = RankingPeriod::query()->whereKey($period->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== RankingPeriodStatus::Closed) {
                return; // Idempotente: ya snapshotteado o en estado no elegible.
            }

            $already = RankingSnapshot::where('ranking_period_id', $locked->id)->count();
            if ($already > 0) {
                $this->markSnapshotted($locked, $at);
                return;
            }

            $rows = $this->ranking->rankingForPeriod($locked->id); // misma query oficial

            foreach ($rows as $i => $r) {
                RankingSnapshot::create([
                    'ranking_period_id'      => $locked->id,
                    'profile_id'             => $r['profile_id'],
                    'final_position'         => $i + 1,
                    'total_real_clp'         => $r['total_real_clp'],
                    'total_promotional_clp'  => $r['total_promotional_clp'],
                    'total_supporters'       => $r['supporter_count'],
                    'total_reached_at'       => $r['total_reached_at'],
                    'revision'               => 0,
                    'is_revision_of'         => null,
                    'immutable'              => true,
                ]);
            }

            $this->markSnapshotted($locked, $at);

            audit(\App\Models\AuditLog::EVT_PERIOD_SNAPSHOTTED, 'ranking_period', $locked->id, [
                'code'              => $locked->code,
                'snapshot_rows'     => count($rows),
            ]);
        });
    }

    private function markSnapshotted(RankingPeriod $period, ?CarbonImmutable $at = null): void
    {
        if ($period->status !== RankingPeriodStatus::Snapshotted) {
            $period->update([
                'status'         => RankingPeriodStatus::Snapshotted->value,
                'snapshotted_at' => $at ?? now(),
            ]);
        }
    }
}