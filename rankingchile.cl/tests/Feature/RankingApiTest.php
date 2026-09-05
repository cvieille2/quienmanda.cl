<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_ranking_endpoint_returns_expected_structure(): void
    {
        $this->seed();

        $response = $this->getJson('/api/ranking/current');

        $response->assertOk()->assertJsonStructure([
            'period' => [
                'external_key',
                'code',
                'starts_at_utc',
                'ends_at_utc',
                'ends_in_seconds',
            ],
            'ranking',
        ]);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
