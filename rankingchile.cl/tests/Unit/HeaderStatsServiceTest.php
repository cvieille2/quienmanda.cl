<?php

namespace Tests\Unit;

use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\Profile;
use App\Models\RankingPeriod;
use App\Services\HeaderStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HeaderStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private HeaderStatsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HeaderStatsService::class);
    }

    private function createPeriod(string $status = 'active'): RankingPeriod
    {
        $id = DB::table('ranking_periods')->insertGetId([
            'public_id' => uniqid('rp_'),
            'code' => 'W' . date('Y') . '-' . date('W'),
            'period_type' => 'weekly',
            'starts_at' => now()->startOfWeek(),
            'ends_at' => now()->endOfWeek(),
            'settlement_delay_minutes' => 5,
            'status' => $status,
            'configuration' => json_encode(['minimum_support_clp' => 1000, 'maximum_support_clp' => 500000]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return RankingPeriod::find($id);
    }

    private function createProfile(string $status = 'active'): Profile
    {
        $id = DB::table('profiles')->insertGetId([
            'public_id' => uniqid('pf_'),
            'display_name' => 'Test Profile ' . rand(1, 999),
            'slug' => 'test-' . uniqid(),
            'category' => 'General',
            'type' => 'public_figure',
            'status' => $status,
            'verification_status' => 'unverified',
            'is_community_created' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Profile::find($id);
    }

    public function test_empty_when_no_active_period(): void
    {
        $stats = $this->service->forActivePeriod();
        $this->assertEquals(0, $stats['active_profiles']);
        $this->assertEquals(0, $stats['period_amount']);
        $this->assertEquals(0, $stats['outbound_clicks']);
    }

    public function test_counts_active_profiles_with_approved_transactions(): void
    {
        $period = $this->createPeriod();
        $profile = $this->createProfile();

        DB::table('support_transactions')->insert([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 5000,
            'currency' => 'CLP',
            'type' => SupportTransactionType::Real->value,
            'status' => SupportTransactionStatus::Approved->value,
            'ranking_qualified_at' => $period->starts_at->copy()->addHour(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stats = $this->service->forActivePeriod();
        $this->assertEquals(1, $stats['active_profiles']);
        $this->assertEquals(5000, $stats['period_amount']);
    }

    public function test_sums_period_amount_correctly(): void
    {
        $period = $this->createPeriod();
        $p1 = $this->createProfile();
        $p2 = $this->createProfile();

        DB::table('support_transactions')->insert([
            ['ranking_period_id' => $period->id, 'profile_id' => $p1->id, 'amount_clp' => 3000, 'currency' => 'CLP', 'type' => 'real', 'status' => 'approved', 'ranking_qualified_at' => $period->starts_at->copy()->addHour(), 'created_at' => now(), 'updated_at' => now()],
            ['ranking_period_id' => $period->id, 'profile_id' => $p2->id, 'amount_clp' => 7000, 'currency' => 'CLP', 'type' => 'real', 'status' => 'approved', 'ranking_qualified_at' => $period->starts_at->copy()->addHours(2), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $stats = $this->service->forActivePeriod();
        $this->assertEquals(2, $stats['active_profiles']);
        $this->assertEquals(10000, $stats['period_amount']);
    }

    public function test_excludes_pending_transactions(): void
    {
        $period = $this->createPeriod();
        $profile = $this->createProfile();

        DB::table('support_transactions')->insert([
            'ranking_period_id' => $period->id, 'profile_id' => $profile->id,
            'amount_clp' => 5000, 'currency' => 'CLP', 'type' => 'real', 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $stats = $this->service->forActivePeriod();
        $this->assertEquals(0, $stats['active_profiles']);
        $this->assertEquals(0, $stats['period_amount']);
    }

    public function test_excludes_inactive_profiles(): void
    {
        $period = $this->createPeriod();
        $profile = $this->createProfile('pending_review');

        DB::table('support_transactions')->insert([
            'ranking_period_id' => $period->id, 'profile_id' => $profile->id,
            'amount_clp' => 5000, 'currency' => 'CLP', 'type' => 'real', 'status' => 'approved',
            'ranking_qualified_at' => $period->starts_at->copy()->addHour(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $stats = $this->service->forActivePeriod();
        $this->assertEquals(0, $stats['active_profiles']);
    }

    public function test_cache_invalidation_clears_stats(): void
    {
        $period = $this->createPeriod();
        $this->service->forActivePeriod();
        $this->service->invalidate($period);
        $this->assertNull(Cache::get("header_stats:{$period->id}"));
    }
}
