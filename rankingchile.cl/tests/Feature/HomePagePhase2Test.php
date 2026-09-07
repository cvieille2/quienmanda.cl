<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon as LaravelCarbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomePagePhase2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $now = LaravelCarbon::parse('2026-09-16 12:00:00', 'America/Santiago');
        LaravelCarbon::setTestNow($now);
        CarbonImmutable::setTestNow($now);
    }

    protected function tearDown(): void
    {
        LaravelCarbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

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
            'code' => 'W2026-36',
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

    private function insertSupportTransaction(int $periodId, int $profileId, int $amountClp, string $qualifiedAt): void
    {
        DB::table('support_transactions')->insert([
            'ranking_period_id' => $periodId,
            'profile_id' => $profileId,
            'amount_clp' => $amountClp,
            'currency' => 'CLP',
            'type' => SupportTransactionType::Real->value,
            'status' => SupportTransactionStatus::Approved->value,
            'ranking_qualified_at' => CarbonImmutable::parse($qualifiedAt, 'America/Santiago'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertProfileView(int $profileId, string $createdAt = '2026-09-16 11:00:00'): void
    {
        DB::table('profile_view_events')->insert([
            'profile_id' => $profileId,
            'session_id' => uniqid('session_'),
            'referrer' => 'https://quienmanda.cl/',
            'created_at' => CarbonImmutable::parse($createdAt, 'America/Santiago'),
        ]);
    }

    private function insertDestinationClick(int $profileId, string $createdAt = '2026-09-16 11:00:00'): void
    {
        DB::table('outbound_click_events')->insert([
            'profile_id' => $profileId,
            'ranking_period_id' => null,
            'source' => 'profile',
            'destination_url' => 'https://destino-ejemplo.cl',
            'session_id' => uniqid('session_'),
            'referrer' => 'https://quienmanda.cl/',
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
            'created_at' => CarbonImmutable::parse($createdAt, 'America/Santiago'),
        ]);
    }

    public function test_home_defaults_to_week(): void
    {
        $periodId = $this->createPeriod();
        $leader = $this->createProfile(['display_name' => 'Hoy Lider', 'slug' => 'hoy-lider']);
        $monthly = $this->createProfile(['display_name' => 'Mensual', 'slug' => 'mensual']);
        $yearly = $this->createProfile(['display_name' => 'Anual', 'slug' => 'anual']);

        $this->insertSupportTransaction($periodId, $leader, 10000, '2026-09-06 11:00:00');
        $this->insertSupportTransaction($periodId, $monthly, 5000, '2026-09-02 11:00:00');
        $this->insertSupportTransaction($periodId, $yearly, 3000, '2026-05-06 11:00:00');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('¿Quién manda esta semana?');
        $response->assertSee('esta semana');
        $response->assertSee('Hoy Lider');
    }

    public function test_home_today_changes_headline_and_header_stats(): void
    {
        $periodId = $this->createPeriod();
        $leader = $this->createProfile(['display_name' => 'Hoy Lider', 'slug' => 'hoy-lider']);
        $monthly = $this->createProfile(['display_name' => 'Mensual', 'slug' => 'mensual']);

        $this->insertSupportTransaction($periodId, $leader, 10000, '2026-09-06 11:00:00');
        $this->insertSupportTransaction($periodId, $monthly, 5000, '2026-09-02 11:00:00');

        $response = $this->get('/?period=today');

        $response->assertOk();
        $response->assertSee('¿Quién manda hoy?');
        $response->assertSee('hoy');
        $response->assertSee('Hoy');
        $response->assertSee('Semana');
    }

    public function test_home_month_and_year_expand_the_window(): void
    {
        $periodId = $this->createPeriod();
        $leader = $this->createProfile(['display_name' => 'Hoy Lider', 'slug' => 'hoy-lider']);
        $monthly = $this->createProfile(['display_name' => 'Mensual', 'slug' => 'mensual']);
        $yearly = $this->createProfile(['display_name' => 'Anual', 'slug' => 'anual']);

        $this->insertSupportTransaction($periodId, $leader, 10000, '2026-09-06 11:00:00');
        $this->insertSupportTransaction($periodId, $monthly, 5000, '2026-09-02 11:00:00');
        $this->insertSupportTransaction($periodId, $yearly, 3000, '2026-05-06 11:00:00');

        $monthResponse = $this->get('/?period=month');
        $monthResponse->assertOk();
        $monthResponse->assertSee('¿Quién manda este mes?');
        $monthResponse->assertSee('este mes');

        $yearResponse = $this->get('/?period=year');
        $yearResponse->assertOk();
        $yearResponse->assertSee('¿Quién manda este año?');
        $yearResponse->assertSee('este año');

        $response = $this->get('/?period=month');
        $response->assertOk();
        $response->assertSee('Hoy');
        $response->assertSee('Semana');
        $response->assertSee('Mes');
        $response->assertSee('Año');
    }

    public function test_home_category_and_period_can_combine(): void
    {
        $periodId = $this->createPeriod();
        $musicaId = DB::table('profile_categories')->insertGetId([
            'name' => 'Música',
            'slug' => 'musica',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('profile_categories')->insertGetId([
            'name' => 'Deportes',
            'slug' => 'deportes',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $musico = $this->createProfile(['display_name' => 'Músico', 'slug' => 'musico', 'profile_category_id' => $musicaId, 'category' => 'Música']);
        $deportista = $this->createProfile(['display_name' => 'Deportista', 'slug' => 'deportista', 'category' => 'Deportes']);

        $this->insertSupportTransaction($periodId, $musico, 7000, '2026-09-16 09:00:00');
        $this->insertSupportTransaction($periodId, $deportista, 4000, '2026-09-16 10:00:00');

        $response = $this->get('/?category=musica&period=month');

        $response->assertOk();
        $response->assertSee('¿Quién manda en Música este mes?');
        $response->assertSee('Músico');
        $response->assertDontSee('Deportista');
    }

    public function test_home_shows_profile_views_and_destination_clicks(): void
    {
        $periodId = $this->createPeriod();
        $profile = $this->createProfile(['display_name' => 'Perfil Métrica', 'slug' => 'perfil-metrica']);

        $this->insertSupportTransaction($periodId, $profile, 9000, '2026-09-16 10:00:00');
        $this->insertSupportTransaction($periodId, $this->createProfile(['display_name' => 'Otro', 'slug' => 'otro']), 3000, '2026-09-16 10:05:00');

        for ($i = 0; $i < 4; $i++) {
            $this->insertProfileView($profile);
        }

        for ($i = 0; $i < 3; $i++) {
            $this->insertDestinationClick($profile);
        }

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('4 vistas');
        $response->assertSee('3 clics');
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
        $response->assertSee(route('entrar.index'));
    }

    public function test_home_zero_state_when_no_activity(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Sé el primero en moverlo');
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
