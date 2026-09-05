<?php

namespace App\Services;

use App\Enums\RankingPeriodStatus;
use App\Models\RankingPeriod;
use App\Models\RankingSnapshot;
use App\Models\SupportTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Delta recalculation (D-042, policy delta recalc).
 *
 * Un pago `approved` cuyo `ranking_qualified_at` cae dentro de un periodo ya cerrado
 * PERTENECE a ese periodo (intervalo half-open). Si el periodo ya fue snapshotteado,
 * se inserta un snapshot CORREGIDO (nuevo registro, `revision+1`) SIN tocar el original.
 *
 * Reglas de integridad:
 *  - LÍNEA DE REVISIONES DETERMINISTA: todo revision > 0 apunta con `is_revision_of`
 *    al snapshot ORIGINAL (revision=0), nunca a revisiones intermedias.
 *  - Inmutabilidad: jamás se actualiza/elimina un snapshot existente (guard en el Model
 *    y constrains/checks). La posición oficial = la de MAYOR revision.
 *  - Chargebacks/refunds post-cierre NUNCA invocan este servicio (solo audit).
 */
class DeltaRecalculateService
{
    public function __construct(private RankingService $ranking, private RankingPeriodService $periods) {}

    /**
     * Recalcula el snapshot oficial del periodo incorporando el pago tardío ya aprobado.
     * Idempotente por UNIQUE(ranking_period_id, profile_id, revision).
     * Lock sobre la fila del periodo: serializa recálculos concurrentes del mismo periodo.
     */
    public function apply(RankingPeriod $period): void
    {
        if (! in_array($period->status, [
            RankingPeriodStatus::Closed,
            RankingPeriodStatus::Snapshotted,
        ], true)) {
            return; // solo periodos cerrados/snapshotteados son elegibles para delta recalc
        }

        DB::transaction(function () use ($period) {
            $locked = RankingPeriod::query()->whereKey($period->id)->lockForUpdate()->firstOrFail();

            $rows = $this->ranking->rankingForPeriod($locked->id); // incluye el pago tardío aprobado

            foreach ($rows as $i => $r) {
                $original = RankingSnapshot::query()
                    ->where('ranking_period_id', $locked->id)
                    ->where('profile_id', $r['profile_id'])
                    ->orderBy('revision') // revision 0 = snapshot base (evidencia)
                    ->first();

                $maxRevision = (int) RankingSnapshot::query()
                    ->where('ranking_period_id', $locked->id)
                    ->where('profile_id', $r['profile_id'])
                    ->max('revision');

                $nextRevision = $maxRevision + 1;

                RankingSnapshot::create([
                    'ranking_period_id'     => $locked->id,
                    'profile_id'            => $r['profile_id'],
                    'final_position'        => $i + 1,
                    'total_real_clp'        => $r['total_real_clp'],
                    'total_promotional_clp' => $r['total_promotional_clp'],
                    'total_supporters'      => $r['supporter_count'],
                    'total_reached_at'      => $r['total_reached_at'],
                    'revision'              => $nextRevision,
                    'is_revision_of'        => $original?->id, // SIEMPRE el original (lineage determinista)
                    'immutable'             => true,
                ]);

                audit(\App\Models\AuditLog::EVT_RANKING_DELTA_RECALCULATED, 'ranking_snapshot', $original?->id, [
                    'ranking_period_id' => $locked->id,
                    'profile_id'        => $r['profile_id'],
                    'revision'          => $nextRevision,
                    'final_position'    => $i + 1,
                    'total_real_clp'    => $r['total_real_clp'],
                ]);
            }
        });

        $this->ranking->invalidateCache();
    }

    /**
     * Punto de entrada desde el flujo de aprobación (pago tardío de un periodo cerrado).
     * Recalcula SOLO si el periodo ya fue snapshotteado; en otro caso el snapshot base lo
     * incorporará al ejecutar el settlement (el pago ya quedó asignado con ranking_period_id).
     */
    public function handleApprovedForClosedPeriod(SupportTransaction $tx, RankingPeriod $period): void
    {
        if ($period->status === RankingPeriodStatus::Snapshotted) {
            $this->apply($period);
        }

        audit(\App\Models\AuditLog::EVT_PAYMENT_DELTA_QUEUED, 'support_transaction', $tx->id, [
            'ranking_period_id' => $period->id,
            'snapshotted'       => $period->status === RankingPeriodStatus::Snapshotted,
        ]);
    }
}