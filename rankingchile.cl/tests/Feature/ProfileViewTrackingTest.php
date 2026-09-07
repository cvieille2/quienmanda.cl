<?php

namespace Tests\Feature;

use App\Enums\ProfileStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileViewTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_show_records_a_view_event(): void
    {
        $profileId = DB::table('profiles')->insertGetId([
            'public_id' => uniqid('pf_'),
            'display_name' => 'Perfil Visto',
            'slug' => 'perfil-visto',
            'category' => 'General',
            'type' => 'public_figure',
            'status' => ProfileStatus::Active->value,
            'verification_status' => 'unverified',
            'is_community_created' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/perfil/perfil-visto');

        $response->assertOk();
        $this->assertSame(1, DB::table('profile_view_events')->where('profile_id', $profileId)->count());
    }
}
