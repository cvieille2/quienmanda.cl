<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_contains_footer_with_rules_link(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee(route('rules'));
        $response->assertSee('Reglas');
    }

    public function test_homepage_contains_footer_with_terms_link(): void
    {
        $response = $this->get('/');
        $response->assertSee(route('legal.terms'));
        $response->assertSee('Términos');
    }

    public function test_homepage_contains_footer_with_privacy_link(): void
    {
        $response = $this->get('/');
        $response->assertSee(route('legal.privacy'));
        $response->assertSee('Privacidad');
    }

    public function test_homepage_contains_footer_with_contact_email(): void
    {
        $this->get('/')->assertSee('contacto@quienmanda.cl');
    }

    public function test_terms_page_contains_footer_with_rules_link(): void
    {
        $response = $this->get('/terminos');
        $response->assertOk();
        $response->assertSee(route('rules'));
    }

    public function test_privacy_page_contains_footer_with_rules_link(): void
    {
        $response = $this->get('/privacidad');
        $response->assertOk();
        $response->assertSee(route('rules'));
    }

    public function test_rules_page_contains_footer_with_all_legal_links(): void
    {
        $response = $this->get('/reglas');
        $response->assertOk();
        $response->assertSee(route('rules'));
        $response->assertSee(route('legal.terms'));
        $response->assertSee(route('legal.privacy'));
        $response->assertSee('contacto@quienmanda.cl');
    }
}
