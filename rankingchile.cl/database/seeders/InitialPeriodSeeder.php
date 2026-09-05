<?php

namespace Database\Seeders;

use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use App\Models\RankingPeriod;
use App\Services\RankingPeriodService;
use App\Services\RankingSettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InitialPeriodSeeder extends Seeder
{
    /**
     * Crea el periodo activo inicial (semana corriente).
     * Intervalo half-open (D-041): starts_at <= t < ends_at.
     */
    public function run(): void
    {
        $settingsService = app(RankingSettingsService::class);
        $defaults = $settingsService->defaults();
        $config = $settingsService->frozenConfiguration($defaults);

        $tz = RankingPeriodService::TZ;
        $now = CarbonImmutable::now()->timezone($tz);
        $weekStart = $now->startOfWeek(CarbonImmutable::MONDAY)->startOfDay();
        $weekEnd = $weekStart->addWeek();
        $code = sprintf('W%s-%s', $weekStart->isoWeekYear(), $weekStart->isoWeek());

        RankingPeriod::firstOrCreate(
            ['code' => $code],
            [
                'public_id' => (string) Str::ulid(),
                'period_type' => RankingPeriodType::Weekly->value,
                'starts_at' => $weekStart->timezone('UTC'),
                'ends_at' => $weekEnd->timezone('UTC'),
                'settlement_delay_minutes' => $defaults->settlement_delay_minutes,
                'status' => RankingPeriodStatus::Active->value,
                'configuration' => $config,
            ]
        );
    }
}
