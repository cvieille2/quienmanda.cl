<?php

namespace App\Services;

use App\Enums\RankingPeriodWindow;
use Carbon\CarbonImmutable;

class RankingTimeWindowService
{
    public const TIMEZONE = 'America/Santiago';
    public const DATABASE_TIMEZONE = 'UTC';

    public function resolve(RankingPeriodWindow $period): array
    {
        $now = CarbonImmutable::now(self::TIMEZONE);

        [$startsAtLocal, $endsAtLocal] = match ($period) {
            RankingPeriodWindow::TODAY => [$now->startOfDay(), $now],
            RankingPeriodWindow::WEEK => [$now->startOfWeek(CarbonImmutable::MONDAY), $now],
            RankingPeriodWindow::MONTH => [$now->startOfMonth(), $now],
            RankingPeriodWindow::YEAR => [$now->startOfYear(), $now],
        };

        return [
            'period' => $period->value,
            'label' => $period->label(),
            'starts_at_local' => $startsAtLocal,
            'ends_at_local' => $endsAtLocal,
            'starts_at_utc' => $startsAtLocal->setTimezone(self::DATABASE_TIMEZONE),
            'ends_at_utc' => $endsAtLocal->setTimezone(self::DATABASE_TIMEZONE),
        ];
    }
}
