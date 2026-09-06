<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\GatewayProcessingResult;
use App\Enums\PaymentGateway;
use App\Enums\PaymentGatewayConfirmationStatus;
use App\Enums\ProfileStatus;
use App\Enums\ProfileType;
use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Models\AuditLog;
use App\Models\FeatureFlag;
use App\Models\PaymentGatewayEvent;
use App\Models\Profile;
use App\Models\RankingPeriod;
use App\Models\SupportTransaction;
use App\Services\Payments\MercadoPagoWebhookSignature;
use App\Services\Payments\PaymentGatewayInterface;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TASK-PROD-007: suite financiera de regresión (BLOCKER-001..006).
 */
class PaymentFinancialTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-webhook-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['mercadopago.webhook_secret' => self::SECRET]);
        FeatureFlag::create(['key' => 'payments_enabled', 'value' => true]);
    }

    #[Test]
    public function pay_001_checkout_assigns_active_period(): void
    {
        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 001');

        $tx = SupportTransaction::create($this->txAttributes($period, $profile));

        $this->assertSame($period->id, (int) $tx->ranking_period_id);
    }

    #[Test]
    public function pay_002_webhook_captures_payment_id_and_confirms_with_it(): void
    {
        $spy = new ConfirmSpyGateway();
        $this->app->bind(PaymentGatewayInterface::class, fn () => $spy);

        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 002');
        $tx = SupportTransaction::create($this->txAttributes($period, $profile));
        $paymentId = 'pay-'.Str::ulid();

        $this->postJson('/api/webhooks/mercadopago', $this->paymentPayload($tx, $paymentId), $this->signatureHeaders($paymentId))
            ->assertOk();

        $tx->refresh();
        $this->assertEquals(SupportTransactionStatus::Approved, $tx->status);
        $this->assertSame($paymentId, $tx->provider_payment_id);
        $this->assertSame($paymentId, $spy->lastConfirmedToken); // BLOCKER-003: confirm con payment id, no con preferencia
    }

    #[Test]
    public function pay_003_amount_mismatch_fails_transaction(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, fn () => new AmountMismatchGateway());

        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 003');
        $tx = SupportTransaction::create($this->txAttributes($period, $profile, 2000));
        $paymentId = 'pay-'.Str::ulid();

        $this->postJson('/api/webhooks/mercadopago', $this->paymentPayload($tx, $paymentId), $this->signatureHeaders($paymentId))
            ->assertOk();

        $tx->refresh();
        $this->assertEquals(SupportTransactionStatus::Failed, $tx->status);
        $this->assertSame(PaymentGatewayConfirmationStatus::Approved->value, $tx->gateway_status);
        $this->assertDatabaseHas('audit_logs', ['event_type' => AuditLog::EVT_PAYMENT_FAILED, 'entity_type' => 'support_transaction', 'entity_id' => $tx->id]);
    }

    #[Test]
    public function pay_004_kill_switch_blocks_webhook_and_keeps_transaction_pending(): void
    {
        FeatureFlag::where('key', 'payments_enabled')->update(['value' => false]);
        $this->app->bind(PaymentGatewayInterface::class, fn () => new ApprovingGateway());

        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 004');
        $tx = SupportTransaction::create($this->txAttributes($period, $profile));
        $paymentId = 'pay-'.Str::ulid();

        $this->postJson('/api/webhooks/mercadopago', $this->paymentPayload($tx, $paymentId), $this->signatureHeaders($paymentId))
            ->assertOk();

        $tx->refresh();
        $this->assertEquals(SupportTransactionStatus::Pending, $tx->status);
        $this->assertDatabaseHas('payment_gateway_events', [
            'gateway_event_id' => $paymentId,
            'processing_result' => GatewayProcessingResult::Ignored->value,
        ]);
    }

    #[Test]
    public function pay_005_unknown_payment_returns_404_and_event_ignored(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, fn () => new ApprovingGateway());

        $paymentId = 'pay-'.Str::ulid();

        $this->postJson('/api/webhooks/mercadopago', $this->paymentPayloadWithoutTx($paymentId), $this->signatureHeaders($paymentId))
            ->assertNotFound();

        $this->assertDatabaseHas('payment_gateway_events', [
            'gateway_event_id' => $paymentId,
            'processing_result' => GatewayProcessingResult::Ignored->value,
        ]);
    }

    #[Test]
    public function pay_006_webhook_without_signature_is_rejected_403(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, fn () => new ApprovingGateway());

        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 006');
        $tx = SupportTransaction::create($this->txAttributes($period, $profile));
        $paymentId = 'pay-'.Str::ulid();

        $this->postJson('/api/webhooks/mercadopago', $this->paymentPayload($tx, $paymentId))
            ->assertForbidden();

        $this->assertSame(0, PaymentGatewayEvent::count());
        $this->assertEquals(SupportTransactionStatus::Pending, $tx->fresh()->status);
    }

    #[Test]
    public function pay_007_webhook_with_valid_signature_is_accepted(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, fn () => new ApprovingGateway());

        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 007');
        $tx = SupportTransaction::create($this->txAttributes($period, $profile));
        $paymentId = 'pay-'.Str::ulid();

        $this->postJson('/api/webhooks/mercadopago', $this->paymentPayload($tx, $paymentId), $this->signatureHeaders($paymentId))
            ->assertOk();

        $this->assertEquals(SupportTransactionStatus::Approved, $tx->fresh()->status);
    }

    #[Test]
    public function pay_008_signature_verifier_rejects_bad_hmac_and_missing_parts(): void
    {
        $dataId = 'pay-123';
        $requestId = 'req-1';

        $good = $this->signatureFor(self::SECRET, $dataId, $requestId, '1699999999');
        $verifier = new MercadoPagoWebhookSignature(self::SECRET);

        $this->assertTrue($verifier->verify($this->signedRequest($good, $requestId), $dataId));

        $bad = $this->signatureFor('other-secret', $dataId, $requestId, '1699999999');
        $this->assertFalse($verifier->verify($this->signedRequest($bad, $requestId), $dataId));

        $wrongId = $this->signatureFor(self::SECRET, 'pay-999', $requestId, '1699999999');
        $this->assertFalse($verifier->verify($this->signedRequest($wrongId, $requestId), $dataId));

        $this->assertFalse($verifier->verify($this->signedRequest('ts=1699999999', $requestId), $dataId));
        $this->assertFalse($verifier->verify($this->unsignedRequest(), $dataId));
    }

    #[Test]
    public function pay_009_return_route_captures_payment_id(): void
    {
        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 009');
        $tx = SupportTransaction::create($this->txAttributes($period, $profile));
        $paymentId = 'pay-'.Str::ulid();

        $this->get(route('payments.return', ['payment_id' => $paymentId, 'external_reference' => $tx->external_reference]))
            ->assertRedirect(route('payments.resultado', ['payment_id' => $paymentId]));

        $this->assertSame($paymentId, $tx->fresh()->provider_payment_id);
    }

    #[Test]
    public function pay_010_resultado_page_renders(): void
    {
        $period = $this->createActivePeriod();
        $profile = $this->createProfile('PAY 010');
        $tx = SupportTransaction::create($this->txAttributes($period, $profile));

        $this->get(route('payments.resultado', ['payment_id' => $tx->provider_transaction_id]))
            ->assertOk()
            ->assertSee('Procesando tu impulso');
    }

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function createActivePeriod(): RankingPeriod
    {
        return RankingPeriod::create([
            'code' => 'W'.substr((string) Str::ulid(), 0, 8),
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::now()->subDay()->timezone('UTC'),
            'ends_at' => CarbonImmutable::now()->addDays(6)->timezone('UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => ['minimum_support_clp' => 1000, 'maximum_support_clp' => 500000],
        ]);
    }

    private function createProfile(string $name): Profile
    {
        return Profile::create([
            'display_name' => $name,
            'slug' => Str::slug($name).'-'.substr((string) Str::ulid(), 0, 6),
            'category' => 'test',
            'status' => ProfileStatus::Active,
            'type' => ProfileType::PublicFigure,
        ]);
    }

    private function txAttributes(RankingPeriod $period, Profile $profile, int $amount = 5000): array
    {
        return [
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => $amount,
            'currency' => Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Pending,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'external_reference' => (string) Str::ulid(),
            'provider_transaction_id' => 'pref-'.Str::ulid(),
            'provider_order_id' => 'pref-'.Str::ulid(),
            'checkout_created_at' => now(),
        ];
    }

    private function paymentPayload(SupportTransaction $tx, string $paymentId): array
    {
        return [
            'type' => 'payment',
            'data' => ['id' => $paymentId],
            'external_reference' => $tx->external_reference,
        ];
    }

    private function paymentPayloadWithoutTx(string $paymentId): array
    {
        return [
            'type' => 'payment',
            'data' => ['id' => $paymentId],
            'external_reference' => 'nonexistent-'.Str::ulid(),
        ];
    }

    private function signatureHeaders(string $paymentId): array
    {
        $requestId = 'req-'.Str::ulid();
        $ts = (string) time();
        $signature = $this->signatureFor(self::SECRET, $paymentId, $requestId, $ts);

        return [
            'x-signature' => $signature,
            'x-request-id' => $requestId,
        ];
    }

    private function signatureFor(string $secret, string $dataId, string $requestId, string $ts): string
    {
        $manifest = 'id:'.$dataId.';request-id:'.$requestId.';ts:'.$ts.';';

        return 'ts='.$ts.',v1='.hash_hmac('sha256', $manifest, $secret);
    }

    private function signedRequest(string $signature, string $requestId): \Illuminate\Http\Request
    {
        $request = \Illuminate\Http\Request::create('/api/webhooks/mercadopago', 'POST', [], [], [], [], '{}');
        $request->headers->set('x-signature', $signature);
        $request->headers->set('x-request-id', $requestId);

        return $request;
    }

    private function unsignedRequest(): \Illuminate\Http\Request
    {
        return \Illuminate\Http\Request::create('/api/webhooks/mercadopago', 'POST', [], [], [], [], '{}');
    }
}

/** Gateway aprobador: confirma siempre como pago aprobado por el monto pedido. */
class ApprovingGateway implements PaymentGatewayInterface
{
    public function __construct(private int $amount = 5000) {}
    public function create(int $amountClp, string $externalReference, string $sessionId, array $metadata = []): array
    {
        return ['url' => 'http://stub', 'token' => 'stub'];
    }

    public function confirm(string $transactionToken): array
    {
        return ['status' => PaymentGatewayConfirmationStatus::Approved->value, 'amount' => $this->amount, 'transaction_id' => $transactionToken, 'approved_at' => now()->toIso8601String(), 'payer_id' => null, 'raw' => []];
    }

    public function isAuthorized(array $confirmation): bool
    {
        return ($confirmation['status'] ?? null) === PaymentGatewayConfirmationStatus::Approved->value;
    }

    public function statusInfo(string $transactionToken): array
    {
        return $this->confirm($transactionToken);
    }
}

/** Gateway que devuelve un monto distinto al esperado para forzar rechazo. */
class AmountMismatchGateway implements PaymentGatewayInterface
{
    public function create(int $amountClp, string $externalReference, string $sessionId, array $metadata = []): array
    {
        return ['url' => 'http://stub', 'token' => 'stub'];
    }

    public function confirm(string $transactionToken): array
    {
        return ['status' => PaymentGatewayConfirmationStatus::Approved->value, 'amount' => 9999, 'transaction_id' => $transactionToken, 'approved_at' => now()->toIso8601String(), 'payer_id' => null, 'raw' => []];
    }

    public function isAuthorized(array $confirmation): bool
    {
        return ($confirmation['status'] ?? null) === PaymentGatewayConfirmationStatus::Approved->value;
    }

    public function statusInfo(string $transactionToken): array
    {
        return $this->confirm($transactionToken);
    }
}

/** Gateway espía: registra el token con que se llamó a confirm(). */
class ConfirmSpyGateway implements PaymentGatewayInterface
{
    public function __construct(private int $amount = 5000) {}
    public ?string $lastConfirmedToken = null;

    public function create(int $amountClp, string $externalReference, string $sessionId, array $metadata = []): array
    {
        return ['url' => 'http://stub', 'token' => 'stub'];
    }

    public function confirm(string $transactionToken): array
    {
        $this->lastConfirmedToken = $transactionToken;

        return ['status' => PaymentGatewayConfirmationStatus::Approved->value, 'amount' => $this->amount, 'transaction_id' => $transactionToken, 'approved_at' => now()->toIso8601String(), 'payer_id' => null, 'raw' => []];
    }

    public function isAuthorized(array $confirmation): bool
    {
        return ($confirmation['status'] ?? null) === PaymentGatewayConfirmationStatus::Approved->value;
    }

    public function statusInfo(string $transactionToken): array
    {
        return $this->confirm($transactionToken);
    }
}
