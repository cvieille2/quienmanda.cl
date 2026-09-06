<?php

namespace Tests\Feature;

use App\Enums\ProfileStatus;
use App\Models\ProfileCategory;
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

    public function test_category_page_returns_200(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);
        $this->createProfile(['display_name' => 'Artista', 'category' => 'Música']);

        $response = $this->get('/categorias/musica');
        $response->assertOk();
    }

    public function test_category_page_shows_title(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categorias/musica');
        $response->assertOk();
        $response->assertSee('Música');
    }

    public function test_category_page_returns_404_for_nonexistent(): void
    {
        $response = $this->get('/categorias/no-existe');
        $response->assertNotFound();
    }

    public function test_category_page_has_canonical_url(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);

        $response = $this->get('/categorias/musica');
        $response->assertOk();
        $response->assertSee('quienmanda.cl/categorias/musica');
    }

    public function test_category_page_shows_category_nav(): void
    {
        $this->createCategory(['name' => 'Música', 'slug' => 'musica']);
        $deportesId = $this->createCategory(['name' => 'Deportes', 'slug' => 'deportes']);
        $this->createProfile(['display_name' => 'Deportista', 'category' => 'Deportes', 'slug' => 'deportista-' . uniqid(), 'profile_category_id' => $deportesId]);

        $response = $this->get('/categorias/musica');
        $response->assertOk();
        $response->assertSee('Deportes');
    }
}
