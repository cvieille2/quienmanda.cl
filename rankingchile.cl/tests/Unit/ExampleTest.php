<?php

namespace Tests\Unit;

use App\Models\RankingPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranking_period_uses_half_open_interval(): void
    {
        $period = new RankingPeriod([
            'starts_at' => CarbonImmutable::parse('2026-08-24 00:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-08-31 00:00:00', 'UTC'),
        ]);

        $this->assertTrue($period->includes(CarbonImmutable::parse('2026-08-24 00:00:00', 'UTC')));
        $this->assertTrue($period->includes(CarbonImmutable::parse('2026-08-30 23:59:59', 'UTC')));
        $this->assertFalse($period->includes(CarbonImmutable::parse('2026-08-31 00:00:00', 'UTC')));
    }
}
