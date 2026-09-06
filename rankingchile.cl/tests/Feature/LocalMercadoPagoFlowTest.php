<?php

namespace Tests\Feature;

use App\Enums\GatewayProcessingResult;
use App\Enums\SupportTransactionStatus;
use App\Models\PaymentGatewayEvent;
use App\Models\Profile;
use App\Models\ShareEvent;
use App\Models\SupportTransaction;
use App\Services\Payments\LocalMercadoPagoGateway;
use App\Services\Payments\PaymentGatewayInterface;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalMercadoPagoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_checkout_approve_updates_transaction_share_and_ranking(): void
    {
        $this->configureLocalGateway();
        $this->seed(DatabaseSeeder::class);
        $profile = Profile::query()->firstOrFail();

        $checkout = $this->postJson('/api/pagos', [
            'profile_id' => $profile->id,
            'amount_clp' => 2000,
            'supporter_name' => 'Tester Local',
            'is_anonymous' => false,
            'age_declared_18' => true,
            'checkout_token' => 'checkout-local-token',
            'payer_email' => 'tester-local@example.com',
        ])->assertOk();

        $tx = SupportTransaction::where('external_reference', $checkout->json('external_reference'))->firstOrFail();

        $this->assertEquals(SupportTransactionStatus::Pending, $tx->status);
        $this->assertNotNull($tx->ranking_period_id);
        $this->assertNotNull($tx->provider_order_id); // BLOCKER-002: preferencia como provider_order_id
        $this->assertStringContainsString('/local/mercadopago/checkout/', $checkout->json('checkout_url'));

        $this->get($checkout->json('checkout_url'))->assertOk();

        $this->post(route('local.mercadopago.approve', ['token' => $tx->provider_transaction_id]))
            ->assertRedirect(route('payments.return', ['payment_id' => $tx->provider_transaction_id]));

        $this->get(route('payments.return', ['payment_id' => $tx->provider_transaction_id]))
            ->assertRedirect(route('payments.resultado', ['payment_id' => $tx->provider_transaction_id]));

        $tx->refresh();

        $this->assertEquals(SupportTransactionStatus::Approved, $tx->status);
        $this->assertNotNull($tx->provider_approved_at);
        $this->assertNotNull($tx->provider_payment_id); // BLOCKER-003: payment id capturado por webhook
        $this->assertNotNull($tx->webhook_received_at);
        $this->assertNotNull($tx->ranking_qualified_at);
        $this->assertSame(1, ShareEvent::where('support_transaction_id', $tx->id)->count());

        $this->assertDatabaseHas('payment_gateway_events', [
            'gateway_event_id' => $tx->provider_transaction_id,
            'provider_transaction_id' => $tx->provider_transaction_id,
            'external_reference' => $tx->external_reference,
            'processing_result' => GatewayProcessingResult::Processed->value,
        ]);

        $ranking = $this->getJson('/api/ranking/current')->assertOk()->json('ranking');
        $row = collect($ranking)->firstWhere('slug', $profile->slug);

        $this->assertNotNull($row);
        $this->assertSame(2000, $row['total_real_clp']);
    }

    public function test_local_checkout_reject_fails_transaction_without_ranking_or_share(): void
    {
        $this->configureLocalGateway();
        $this->seed(DatabaseSeeder::class);
        $profile = Profile::query()->firstOrFail();

        $checkout = $this->postJson('/api/pagos', [
            'profile_id' => $profile->id,
            'amount_clp' => 3000,
            'age_declared_18' => true,
            'checkout_token' => 'checkout-local-token',
            'payer_email' => 'tester-reject@example.com',
        ])->assertOk();

        $tx = SupportTransaction::where('external_reference', $checkout->json('external_reference'))->firstOrFail();

        $this->post(route('local.mercadopago.reject', ['token' => $tx->provider_transaction_id]))
            ->assertRedirect(route('payments.return', ['payment_id' => $tx->provider_transaction_id]));

        $tx->refresh();

        $this->assertEquals(SupportTransactionStatus::Failed, $tx->status);
        $this->assertNull($tx->ranking_qualified_at);
        $this->assertSame(0, ShareEvent::where('support_transaction_id', $tx->id)->count());
        $this->assertSame(1, PaymentGatewayEvent::count());

        $ranking = $this->getJson('/api/ranking/current')->assertOk()->json('ranking');
        $row = collect($ranking)->firstWhere('slug', $profile->slug);

        $this->assertNotNull($row);
        $this->assertSame(0, $row['total_real_clp']);
    }

    public function test_local_pending_webhook_keeps_transaction_retryable_without_ranking_or_share(): void
    {
        $this->configureLocalGateway();
        $this->seed(DatabaseSeeder::class);
        $profile = Profile::query()->firstOrFail();

        $checkout = $this->postJson('/api/pagos', [
            'profile_id' => $profile->id,
            'amount_clp' => 3500,
            'age_declared_18' => true,
            'checkout_token' => 'checkout-local-token',
            'payer_email' => 'tester-pending@example.com',
        ])->assertOk();

        $tx = SupportTransaction::where('external_reference', $checkout->json('external_reference'))->firstOrFail();

        $this->postJson('/api/webhooks/mercadopago', [
            'type' => 'payment',
            'data' => ['id' => $tx->provider_transaction_id],
            'external_reference' => $tx->external_reference,
        ])->assertOk();

        $tx->refresh();

        $this->assertEquals(SupportTransactionStatus::Pending, $tx->status);
        $this->assertNull($tx->ranking_qualified_at);
        $this->assertSame(0, ShareEvent::where('support_transaction_id', $tx->id)->count());

        $event = PaymentGatewayEvent::query()->firstOrFail();
        $this->assertEquals(GatewayProcessingResult::Pending, $event->processing_result);
        $this->assertNull($event->processed_at);

        $ranking = $this->getJson('/api/ranking/current')->assertOk()->json('ranking');
        $row = collect($ranking)->firstWhere('slug', $profile->slug);

        $this->assertNotNull($row);
        $this->assertSame(0, $row['total_real_clp']);
    }

    public function test_local_double_approve_is_idempotent(): void
    {
        $this->configureLocalGateway();
        $this->seed(DatabaseSeeder::class);
        $profile = Profile::query()->firstOrFail();

        $checkout = $this->postJson('/api/pagos', [
            'profile_id' => $profile->id,
            'amount_clp' => 4000,
            'age_declared_18' => true,
            'checkout_token' => 'checkout-local-token',
            'payer_email' => 'tester-idempotent@example.com',
        ])->assertOk();

        $tx = SupportTransaction::where('external_reference', $checkout->json('external_reference'))->firstOrFail();

        $this->post(route('local.mercadopago.approve', ['token' => $tx->provider_transaction_id]))->assertRedirect();
        $this->post(route('local.mercadopago.approve', ['token' => $tx->provider_transaction_id]))->assertRedirect();

        $this->assertEquals(SupportTransactionStatus::Approved, $tx->fresh()->status);
        $this->assertSame(1, PaymentGatewayEvent::count());
        $this->assertSame(1, ShareEvent::where('support_transaction_id', $tx->id)->count());

        $ranking = $this->getJson('/api/ranking/current')->assertOk()->json('ranking');
        $row = collect($ranking)->firstWhere('slug', $profile->slug);

        $this->assertNotNull($row);
        $this->assertSame(4000, $row['total_real_clp']);
    }

    private function configureLocalGateway(): void
    {
        config(['mercadopago.driver' => 'local']);
        $this->app->bind(PaymentGatewayInterface::class, LocalMercadoPagoGateway::class);
    }
}
