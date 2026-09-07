<?php

namespace App\Services;

use App\Domain\Ranking\RankingContext;
use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankingStatsService
{
    public const CACHE_TTL_SECONDS = 60;

    public function __construct(
        private RankingTimeWindowService $timeWindowService,
    ) {}

    public function getStats(RankingContext $context): array
    {
        $window = $this->timeWindowService->resolve($context->period);
        $cacheKey = sprintf(
            'ranking_stats:%s:%s:%s',
            $context->period->value,
            $context->cacheScope(),
            $this->calendarWindowIdentifier($window),
        );

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($context, $window): array {
            $qualifiedAmount = $this->sumQualifiedAmount($context, $window);

            return [
                'active_profiles' => $this->countActiveProfiles($context, $window),
                'qualified_amount' => $qualifiedAmount,
                'period_amount' => $qualifiedAmount,
                'outbound_clicks' => $this->countOutboundClicks($context, $window),
            ];
        });
    }

    private function countActiveProfiles(RankingContext $context, array $window): int
    {
        $sql = <<<SQL
            SELECT COUNT(DISTINCT st.profile_id) AS cnt
            FROM support_transactions st
            JOIN profiles p ON p.id = st.profile_id
            WHERE st.status = ?
              AND st.type = ?
              AND st.ranking_qualified_at >= ?
              AND st.ranking_qualified_at < ?
              AND p.status = ?
        SQL;

        $bindings = [
            SupportTransactionStatus::Approved->value,
            SupportTransactionType::Real->value,
            $window['starts_at_utc'],
            $window['ends_at_utc'],
            ProfileStatus::Active->value,
        ];

        if ($context->categoryId !== null) {
            $sql .= ' AND p.profile_category_id = ?';
            $bindings[] = $context->categoryId;
        }

        return (int) (optional(DB::selectOne($sql, $bindings))->cnt ?? 0);
    }

    private function sumQualifiedAmount(RankingContext $context, array $window): int
    {
        $sql = <<<SQL
            SELECT COALESCE(SUM(st.amount_clp), 0) AS total
            FROM support_transactions st
            JOIN profiles p ON p.id = st.profile_id
            WHERE st.status = ?
              AND st.type = ?
              AND st.ranking_qualified_at >= ?
              AND st.ranking_qualified_at < ?
        SQL;

        $bindings = [
            SupportTransactionStatus::Approved->value,
            SupportTransactionType::Real->value,
            $window['starts_at_utc'],
            $window['ends_at_utc'],
        ];

        if ($context->categoryId !== null) {
            $sql .= ' AND p.profile_category_id = ?';
            $bindings[] = $context->categoryId;
        }

        return (int) (optional(DB::selectOne($sql, $bindings))->total ?? 0);
    }

    private function countOutboundClicks(RankingContext $context, array $window): int
    {
        $sql = <<<SQL
            SELECT COUNT(*) AS cnt
            FROM outbound_click_events oce
            JOIN profiles p ON p.id = oce.profile_id
            WHERE oce.created_at >= ?
              AND oce.created_at < ?
        SQL;

        $bindings = [$window['starts_at_utc'], $window['ends_at_utc']];

        if ($context->categoryId !== null) {
            $sql .= ' AND p.profile_category_id = ?';
            $bindings[] = $context->categoryId;
        }

        return (int) (optional(DB::selectOne($sql, $bindings))->cnt ?? 0);
    }

    private function calendarWindowIdentifier(array $window): string
    {
        $local = $window['starts_at_local'];

        return match ($window['period']) {
            'today' => $local->format('Y-m-d'),
            'week' => sprintf('%d-W%02d', $local->isoWeekYear(), $local->isoWeek()),
            'month' => $local->format('Y-m'),
            'year' => $local->format('Y'),
            default => $local->format('Y-m-d'),
        };
    }
}
