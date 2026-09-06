<?php

namespace Tests\Feature;

use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\ProfileCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomePagePhase2Test extends TestCase
{
    use RefreshDatabase;

    private function createProfile(array $overrides = []): int
    {
        return DB::table('profiles')->insertGetId(array_merge([
            'public_id' => uniqid('pf_'),
            'display_name' => $overrides['display_name'] ?? 'Test Profile',
            'slug' => $overrides['slug'] ?? 'test-' . uniqid(),
            'category' => $overrides['category'] ?? 'General',
            'type' => 'public_figure',
            'status' => ProfileStatus::Active->value,
            'verification_status' => 'unverified',
            'is_community_created' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function createPeriod(): int
    {
        return DB::table('ranking_periods')->insertGetId([
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
    }

    public function test_home_renders_with_brand_not_domain(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('QUIÉN MANDA');
    }

    public function test_home_renders_conversion_hero(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('¿QUIERES MANDAR?');
        $response->assertSee('ESTA SEMANA');
    }

    public function test_home_shows_position_selector(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('¿Hasta dónde quieres subir?');
    }

    public function test_home_hero_links_to_entrar(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee(route('entrar.index'));
    }

    public function test_home_shows_entrar_y_subir_cta(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('ENTRAR Y SUBIR');
    }

    public function test_home_zero_state_when_no_activity(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('La semana acaba de empezar');
    }

    public function test_home_ranking_shows_position_aware_copy(): void
    {
        $periodId = $this->createPeriod();
        $p1 = $this->createProfile(['display_name' => 'Líder', 'slug' => 'lider']);
        $p2 = $this->createProfile(['display_name' => 'Retador', 'slug' => 'retador']);

        DB::table('support_transactions')->insert([
            ['ranking_period_id' => $periodId, 'profile_id' => $p1, 'amount_clp' => 10000, 'currency' => 'CLP', 'type' => 'real', 'status' => 'approved', 'ranking_qualified_at' => now()->subHour(), 'created_at' => now(), 'updated_at' => now()],
            ['ranking_period_id' => $periodId, 'profile_id' => $p2, 'amount_clp' => 5000, 'currency' => 'CLP', 'type' => 'real', 'status' => 'approved', 'ranking_qualified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('DEFENDER LA CORONA');
        $response->assertSee('Líder');
        $response->assertSee('Retador');
    }
}
