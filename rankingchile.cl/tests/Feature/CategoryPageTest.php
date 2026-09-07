<?php

namespace Tests\Feature;

use App\Enums\ProfileStatus;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    private function createCategory(array $overrides = []): int
    {
        return DB::table('profile_categories')->insertGetId(array_merge([
            'name' => $overrides['name'] ?? 'Test Category',
            'slug' => $overrides['slug'] ?? 'test-category',
            'is_active' => $overrides['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
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

    public function test_canonical_route_returns_200(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);
        $this->createProfile(['display_name' => 'Artista', 'category' => 'Música']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
    }

    public function test_legacy_route_redirects_301(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categorias/musica');
        $response->assertRedirect(route('category.show', 'musica', false));
        $response->assertStatus(301);
    }

    public function test_category_page_returns_404_for_nonexistent(): void
    {
        $response = $this->get('/categoria/no-existe');
        $response->assertNotFound();
    }

    public function test_category_page_shows_hero_title(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('¿Quién manda en Música esta semana?');
    }

    public function test_category_page_has_canonical_url(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('quienmanda.cl/categoria/musica');
    }

    public function test_category_page_shows_breadcrumbs(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('Inicio');
        $response->assertSee('Categorías');
        $response->assertSee('Música');
    }

    public function test_category_page_has_position_selector(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('¿Hasta dónde quieres subir?');
    }

    public function test_category_page_has_checkout_modal(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('Tu inversión promocional suma');
    }

    public function test_category_page_has_itemlist_schema(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);
        $p1 = $this->createProfile(['display_name' => 'Artista A', 'slug' => 'artista-a', 'category' => 'Música']);
        $periodId = $this->createPeriod();

        DB::table('support_transactions')->insert([
            'ranking_period_id' => $periodId,
            'profile_id' => $p1,
            'amount_clp' => 5000,
            'currency' => 'CLP',
            'type' => SupportTransactionType::Real->value,
            'status' => SupportTransactionStatus::Approved->value,
            'ranking_qualified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('ItemList', false);
        $response->assertSee('Artista A');
    }

    public function test_category_page_has_breadcrumb_schema(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('BreadcrumbList', false);
    }

    public function test_category_page_shows_category_nav(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);
        $deportesId = $this->createCategory(['name' => 'Deportes', 'slug' => 'deportes']);
        $this->createProfile(['display_name' => 'Deportista', 'category' => 'Deportes', 'slug' => 'deportista-' . uniqid(), 'profile_category_id' => $deportesId]);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('Deportes');
    }

    public function test_category_listing_links_to_canonical_category_route(): void
    {
        $this->createPeriod();
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categorias');
        $response->assertOk();
        $response->assertSee(route('category.show', 'musica', false), false);
    }

    public function test_categorias_index_route_is_not_registered(): void
    {
        $response = $this->get('/categorias-index');
        $response->assertNotFound();
    }

    public function test_category_page_filters_ranking_by_category(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);
        $periodId = $this->createPeriod();
        $p1 = $this->createProfile(['display_name' => 'Músico', 'slug' => 'musico', 'category' => 'Música']);
        $p2 = $this->createProfile(['display_name' => 'Deportista', 'slug' => 'deportista', 'category' => 'Deportes']);

        DB::table('support_transactions')->insert([
            'ranking_period_id' => $periodId,
            'profile_id' => $p1,
            'amount_clp' => 5000,
            'currency' => 'CLP',
            'type' => SupportTransactionType::Real->value,
            'status' => SupportTransactionStatus::Approved->value,
            'ranking_qualified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('Músico');
        $response->assertDontSee('Deportista');
    }

    public function test_category_page_shows_faq_section(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categoria/musica');
        $response->assertOk();
        $response->assertSee('Preguntas frecuentes');
        $response->assertSee('¿Cómo funciona el ranking');
    }

    public function test_category_listing_shows_first_place_cta_when_empty(): void
    {
        $this->createPeriod();
        $this->createCategory(['name' => 'Aparte', 'slug' => 'aparte']);

        $response = $this->get('/categorias');
        $response->assertOk();
        $response->assertSee('SER EL PRIMERO AHORA', false);
    }

    public function test_category_listing_shows_top1_and_fight_cta_when_one(): void
    {
        $periodId = $this->createPeriod();
        $p1 = $this->createProfile(['display_name' => 'Único Líder', 'slug' => 'unico-lider', 'category' => 'Solo']);

        DB::table('support_transactions')->insert([
            'ranking_period_id' => $periodId,
            'profile_id' => $p1,
            'amount_clp' => 5000,
            'currency' => 'CLP',
            'type' => SupportTransactionType::Real->value,
            'status' => SupportTransactionStatus::Approved->value,
            'ranking_qualified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/categorias');
        $response->assertOk();
        $response->assertSee('Único Líder');
        $response->assertSee('DAR PELEA', false);
    }

    public function test_category_listing_shows_top2_and_fight_cta_when_two(): void
    {
        $periodId = $this->createPeriod();
        $p1 = $this->createProfile(['display_name' => 'Primero', 'slug' => 'primero', 'category' => 'Dupla']);
        $p2 = $this->createProfile(['display_name' => 'Segundo', 'slug' => 'segundo', 'category' => 'Dupla']);

        DB::table('support_transactions')->insert([
            ['ranking_period_id' => $periodId, 'profile_id' => $p1, 'amount_clp' => 5000, 'currency' => 'CLP', 'type' => SupportTransactionType::Real->value, 'status' => SupportTransactionStatus::Approved->value, 'ranking_qualified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['ranking_period_id' => $periodId, 'profile_id' => $p2, 'amount_clp' => 3000, 'currency' => 'CLP', 'type' => SupportTransactionType::Real->value, 'status' => SupportTransactionStatus::Approved->value, 'ranking_qualified_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->get('/categorias');
        $response->assertOk();
        $response->assertSee('Primero');
        $response->assertSee('Segundo');
        $response->assertSee('DAR PELEA', false);
    }

    public function test_category_listing_shows_top3_without_fight_cta_when_three(): void
    {
        $periodId = $this->createPeriod();
        $p1 = $this->createProfile(['display_name' => 'Top A', 'slug' => 'top-a', 'category' => 'Trío']);
        $p2 = $this->createProfile(['display_name' => 'Top B', 'slug' => 'top-b', 'category' => 'Trío']);
        $p3 = $this->createProfile(['display_name' => 'Top C', 'slug' => 'top-c', 'category' => 'Trío']);

        $base = ['ranking_period_id' => $periodId, 'currency' => 'CLP', 'type' => SupportTransactionType::Real->value, 'status' => SupportTransactionStatus::Approved->value, 'ranking_qualified_at' => now(), 'created_at' => now(), 'updated_at' => now()];
        DB::table('support_transactions')->insert([
            [...$base, 'profile_id' => $p1, 'amount_clp' => 9000],
            [...$base, 'profile_id' => $p2, 'amount_clp' => 6000],
            [...$base, 'profile_id' => $p3, 'amount_clp' => 3000],
        ]);

        $response = $this->get('/categorias');
        $response->assertOk();
        $response->assertSee('Top A');
        $response->assertSee('Top B');
        $response->assertSee('Top C');
        $response->assertDontSee('DAR PELEA');
    }

    public function test_category_listing_entrar_cta_prefills_category(): void
    {
        $this->createPeriod();
        $this->createCategory(['name' => 'Aparte', 'slug' => 'aparte']);

        $response = $this->get('/categorias');
        $response->assertOk();
        $response->assertSee('entrar?category=Aparte');
    }
}
