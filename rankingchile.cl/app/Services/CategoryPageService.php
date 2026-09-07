<?php

namespace App\Services;

use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\ProfileCategory;
use App\Models\RankingPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class CategoryPageService
{
    public const RANKING_LIMIT = 50;
    public const RECENT_ACTIVITY_LIMIT = 10;
    public const OTHER_CATEGORIES_LIMIT = 8;

    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
        private RankingProjectionService $projection,
        private CategoryStatsService $stats,
    ) {}

    /**
     * Resolve the category that owns a slug, active only.
     */
    public function resolve(string $slug): ?ProfileCategory
    {
        return ProfileCategory::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Build all data needed for the category home page.
     */
    public function show(ProfileCategory $category): array
    {
        $period = $this->periods->activePeriod();
        $ranking = $period
            ? $this->ranking->rankingForCategory($period->id, $category, true)
            : [];

        // Truncate to the official rendering limit (positions deeper than the
        // limit still affect pricing via projection, not the visible list).
        $visibleRanking = array_slice($ranking, 0, self::RANKING_LIMIT);

        $cfg = $period?->configuration ?? [];

        $categoriesForNav = ProfileCategory::query()
            ->active()
            ->whereHas('profiles', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        return [
            'category'         => $category,
            'period'           => $period,
            'ranking'          => $visibleRanking,
            'leader'           => $ranking[0] ?? null,
            'limits'           => [
                'min' => (int) ($cfg['minimum_support_clp'] ?? PaymentLimitsService::MIN_CLP),
                'max' => (int) ($cfg['maximum_support_clp'] ?? PaymentLimitsService::MAX_CLP),
            ],
            'positionPricing'  => $this->projection->projectAvailablePositions($period, null, $category),
            'stats'            => $period ? $this->stats->forCategory($period, $category) : $this->emptyStats(),
            'recentActivity'   => $period ? $this->recentActivity($period, $category) : [],
            'otherCategories'  => $this->otherCategories($category),
            'categories'       => $categoriesForNav,
            'activeCategorySlug' => $category->slug,
            'paymentsEnabled'  => \App\Models\FeatureFlag::enabled(\App\Models\FeatureFlag::KEY_PAYMENTS_ENABLED),
            'checkoutToken'    => Str::random(32),
        ];
    }

    /**
     * Most recent approved real transactions in this category.
     */
    private function recentActivity(RankingPeriod $period, ProfileCategory $category): array
    {
        $rows = DB::select("
            SELECT st.id, st.amount_clp, st.supporter_name, st.created_at,
                   p.id AS profile_id, p.slug AS profile_slug, p.display_name AS profile_name
            FROM support_transactions st
            JOIN profiles p ON p.id = st.profile_id
            LEFT JOIN profile_categories pc ON pc.id = p.profile_category_id
            WHERE st.ranking_period_id = ?
              AND st.status = ?
              AND st.type = ?
              AND st.ranking_qualified_at >= ?
              AND st.ranking_qualified_at < ?
              AND (
                  p.profile_category_id = ?
                  OR (p.profile_category_id IS NULL AND p.category = ?)
              )
            ORDER BY st.ranking_qualified_at DESC, st.id DESC
            LIMIT ?
        ", [
            $period->id,
            SupportTransactionStatus::Approved->value,
            SupportTransactionType::Real->value,
            $period->starts_at,
            $period->ends_at,
            $category->id,
            $category->name,
            self::RECENT_ACTIVITY_LIMIT,
        ]);

        return array_map(static fn ($row) => [
            'profile_slug'   => $row->profile_slug,
            'profile_name'   => $row->profile_name,
            'amount_clp'     => (int) $row->amount_clp,
            'supporter_name' => $row->supporter_name,
            'created_at'     => CarbonImmutable::parse($row->created_at),
        ], $rows);
    }

    /**
     * Other active categories with activity, excluding the current one.
     */
    private function otherCategories(ProfileCategory $category): Collection
    {
        return ProfileCategory::query()
            ->active()
            ->where('id', '!=', $category->id)
            ->whereHas('profiles', fn ($q) => $q->where('status', 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(self::OTHER_CATEGORIES_LIMIT)
            ->get();
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
