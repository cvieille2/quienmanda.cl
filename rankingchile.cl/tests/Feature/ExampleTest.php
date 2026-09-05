<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_success(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_pending_payment_route_returns_json(): void
    {
        $this->getJson('/pagos/demo-slug/pendiente')
            ->assertOk()
            ->assertJson([
                'status' => 'pending_confirmation',
                'slug' => 'demo-slug',
            ]);
    }
}
