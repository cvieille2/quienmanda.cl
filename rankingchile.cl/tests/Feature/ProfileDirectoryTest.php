<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_directory_is_a_public_seo_landing(): void
    {
        DB::table('ranking_periods')->insert([
            'public_id' => '01JPROFILEDIRECTORY0000000000',
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

        DB::table('profiles')->insert([
            'public_id' => '01JPROFILEDIRECTORYPROFILE01',
            'display_name' => 'Creador Directorio',
            'slug' => 'creador-directorio',
            'category' => 'Tecnología',
            'type' => 'public_figure',
            'status' => 'active',
            'verification_status' => 'unverified',
            'is_community_created' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/perfil/');

        $response->assertOk()
            ->assertSee('Perfiles que compiten por mandar')
            ->assertSee('Creador Directorio')
            ->assertSee('canonical');
    }

    public function test_profile_directory_empty_state_invites_first_profile(): void
    {
        $response = $this->get('/perfil/');

        $response->assertOk()
            ->assertSee('Todavía no hay perfiles publicados')
            ->assertSee(route('entrar.index'));
    }
}
