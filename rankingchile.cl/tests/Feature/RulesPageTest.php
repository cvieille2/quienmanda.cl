<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RulesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_rules_page_returns_200(): void
    {
        $this->get('/reglas')->assertOk();
    }

    public function test_rules_page_contains_title(): void
    {
        $this->get('/reglas')->assertSee('Reglas de Quién Manda');
    }

    public function test_rules_page_explains_only_confirmed_payments_affect_ranking(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('pagos confirmados');
        $response->assertSee('no cambia el ranking');
    }

    public function test_rules_page_explains_can_be_outbid(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('Puedes ser superado después');
        $response->assertSee('no significa comprar una posición permanente');
    }

    public function test_rules_page_explains_no_guarantees(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('Lo que no garantizamos');
        $response->assertSee('Seguidores');
        $response->assertSee('Ventas');
        $response->assertSee('Clics');
    }

    public function test_rules_page_contains_link_to_terms(): void
    {
        $this->get('/reglas')->assertSee(route('legal.terms'));
    }

    public function test_rules_page_contains_contact_email(): void
    {
        $this->get('/reglas')->assertSee('contacto@quienmanda.cl');
    }

    public function test_rules_page_explains_ties(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('empates');
        $response->assertSee('monto');
    }

    public function test_rules_page_explains_pending_payments_do_not_count(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('pago pendiente');
        $response->assertSee('no cambia el ranking');
    }

    public function test_rules_page_explains_ranking_periods(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('períodos');
        $response->assertSee('cierre');
    }

    public function test_rules_page_explains_late_payments(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('Pagos confirmados tarde');
        $response->assertSee('no borra silenciosamente');
    }

    public function test_rules_page_explains_reversals(): void
    {
        $this->get('/reglas')->assertSee('anulados, revertidos o fraudulentos');
    }

    public function test_rules_page_explains_not_endorsement(): void
    {
        $this->get('/reglas')->assertSee('no representa una evaluación');
    }

    public function test_rules_page_explains_visibility_purchase(): void
    {
        $response = $this->get('/reglas');
        $response->assertSee('visibilidad y participación promocional');
        $response->assertSee('No compra seguidores');
    }

    public function test_rules_page_has_correct_seo_headers(): void
    {
        $this->get('/reglas')->assertHeader('X-Robots-Tag', 'index, follow');
    }
}
