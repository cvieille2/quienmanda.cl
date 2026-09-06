<?php

namespace App\Services;

use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\RankingPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HeaderStatsService
{
    public const CACHE_TTL_SECONDS = 60;

    public function __construct(
        private RankingPeriodService $periods,
    ) {}

    /**
     * Get header stats for the active period, cached.
     */
    public function forActivePeriod(): array
    {
        $period = $this->periods->activePeriod();

        if (! $period) {
            return $this->emptyStats();
        }

        $cacheKey = "header_stats:{$period->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, fn () => $this->calculate($period));
    }

    /**
     * Force recalculate (used after payment approval).
     */
    public function invalidate(?RankingPeriod $period = null): void
    {
        $period ??= $this->periods->activePeriod();
        if ($period) {
            Cache::forget("header_stats:{$period->id}");
        }
    }

    private function calculate(RankingPeriod $period): array
    {
        return [
            'active_profiles' => $this->countActiveProfiles($period),
            'period_amount' => $this->sumPeriodAmount($period),
            'outbound_clicks' => $this->countOutboundClicks($period),
        ];
    }

    /**
     * COUNT DISTINCT profile_id from approved transactions within period,
     * only for active profiles.
     */
    private function countActiveProfiles(RankingPeriod $period): int
    {
        return (int) DB::selectOne("
            SELECT COUNT(DISTINCT st.profile_id) AS cnt
            FROM support_transactions st
            JOIN profiles p ON p.id = st.profile_id
            WHERE st.ranking_period_id = ?
              AND st.status = ?
              AND st.type = ?
              AND st.ranking_qualified_at >= ?
              AND st.ranking_qualified_at < ?
              AND p.status = ?
        ", [
            $period->id,
            SupportTransactionStatus::Approved->value,
            SupportTransactionType::Real->value,
            $period->starts_at,
            $period->ends_at,
            ProfileStatus::Active->value,
        ])->cnt ?? 0;
    }

    /**
     * SUM amount_clp from approved transactions within period.
     */
    private function sumPeriodAmount(RankingPeriod $period): int
    {
        return (int) DB::selectOne("
            SELECT COALESCE(SUM(st.amount_clp), 0) AS total
            FROM support_transactions st
            WHERE st.ranking_period_id = ?
              AND st.status = ?
              AND st.ranking_qualified_at >= ?
              AND st.ranking_qualified_at < ?
        ", [
            $period->id,
            SupportTransactionStatus::Approved->value,
            $period->starts_at,
            $period->ends_at,
        ])->total ?? 0;
    }

    /**
     * COUNT outbound_click_events within current period.
     */
    private function countOutboundClicks(RankingPeriod $period): int
    {
        return (int) DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM outbound_click_events
            WHERE ranking_period_id = ?
              AND created_at >= ?
              AND created_at < ?
        ", [
            $period->id,
            $period->starts_at,
            $period->ends_at,
        ])->cnt ?? 0;
    }

    private function emptyStats(): array
    {
        return [
            'active_profiles' => 0,
            'period_amount' => 0,
            'outbound_clicks' => 0,
        ];
    }
}
