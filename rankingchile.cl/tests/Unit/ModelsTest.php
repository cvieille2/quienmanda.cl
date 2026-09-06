<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Enums\GatewayProcessingResult;
use App\Enums\PaymentGateway;
use App\Enums\ProfileClaimStatus;
use App\Enums\ProfileReportStatus;
use App\Enums\ProfileStatus;
use App\Enums\ProfileSubmissionStatus;
use App\Enums\ProfileType;
use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use Illuminate\Support\Facades\Cache;
use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\FeatureFlag;
use App\Models\PaymentGatewayEvent;
use App\Models\Profile;
use App\Models\ProfileClaim;
use App\Models\ProfileReport;
use App\Models\ProfileSubmission;
use App\Models\RankingPeriod;
use App\Models\RankingSetting;
use App\Models\RankingSnapshot;
use App\Models\ShareEvent;
use App\Models\SupportTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ModelsTest extends \Tests\TestCase
{
    use RefreshDatabase;

    #[Test]
    public function profile_generates_public_id_on_creating(): void
    {
        $profile = Profile::create([
            'display_name' => 'Test Profile',
            'slug' => 'test-profile',
            'category' => 'test',
            'status' => ProfileStatus::Active,
            'type' => ProfileType::PublicFigure,
        ]);

        $this->assertNotNull($profile->public_id);
        $this->assertEquals(26, strlen($profile->public_id));
    }

    #[Test]
    public function profile_preserves_existing_public_id(): void
    {
        $profile = Profile::create([
            'public_id' => 'custom-public-id-1234567890123',
            'display_name' => 'Test Profile',
            'slug' => 'test-profile-preserve',
            'category' => 'test',
            'status' => ProfileStatus::Active,
            'type' => ProfileType::PublicFigure,
        ]);

        $this->assertSame('custom-public-id-1234567890123', $profile->public_id);
    }

    #[Test]
    public function profile_casts_enums_correctly(): void
    {
        $profile = Profile::create([
            'display_name' => 'Enum Test',
            'slug' => 'enum-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
            'type' => ProfileType::Community,
            'verification_status' => VerificationStatus::Verified,
            'is_community_created' => true,
        ]);

        $this->assertInstanceOf(ProfileStatus::class, $profile->status);
        $this->assertSame(ProfileStatus::Active, $profile->status);
        $this->assertInstanceOf(ProfileType::class, $profile->type);
        $this->assertSame(ProfileType::Community, $profile->type);
        $this->assertInstanceOf(VerificationStatus::class, $profile->verification_status);
        $this->assertSame(VerificationStatus::Verified, $profile->verification_status);
        $this->assertTrue($profile->is_community_created);
    }

    #[Test]
    public function profile_badge_returns_verified_when_verified(): void
    {
        $profile = Profile::create([
            'display_name' => 'Verified',
            'slug' => 'verified-badge',
            'category' => 'test',
            'status' => ProfileStatus::Active,
            'verification_status' => VerificationStatus::Verified,
        ]);

        $this->assertSame('verified', $profile->badge);
    }

    #[Test]
    public function profile_badge_returns_community_when_community_created(): void
    {
        $profile = Profile::create([
            'display_name' => 'Community',
            'slug' => 'community-badge',
            'category' => 'test',
            'status' => ProfileStatus::Active,
            'is_community_created' => true,
        ]);

        $this->assertSame('community', $profile->badge);
    }

    #[Test]
    public function profile_badge_returns_none_when_no_special_status(): void
    {
        $profile = Profile::create([
            'display_name' => 'Normal',
            'slug' => 'normal-badge',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $this->assertSame('none', $profile->badge);
    }

    #[Test]
    public function profile_has_many_transactions(): void
    {
        $profile = Profile::create([
            'display_name' => 'Rel Test',
            'slug' => 'rel-test-tx',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $profile->transactions());
    }

    #[Test]
    public function profile_has_many_snapshots(): void
    {
        $profile = Profile::create([
            'display_name' => 'Rel Test Snap',
            'slug' => 'rel-test-snap',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $profile->snapshots());
    }

    #[Test]
    public function profile_has_many_reports(): void
    {
        $profile = Profile::create([
            'display_name' => 'Rel Test Reports',
            'slug' => 'rel-test-reports',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $profile->reports());
    }

    #[Test]
    public function profile_has_many_claims(): void
    {
        $profile = Profile::create([
            'display_name' => 'Rel Test Claims',
            'slug' => 'rel-test-claims',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $profile->claims());
    }

    #[Test]
    public function profile_has_many_share_events(): void
    {
        $profile = Profile::create([
            'display_name' => 'Rel Test Shares',
            'slug' => 'rel-test-shares',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $profile->shareEvents());
    }

    // ─── RankingPeriod ───────────────────────────────────────────────────────

    #[Test]
    public function ranking_period_generates_public_id_on_creating(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01 00:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08 00:00:00', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => ['minimum_support_clp' => 1000],
        ]);

        $this->assertNotNull($period->public_id);
        $this->assertEquals(26, strlen($period->public_id));
    }

    #[Test]
    public function ranking_period_includes_uses_half_open_interval(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-test',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01 00:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08 00:00:00', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $this->assertTrue($period->includes(CarbonImmutable::parse('2026-09-01 00:00:00', 'UTC')));
        $this->assertTrue($period->includes(CarbonImmutable::parse('2026-09-07 23:59:59', 'UTC')));
        $this->assertFalse($period->includes(CarbonImmutable::parse('2026-09-08 00:00:00', 'UTC')));
        $this->assertFalse($period->includes(CarbonImmutable::parse('2026-08-31 23:59:59', 'UTC')));
    }

    #[Test]
    public function ranking_period_includes_null_when_null(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-null-test',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $tx = new SupportTransaction(['ranking_qualified_at' => null]);
        $this->assertFalse($period->includes($tx));
    }

    #[Test]
    public function ranking_period_casts_enums_correctly(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-cast',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Draft,
            'configuration' => ['min' => 1000],
            'settlement_delay_minutes' => 10,
        ]);

        $this->assertInstanceOf(RankingPeriodType::class, $period->period_type);
        $this->assertSame(RankingPeriodType::Weekly, $period->period_type);
        $this->assertInstanceOf(RankingPeriodStatus::class, $period->status);
        $this->assertSame(RankingPeriodStatus::Draft, $period->status);
        $this->assertIsArray($period->configuration);
        $this->assertSame(10, $period->settlement_delay_minutes);
    }

    // ─── SupportTransaction ──────────────────────────────────────────────────

    #[Test]
    public function support_transaction_casts_enums_correctly(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-stx',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'ST Test',
            'slug' => 'st-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $tx = SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 5000,
            'currency' => Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Pending,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'external_reference' => 'ext-ref-123',
        ]);

        $this->assertInstanceOf(Currency::class, $tx->currency);
        $this->assertSame(Currency::CLP, $tx->currency);
        $this->assertInstanceOf(SupportTransactionType::class, $tx->type);
        $this->assertSame(SupportTransactionType::Real, $tx->type);
        $this->assertInstanceOf(SupportTransactionStatus::class, $tx->status);
        $this->assertSame(SupportTransactionStatus::Pending, $tx->status);
        $this->assertInstanceOf(PaymentGateway::class, $tx->payment_gateway);
        $this->assertSame(PaymentGateway::MercadoPago, $tx->payment_gateway);
        $this->assertSame(5000, $tx->amount_clp);
    }

    #[Test]
    public function support_transaction_resolve_external_reference_generates_ulid(): void
    {
        $tx = new SupportTransaction();
        $this->assertNull($tx->external_reference);

        $result = $tx->resolveExternalReference();

        $this->assertNotNull($result);
        $this->assertEquals(26, strlen($result));
        $this->assertSame($result, $tx->external_reference);
    }

    #[Test]
    public function support_transaction_resolve_external_reference_preserves_existing(): void
    {
        $tx = new SupportTransaction(['external_reference' => 'existing-ref']);

        $result = $tx->resolveExternalReference();

        $this->assertSame('existing-ref', $result);
    }

    #[Test]
    public function support_transaction_belongs_to_ranking_period(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-belongs',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Belongs Test',
            'slug' => 'belongs-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $tx = SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 1000,
            'currency' => Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Pending,
            'payment_gateway' => PaymentGateway::MercadoPago,
        ]);

        $this->assertNotNull($tx->rankingPeriod);
        $this->assertSame($period->id, $tx->rankingPeriod->id);
        $this->assertNotNull($tx->profile);
        $this->assertSame($profile->id, $tx->profile->id);
    }

    // ─── PaymentGatewayEvent ─────────────────────────────────────────────────

    #[Test]
    public function payment_gateway_event_casts_enums(): void
    {
        $event = PaymentGatewayEvent::create([
            'payment_gateway' => PaymentGateway::MercadoPago,
            'gateway_event_id' => 'evt-123',
            'payload_hash' => hash('sha256', 'test'),
            'received_at' => now(),
            'processing_result' => GatewayProcessingResult::Processed,
        ]);

        $this->assertInstanceOf(PaymentGateway::class, $event->payment_gateway);
        $this->assertSame(PaymentGateway::MercadoPago, $event->payment_gateway);
        $this->assertInstanceOf(GatewayProcessingResult::class, $event->processing_result);
        $this->assertSame(GatewayProcessingResult::Processed, $event->processing_result);
    }

    // ─── FeatureFlag ─────────────────────────────────────────────────────────

    #[Test]
    public function feature_flag_enabled_returns_boolean(): void
    {
        Cache::flush();
        cache()->forget('feature_flag:test_flag');
        cache()->forget('feature_flag:test_flag_off');
        cache()->forget('feature_flag:nonexistent');
        FeatureFlag::create(['key' => 'test_flag', 'value' => true]);
        FeatureFlag::create(['key' => 'test_flag_off', 'value' => false]);

        $this->assertTrue(FeatureFlag::enabled('test_flag'));
        $this->assertFalse(FeatureFlag::enabled('test_flag_off'));
    }

    #[Test]
    public function feature_flag_value_returns_mixed(): void
    {
        Cache::flush();
        cache()->forget('feature_flag:test_flag');
        cache()->forget('feature_flag:test_flag_off');
        cache()->forget('feature_flag:nonexistent');
        FeatureFlag::create(['key' => 'test_str', 'value' => 'hello']);
        FeatureFlag::create(['key' => 'test_int', 'value' => 42]);
        FeatureFlag::create(['key' => 'test_arr', 'value' => [1, 2, 3]]);

        $this->assertSame('hello', FeatureFlag::value('test_str'));
        $this->assertSame(42, FeatureFlag::value('test_int'));
        $this->assertSame([1, 2, 3], FeatureFlag::value('test_arr'));
    }

    // ─── RankingSnapshot ─────────────────────────────────────────────────────

    #[Test]
    public function ranking_snapshot_immutable_cannot_be_updated(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('immutable');

        $period = RankingPeriod::create([
            'code' => 'W2026-36-snap',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Snapshotted,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Snap Test',
            'slug' => 'snap-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $snap = RankingSnapshot::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'final_position' => 1,
            'total_real_clp' => 10000,
            'revision' => 0,
            'immutable' => true,
        ]);

        $snap->update(['total_real_clp' => 20000]);
    }

    #[Test]
    public function ranking_snapshot_immutable_cannot_be_deleted(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('immutable');

        $period = RankingPeriod::create([
            'code' => 'W2026-36-snapdel',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Snapshotted,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Snap Del Test',
            'slug' => 'snap-del-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $snap = RankingSnapshot::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'final_position' => 1,
            'total_real_clp' => 10000,
            'revision' => 0,
            'immutable' => true,
        ]);

        $snap->delete();
    }

    #[Test]
    public function ranking_snapshot_official_for_returns_highest_revision(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-snapoff',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Snapshotted,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Snap Off Test',
            'slug' => 'snap-off-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        RankingSnapshot::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'final_position' => 2,
            'total_real_clp' => 5000,
            'revision' => 0,
            'immutable' => true,
        ]);

        RankingSnapshot::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'final_position' => 1,
            'total_real_clp' => 8000,
            'revision' => 1,
            'immutable' => true,
        ]);

        $official = RankingSnapshot::officialFor($period->id, $profile->id);

        $this->assertNotNull($official);
        $this->assertSame(1, $official->revision);
        $this->assertSame(1, $official->final_position);
    }

    // ─── AuditLog ────────────────────────────────────────────────────────────

    #[Test]
    public function audit_log_constants_are_strings(): void
    {
        $this->assertSame('system', AuditLog::ACTOR_SYSTEM);
        $this->assertSame('admin', AuditLog::ACTOR_ADMIN);
        $this->assertSame('gateway', AuditLog::ACTOR_GATEWAY);
        $this->assertSame('user', AuditLog::ACTOR_USER);
        $this->assertSame('scheduled_task', AuditLog::ACTOR_SCHEDULED_TASK);

        $this->assertIsString(AuditLog::EVT_PAYMENT_CREATED);
        $this->assertIsString(AuditLog::EVT_PAYMENT_APPROVED);
        $this->assertIsString(AuditLog::EVT_PERIOD_CREATED);
        $this->assertIsString(AuditLog::EVT_PERIOD_ACTIVATED);
    }

    // ─── User ────────────────────────────────────────────────────────────────

    #[Test]
    public function user_is_admin_returns_true_for_admin_role(): void
    {
        $user = new User();
        $user->role = \App\Enums\UserRole::Admin;
        $this->assertTrue($user->isAdmin());
    }

    #[Test]
    public function user_is_admin_returns_false_for_non_admin(): void
    {
        $user = new User();
        $user->role = \App\Enums\UserRole::Moderator;
        $this->assertFalse($user->isAdmin());
    }

    // ─── ShareEvent ──────────────────────────────────────────────────────────

    #[Test]
    public function share_event_belongs_to_profile_and_transaction(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-share',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Share Test',
            'slug' => 'share-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $tx = SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 2000,
            'currency' => Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
        ]);

        $share = ShareEvent::create([
            'profile_id' => $profile->id,
            'support_transaction_id' => $tx->id,
            'source' => 'test',
        ]);

        $this->assertNotNull($share->profile);
        $this->assertSame($profile->id, $share->profile->id);
        $this->assertNotNull($share->supportTransaction);
        $this->assertSame($tx->id, $share->supportTransaction->id);
    }

    // ─── RankingSetting ──────────────────────────────────────────────────────

    #[Test]
    public function ranking_setting_casts_correctly(): void
    {
        $setting = RankingSetting::create([
            'scope' => 'test-scope',
            'period_type' => 'weekly',
            'settlement_delay_minutes' => 5,
            'minimum_support_clp' => 1000,
            'maximum_support_clp' => 500000,
            'show_real_amounts' => true,
            'show_supporter_count' => true,
            'max_public_positions' => 10,
            'sharing_enabled' => true,
            'community_profiles_enabled' => false,
            'promotional_credits_enabled' => false,
        ]);

        $this->assertSame(5, $setting->settlement_delay_minutes);
        $this->assertSame(1000, $setting->minimum_support_clp);
        $this->assertSame(500000, $setting->maximum_support_clp);
        $this->assertTrue($setting->show_real_amounts);
        $this->assertTrue($setting->show_supporter_count);
        $this->assertSame(10, $setting->max_public_positions);
        $this->assertTrue($setting->sharing_enabled);
        $this->assertFalse($setting->community_profiles_enabled);
    }

    // ─── ProfileSubmission ───────────────────────────────────────────────────

    #[Test]
    public function profile_submission_casts_status(): void
    {
        $sub = ProfileSubmission::create([
            'display_name' => 'Test Submission',
            'category' => 'test',
            'status' => ProfileSubmissionStatus::Pending,
        ]);

        $this->assertInstanceOf(ProfileSubmissionStatus::class, $sub->status);
        $this->assertSame(ProfileSubmissionStatus::Pending, $sub->status);
    }

    // ─── ProfileReport ───────────────────────────────────────────────────────

    #[Test]
    public function profile_report_belongs_to_profile_and_casts_status(): void
    {
        $profile = Profile::create([
            'display_name' => 'Report Test',
            'slug' => 'report-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $report = ProfileReport::create([
            'profile_id' => $profile->id,
            'reason' => 'spam',
            'status' => ProfileReportStatus::Open,
        ]);

        $this->assertInstanceOf(ProfileReportStatus::class, $report->status);
        $this->assertSame(ProfileReportStatus::Open, $report->status);
        $this->assertNotNull($report->profile);
        $this->assertSame($profile->id, $report->profile->id);
    }

    // ─── ProfileClaim ────────────────────────────────────────────────────────

    #[Test]
    public function profile_claim_belongs_to_profile_and_casts_status(): void
    {
        $profile = Profile::create([
            'display_name' => 'Claim Test',
            'slug' => 'claim-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $claim = ProfileClaim::create([
            'profile_id' => $profile->id,
            'claimant_name' => 'Test Claimant',
            'claimant_email' => 'claim@test.com',
            'status' => ProfileClaimStatus::Pending,
        ]);

        $this->assertInstanceOf(ProfileClaimStatus::class, $claim->status);
        $this->assertSame(ProfileClaimStatus::Pending, $claim->status);
        $this->assertNotNull($claim->profile);
        $this->assertSame($profile->id, $claim->profile->id);
    }
}
