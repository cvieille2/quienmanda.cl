<?php

namespace Tests\Unit;

use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\ProfileCategory;
use App\Models\RankingPeriod;
use App\Services\CategoryStatsService;
use App\Services\RankingProjectionService;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryServicesTest extends TestCase
{
    use RefreshDatabase;

    private function createPeriod(): RankingPeriod
    {
        $id = DB::table('ranking_periods')->insertGetId([
            'public_id' => uniqid('rp_'),
            'code' => 'W' . date('Y') . '-' . date('W'),
            'period_type' => 'weekly',
            'starts_at' => now()->startOfWeek(),
            'ends_at' => now()->endOfWeek(),
            'settlement_delay_minutes' => 5,
            'status' => 'active',
            'configuration' => json_encode(['minimum_support_clp' => 1000, 'maximum_support_clp' => 500000]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return RankingPeriod::find($id);
    }

    private function createCategory(array $overrides = []): ProfileCategory
    {
        $id = DB::table('profile_categories')->insertGetId(array_merge([
            'name' => 'Música',
            'slug' => 'musica',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return ProfileCategory::find($id);
    }

    private function createProfile(array $overrides = []): int
    {
        return DB::table('profiles')->insertGetId(array_merge([
            'public_id' => uniqid('pf_'),
            'display_name' => $overrides['display_name'] ?? 'Perfil',
            'slug' => $overrides['slug'] ?? 'perfil-' . uniqid(),
            'category' => $overrides['category'] ?? 'Música',
            'type' => 'public_figure',
            'status' => ProfileStatus::Active->value,
            'verification_status' => 'unverified',
            'is_community_created' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function addSupport(int $periodId, int $profileId, int $amount, string $qualifiedAt = 'now'): void
    {
        DB::table('support_transactions')->insert([
            'ranking_period_id' => $periodId,
            'profile_id' => $profileId,
            'amount_clp' => $amount,
            'currency' => 'CLP',
            'type' => SupportTransactionType::Real->value,
            'status' => SupportTransactionStatus::Approved->value,
            'ranking_qualified_at' => $qualifiedAt === 'now' ? now() : $qualifiedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_ranking_for_category_only_returns_category_profiles(): void
    {
        $period = $this->createPeriod();
        $category = $this->createCategory();
        $p1 = $this->createProfile(['display_name' => 'Músico A', 'slug' => 'musico-a', 'category' => 'Música', 'profile_category_id' => $category->id]);
        $p2 = $this->createProfile(['display_name' => 'Deportista', 'slug' => 'deportista', 'category' => 'Deportes']);

        $this->addSupport($period->id, $p1, 5000);
        $this->addSupport($period->id, $p2, 9000);

        $ranking = app(RankingService::class)->rankingForCategory($period->id, $category, false);

        $this->assertCount(1, $ranking);
        $this->assertSame('Músico A', $ranking[0]['display_name']);
        $this->assertSame(5000, $ranking[0]['total_real_clp']);
    }

    public function test_ranking_for_category_falls_back_to_legacy_category_string(): void
    {
        $period = $this->createPeriod();
        $category = $this->createCategory(['name' => 'Música', 'slug' => 'musica']);
        // Legacy profile: profile_category_id NULL but category string matches
        $p1 = $this->createProfile(['display_name' => 'Legacy', 'slug' => 'legacy-' . uniqid(), 'category' => 'Música']);

        $this->addSupport($period->id, $p1, 3000);

        $ranking = app(RankingService::class)->rankingForCategory($period->id, $category, false);

        $this->assertCount(1, $ranking);
        $this->assertSame('Legacy', $ranking[0]['display_name']);
    }

    public function test_category_stats_are_cached_and_invalidated(): void
    {
        $period = $this->createPeriod();
        $category = $this->createCategory();
        $p1 = $this->createProfile(['display_name' => 'Músico', 'slug' => 'musico-' . uniqid(), 'category' => 'Música', 'profile_category_id' => $category->id]);

        $this->addSupport($period->id, $p1, 10000);

        $service = app(CategoryStatsService::class);
        $stats = $service->forCategory($period, $category);

        $this->assertSame(1, $stats['active_profiles']);
        $this->assertSame(10000, $stats['period_amount']);
        $this->assertSame(0, $stats['outbound_clicks']);

        // Add another tx and verify cached value does not change until invalidate
        $this->addSupport($period->id, $p1, 20000);
        $cachedStats = $service->forCategory($period, $category);
        $this->assertSame(10000, $cachedStats['period_amount']);

        $service->invalidate($period, $category);
        $freshStats = $service->forCategory($period, $category);
        $this->assertSame(30000, $freshStats['period_amount']);
    }

    public function test_projection_available_positions_is_category_scoped(): void
    {
        $period = $this->createPeriod();
        $category = $this->createCategory();
        $pMusica = $this->createProfile(['display_name' => 'Músico', 'slug' => 'msic-' . uniqid(), 'category' => 'Música', 'profile_category_id' => $category->id]);
        $pDeporte = $this->createProfile(['display_name' => 'Deportista', 'slug' => 'depo-' . uniqid(), 'category' => 'Deportes']);

        $this->addSupport($period->id, $pMusica, 20000);
        $this->addSupport($period->id, $pDeporte, 80000);

        $full = app(RankingProjectionService::class)->projectAvailablePositions($period);
        $scoped = app(RankingProjectionService::class)->projectAvailablePositions($period, null, $category);

        // Global #1 requires beating 80000; category #1 requires beating 20000
        $globalTop = collect($full)->firstWhere('position', 1);
        $categoryTop = collect($scoped)->firstWhere('position', 1);

        $this->assertSame(81000, $globalTop['amount']);
        $this->assertSame(21000, $categoryTop['amount']);
    }
}
