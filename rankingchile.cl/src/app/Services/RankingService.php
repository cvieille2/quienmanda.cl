<?php

namespace App\Services;

use App\Models\RankingPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankingService
{
    public const MIN_INCREMENT_CLP = 1000;
    public const CACHE_SECONDS = 5; // regla oficial

    public function __construct(
        private RankingPeriodService $periods,
        private RankingTimeWindowService $timeWindowService
    ) {}

    /**
     * Oficial Ranking based on RankingContext.
     * @param RankingContext $context
     * @param bool $withDelta
     * @return array
     */

    /**
     * Ranking oficial del periodo activo. Solo transacciones type=real + status=approved.
     * Devuelve posición, total_real_clp, total_promotional_clp (separado), delta al superior
     * y monto para subir al #1.
     */
    public function activePeriodRanking(bool $withDelta = true, ?int $categoryId = null): array
    {
        // Resolve the active period context.
        $now = Carbon::now(RankingTimeWindowService::TIMEZONE);
        // Assuming 'week' is the default for activePeriod if not specified.
        $activePeriodWindow = RankingPeriodWindow::from('week'); 
        $context = new RankingContext($activePeriodWindow, $categoryId);
        
        return $this->getRanking($context, $withDelta);
    }

    /**
     * Misma query oficial pero para un periodo arbitrario (usado por snapshots y delta recalc).
     * Asignación half-open sobre ranking_qualified_at (D-041).
     */
    /**
     * Get ranking for a specific context.
     * @param RankingContext $context
     * @param bool $withDelta
     * @return array
     */
    public function getRanking(RankingContext $context, bool $withDelta = false): array
    {
        $rows = DB::select("
            SELECT
                p.id AS profile_id, p.slug, p.display_name, p.category,
                p.verification_status, p.is_community_created,
                COALESCE(SUM(CASE WHEN st.status='approved' THEN st.amount_clp ELSE 0 END),0) AS total_real_clp,
                COALESCE((
                    SELECT SUM(amount_clp) FROM support_transactions pt
                    WHERE pt.profile_id = p.id
                      AND pt.ranking_period_id = ?
                      AND pt.type='promotional' AND pt.status='approved'
                ),0) AS total_promotional_clp,
                COUNT(DISTINCT st.payer_reference_hash) AS supporter_count,
                MAX(st.ranking_qualified_at) AS total_reached_at
            FROM profiles p
            LEFT JOIN support_transactions st
                   ON st.profile_id = p.id AND st.ranking_period_id = ?
                  AND st.type='real' AND st.status='approved'
            WHERE p.status = 'active' AND (
                $context->categoryId IS NULL OR p.category_id = ?
            )
            GROUP BY p.id, p.slug, p.display_name, p.category,
                     p.verification_status, p.is_community_created
            ORDER BY total_real_clp DESC, total_reached_at ASC
        ", [$context->category_id, $context->category_id]); // Placeholder for category_id filter

        $ranking = [];
        foreach ($rows as $i => $r) {
            $ranking[] = [
                'position'              => $i + 1,
                'profile_id'            => (int) $r->profile_id,
                'slug'                  => $r->slug,
                'display_name'          => $r->display_name,
                'category'              => $r->category,
                'verification_status'   => $r->verification_status,
                'is_community_created'  => (bool) $r->is_community_created,
                'total_real_clp'        => (int) $r->total_real_clp,
                'total_promotional_clp' => (int) $r->total_promotional_clp, // separado, no rankea
                'supporter_count'       => (int) $r->supporter_count,
                'total_reached_at'      => $r->total_reached_at,
            ];
        }

        if ($withDelta) {
            foreach ($ranking as $i => &$item) {
                if ($i === 0) {
                    $item['behind_clp']          = 0;
                    $item['overtake_above_clp']  = 0;
                    $item['to_number_one_clp']   = 0;
                    continue;
                }
                $item['behind_clp']         = $ranking[$i - 1]['total_real_clp'] - $item['total_real_clp'];
                $item['overtake_above_clp'] = $item['behind_clp'] + self::MIN_INCREMENT_CLP;
                $item['to_number_one_clp']  = ($ranking[0]['total_real_clp'] - $item['total_real_clp'])
                                            + self::MIN_INCREMENT_CLP;
            }
            unset($item);
        }

        return $ranking;
    }

    /** Payload mínimo para el polling adaptativo (5.2). public_id como external_key del periodo. */
    /**
     * Minimal payload for adaptive polling.
     * Uses the current active period context.
     */
    public function periodCompact(?int $categoryId = null): array
    {
        $now = Carbon::now(RankingTimeWindowService::TIMEZONE);
        $activePeriodWindow = RankingPeriodWindow::from('week'); // Default to week for active period
        $context = new RankingContext($activePeriodWindow, $categoryId);

        return collect($this->getRanking($context, false))->map(fn ($r) => [
            'rank'          => $r['position'],
            'slug'          => $r['slug'],
            'total_real_clp'=> $r['total_real_clp'],
            'position'      => $r['position'],
        ])->values()->all();
    }

    public function invalidateCache(): void
    {
        // Invalidate cache based on new context principles
        Cache::forget('ranking:today:all:*');
        Cache::forget('ranking:week:all:*');
        Cache::forget('ranking:month:all:*');
        Cache::forget('ranking:year:all:*');
        // Invalidate category-specific caches if they exist and are affected
        // This part might need more specific logic depending on how category caches are structured
        // For now, invalidating all general period caches is a broad but safe approach.
    }
}