<?php

namespace App\Services;

use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\ProfileCategory;
use App\Models\RankingPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryStatsService
{
    public const CACHE_TTL_SECONDS = 60;

    public function __construct(
        private RankingPeriodService $periods,
    ) {}

    /**
     * Stats for a category within an active period, cached for 60s.
     */
    public function forCategory(RankingPeriod $period, ProfileCategory $category): array
    {
        $cacheKey = $this->cacheKey($period, $category);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, fn () => $this->calculate($period, $category));
    }

    public function invalidate(RankingPeriod $period, ProfileCategory $category): void
    {
        Cache::forget($this->cacheKey($period, $category));
    }

    private function cacheKey(RankingPeriod $period, ProfileCategory $category): string
    {
        return "category_stats:{$period->id}:{$category->id}";
    }

    private function calculate(RankingPeriod $period, ProfileCategory $category): array
    {
        return [
            'active_profiles'  => $this->countActiveProfiles($period, $category),
            'period_amount'    => $this->sumPeriodAmount($period, $category),
            'outbound_clicks'  => $this->countOutboundClicks($period, $category),
        ];
    }

    private function countActiveProfiles(RankingPeriod $period, ProfileCategory $category): int
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
              AND (p.profile_category_id = ? OR (p.profile_category_id IS NULL AND p.category = ?))
        ", [
            $period->id,
            SupportTransactionStatus::Approved->value,
            SupportTransactionType::Real->value,
            $period->starts_at,
            $period->ends_at,
            ProfileStatus::Active->value,
            $category->id,
            $category->name,
        ])->cnt ?? 0;
    }

    private function sumPeriodAmount(RankingPeriod $period, ProfileCategory $category): int
    {
        return (int) DB::selectOne("
            SELECT COALESCE(SUM(st.amount_clp), 0) AS total
            FROM support_transactions st
            JOIN profiles p ON p.id = st.profile_id
            WHERE st.ranking_period_id = ?
              AND st.status = ?
              AND st.ranking_qualified_at >= ?
              AND st.ranking_qualified_at < ?
              AND (p.profile_category_id = ? OR (p.profile_category_id IS NULL AND p.category = ?))
        ", [
            $period->id,
            SupportTransactionStatus::Approved->value,
            $period->starts_at,
            $period->ends_at,
            $category->id,
            $category->name,
        ])->total ?? 0;
    }

    private function countOutboundClicks(RankingPeriod $period, ProfileCategory $category): int
    {
        return (int) DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM outbound_click_events oce
            JOIN profiles p ON p.id = oce.profile_id
            WHERE oce.ranking_period_id = ?
              AND oce.created_at >= ?
              AND oce.created_at < ?
              AND (p.profile_category_id = ? OR (p.profile_category_id IS NULL AND p.category = ?))
        ", [
            $period->id,
            $period->starts_at,
            $period->ends_at,
            $category->id,
            $category->name,
        ])->cnt ?? 0;
    }
}
