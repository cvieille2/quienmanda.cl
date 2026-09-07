<?php

namespace App\Services;

use App\Domain\Ranking\RankingContext;
use App\Enums\ProfileStatus;
use App\Enums\RankingPeriodWindow;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\ProfileCategory;
use App\Models\RankingPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankingService
{
    public const MIN_INCREMENT_CLP = 1000;
    public const CACHE_SECONDS = 5; // regla oficial

    public function __construct(
        private RankingPeriodService $periods,
        private RankingTimeWindowService $timeWindowService,
    ) {}

    public function getRankingForFilters(
        string $context = 'home',
        ?string $category = null,
        string $period = 'week',
        int $limit = 20,
    ): array {
        $categoryId = $this->resolveCategoryId($category);
        $rankingContext = new RankingContext(
            period: RankingPeriodWindow::tryFrom($period) ?? RankingPeriodWindow::WEEK,
            categoryId: $categoryId,
        );

        $ranking = $this->getRanking($rankingContext, true);

        return [
            'context' => $context,
            'period' => $rankingContext->period->value,
            'category_id' => $categoryId,
            'category_slug' => $this->resolveCategorySlug($categoryId),
            'ranking' => array_slice($ranking, 0, max(1, $limit)),
            'total' => count($ranking),
        ];
    }

    public function getRanking(RankingContext $context, bool $withDelta = false): array
    {
        $window = $this->timeWindowService->resolve($context->period);

        $sql = "
            SELECT
                p.id AS profile_id, p.slug, p.display_name, p.category, p.profile_image_url,
                p.profile_category_id,
                p.verification_status, p.is_community_created,
                COALESCE(SUM(st.amount_clp), 0) AS total_real_clp,
                COALESCE((
                    SELECT SUM(amount_clp) FROM support_transactions pt
                    WHERE pt.profile_id = p.id
                      AND pt.type = ? AND pt.status = ?
                      AND pt.ranking_qualified_at >= ?
                      AND pt.ranking_qualified_at < ?
                ), 0) AS total_promotional_clp,
                COALESCE((
                    SELECT COUNT(*) FROM profile_view_events pve
                    WHERE pve.profile_id = p.id
                ), 0) AS profile_views_count,
                COALESCE((
                    SELECT COUNT(*) FROM outbound_click_events oce
                    WHERE oce.profile_id = p.id
                ), 0) AS destination_clicks_count,
                COUNT(DISTINCT st.payer_reference_hash) AS supporter_count,
                MAX(st.ranking_qualified_at) AS total_reached_at
            FROM profiles p
            LEFT JOIN support_transactions st
                   ON st.profile_id = p.id
                  AND st.type = ? AND st.status = ?
                  AND st.ranking_qualified_at >= ?
                  AND st.ranking_qualified_at < ?
            WHERE p.status = ?
        ";

        $bindings = [
            SupportTransactionType::Promotional->value,
            SupportTransactionStatus::Approved->value,
            $window['starts_at_utc']->toDateTimeString(),
            $window['ends_at_utc']->toDateTimeString(),
            SupportTransactionType::Real->value,
            SupportTransactionStatus::Approved->value,
            $window['starts_at_utc']->toDateTimeString(),
            $window['ends_at_utc']->toDateTimeString(),
            ProfileStatus::Active->value,
        ];

        if ($context->categoryId !== null) {
            $sql .= " AND p.profile_category_id = ?";
            $bindings[] = $context->categoryId;
        }

        $sql .= "
            GROUP BY p.id, p.slug, p.display_name, p.category,
                     p.profile_category_id,
                     p.verification_status, p.is_community_created
            ORDER BY total_real_clp DESC, total_reached_at ASC
        ";

        $rows = DB::select($sql, $bindings);

        $ranking = [];
        foreach ($rows as $i => $r) {
            $ranking[] = [
                'position' => $i + 1,
                'profile_id' => (int) $r->profile_id,
                'slug' => $r->slug,
                'display_name' => $r->display_name,
                'category' => $r->category,
                'profile_image_url' => $r->profile_image_url,
                'profile_category_id' => $r->profile_category_id !== null ? (int) $r->profile_category_id : null,
                'verification_status' => $r->verification_status,
                'is_community_created' => (bool) $r->is_community_created,
                'total_real_clp' => (int) $r->total_real_clp,
                'total_promotional_clp' => (int) $r->total_promotional_clp,
                'profile_views_count' => (int) $r->profile_views_count,
                'destination_clicks_count' => (int) $r->destination_clicks_count,
                'supporter_count' => (int) $r->supporter_count,
                'total_reached_at' => $r->total_reached_at,
            ];
        }

        if ($withDelta) {
            foreach ($ranking as $i => &$item) {
                if ($i === 0) {
                    $item['behind_clp'] = 0;
                    $item['overtake_above_clp'] = 0;
                    $item['to_number_one_clp'] = 0;
                    continue;
                }

                $item['behind_clp'] = $ranking[$i - 1]['total_real_clp'] - $item['total_real_clp'];
                $item['overtake_above_clp'] = $item['behind_clp'] + self::MIN_INCREMENT_CLP;
                $item['to_number_one_clp'] = ($ranking[0]['total_real_clp'] - $item['total_real_clp']) + self::MIN_INCREMENT_CLP;
            }
            unset($item);
        }

        return $ranking;
    }

    public function activePeriodRanking(bool $withDelta = true): array
    {
        $period = $this->periods->activePeriod();
        if (! $period) {
            return [];
        }

        return $this->rankingForPeriod($period->id, $withDelta);
    }

    public function rankingForPeriod(int $periodId, bool $withDelta = false): array
    {
        $rows = DB::select(
            "
            SELECT
                p.id AS profile_id, p.slug, p.display_name, p.category, p.profile_image_url,
                p.profile_category_id,
                p.verification_status, p.is_community_created,
                COALESCE(SUM(CASE WHEN st.status = ? THEN st.amount_clp ELSE 0 END), 0) AS total_real_clp,
                COALESCE((
                    SELECT SUM(amount_clp) FROM support_transactions pt
                    WHERE pt.profile_id = p.id
                      AND pt.ranking_period_id = ?
                      AND pt.type = ? AND pt.status = ?
                ), 0) AS total_promotional_clp,
                COUNT(DISTINCT st.payer_reference_hash) AS supporter_count,
                MAX(st.ranking_qualified_at) AS total_reached_at
            FROM profiles p
            LEFT JOIN support_transactions st
                   ON st.profile_id = p.id AND st.ranking_period_id = ?
                  AND st.type = ? AND st.status = ?
            WHERE p.status = ?
            GROUP BY p.id, p.slug, p.display_name, p.category,
                     p.profile_category_id,
                     p.verification_status, p.is_community_created
            ORDER BY total_real_clp DESC, total_reached_at ASC
            ",
            [
                SupportTransactionStatus::Approved->value,
                $periodId,
                SupportTransactionType::Promotional->value,
                SupportTransactionStatus::Approved->value,
                $periodId,
                SupportTransactionType::Real->value,
                SupportTransactionStatus::Approved->value,
                ProfileStatus::Active->value,
            ]
        );

        $ranking = [];
        foreach ($rows as $i => $r) {
            $ranking[] = [
                'position'             => $i + 1,
                'profile_id'           => (int) $r->profile_id,
                'slug'                 => $r->slug,
                'display_name'         => $r->display_name,
                'category'             => $r->category,
                'profile_image_url'    => $r->profile_image_url,
                'profile_category_id'  => $r->profile_category_id !== null ? (int) $r->profile_category_id : null,
                'verification_status'   => $r->verification_status,
                'is_community_created'  => (bool) $r->is_community_created,
                'total_real_clp'       => (int) $r->total_real_clp,
                'total_promotional_clp' => (int) $r->total_promotional_clp,
                'supporter_count'      => (int) $r->supporter_count,
                'total_reached_at'     => $r->total_reached_at,
            ];
        }

        if ($withDelta) {
            foreach ($ranking as $i => &$item) {
                if ($i === 0) {
                    $item['behind_clp'] = 0;
                    $item['overtake_above_clp'] = 0;
                    $item['to_number_one_clp'] = 0;
                    continue;
                }

                $item['behind_clp'] = $ranking[$i - 1]['total_real_clp'] - $item['total_real_clp'];
                $item['overtake_above_clp'] = $item['behind_clp'] + self::MIN_INCREMENT_CLP;
                $item['to_number_one_clp'] = ($ranking[0]['total_real_clp'] - $item['total_real_clp']) + self::MIN_INCREMENT_CLP;
            }
            unset($item);
        }

        return $ranking;
    }

    /**
     * Ranking restricted to a category.
     *
     * Reuses the same global ordering rules, then filters by category.
     * A profile belongs to the category when its FK matches, or as a
     * fallback when its legacy category string matches the category name
     * (profiles created before the FK normalization).
     */
    public function rankingForCategory(int $periodId, ProfileCategory $category, bool $withDelta = false): array
    {
        $rows = $this->rankingForPeriod($periodId, $withDelta);

        return array_values(array_filter(
            $rows,
            fn (array $row): bool => $row['profile_category_id'] === $category->id
                || ($row['profile_category_id'] === null && $row['category'] === $category->name)
        ));
    }

    public function periodCompact(): array
    {
        return collect($this->activePeriodRanking(false))->map(fn ($r) => [
            'rank' => $r['position'],
            'slug' => $r['slug'],
            'total_real_clp' => $r['total_real_clp'],
            'position' => $r['position'],
        ])->values()->all();
    }

    public function invalidateCache(): void
    {
        Cache::forget('ranking_period_active');
        Cache::forget('ranking_period_compact');
        Cache::forget('ranking_period_' . $this->periods->activePeriod()?->id);
    }

    private function resolveCategoryId(?string $category): ?int
    {
        if ($category === null || $category === '') {
            return null;
        }

        if (ctype_digit($category)) {
            return (int) $category;
        }

        return ProfileCategory::query()
            ->where('slug', $category)
            ->value('id') ?: null;
    }

    private function resolveCategorySlug(?int $categoryId): ?string
    {
        if ($categoryId === null) {
            return null;
        }

        return ProfileCategory::query()->whereKey($categoryId)->value('slug') ?: null;
    }
}
