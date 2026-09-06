<?php

namespace Tests\Unit;

use App\Enums\GatewayProcessingResult;
use App\Enums\PaymentGateway;
use App\Enums\PaymentGatewayConfirmationStatus;
use App\Enums\ProfileClaimStatus;
use App\Enums\ProfileReportStatus;
use App\Enums\ProfileStatus;
use App\Enums\ProfileSubmissionStatus;
use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
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
use App\Models\SupportTransaction;
use App\Services\AuditService;
use App\Services\FeatureFlagsService;
use App\Services\ModerationService;
use App\Services\PaymentLimitsService;
use App\Services\RankingPeriodService;
use App\Services\RankingService;
use App\Services\RankingSettingsService;
use App\Services\ShareService;
use App\Services\SupportTransactionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;

class ServicesTest extends \Tests\TestCase
{
    use RefreshDatabase;

    // ─── ModerationService ───────────────────────────────────────────────────

    #[Test]
    public function moderation_sanitize_name_returns_clean_string(): void
    {
        $svc = app(ModerationService::class);

        $this->assertSame('Juan Perez', $svc->sanitizeName('Juan Perez'));
        $this->assertSame('Test Name', $svc->sanitizeName('  Test Name  '));
    }

    #[Test]
    public function moderation_sanitize_name_strips_tags(): void
    {
        $svc = app(ModerationService::class);

        // strip_tags removes tags but keeps inner text
        $result = $svc->sanitizeName('<script>alert("XSS")</script>scriptalertXSS');
        $this->assertStringContainsString('alert', $result);
        $this->assertStringNotContainsString('<script>', $result);

        $result2 = $svc->sanitizeName('<b>bold</b>');
        $this->assertSame('bold', $result2);
    }

    #[Test]
    public function moderation_sanitize_name_truncates_at_64_chars(): void
    {
        $svc = app(ModerationService::class);
        $long = str_repeat('a', 100);

        $result = $svc->sanitizeName($long);

        $this->assertEquals(64, strlen($result));
    }

    #[Test]
    public function moderation_sanitize_name_returns_null_for_empty(): void
    {
        $svc = app(ModerationService::class);

        $this->assertNull($svc->sanitizeName(null));
        $this->assertNull($svc->sanitizeName(''));
        $this->assertNull($svc->sanitizeName('   '));
        $this->assertNull($svc->sanitizeName('<>'));
    }

    #[Test]
    public function moderation_approve_submission_updates_status(): void
    {
        $svc = app(ModerationService::class);
        $sub = ProfileSubmission::create([
            'display_name' => 'Approve Test',
            'category' => 'test',
            'status' => ProfileSubmissionStatus::Pending,
        ]);

        $svc->approveSubmission($sub, 1);

        $sub->refresh();
        $this->assertSame(ProfileSubmissionStatus::Approved, $sub->status);
        $this->assertSame(1, $sub->reviewed_by);
        $this->assertNotNull($sub->reviewed_at);
    }

    #[Test]
    public function moderation_approve_submission_ignores_non_pending(): void
    {
        $svc = app(ModerationService::class);
        $sub = ProfileSubmission::create([
            'display_name' => 'Already Approved',
            'category' => 'test',
            'status' => ProfileSubmissionStatus::Approved,
            'reviewed_by' => 1,
            'reviewed_at' => now(),
        ]);

        $svc->approveSubmission($sub, 2);

        $sub->refresh();
        $this->assertSame(1, $sub->reviewed_by);
    }

    #[Test]
    public function moderation_reject_submission_updates_status(): void
    {
        $svc = app(ModerationService::class);
        $sub = ProfileSubmission::create([
            'display_name' => 'Reject Test',
            'category' => 'test',
            'status' => ProfileSubmissionStatus::Pending,
        ]);

        $svc->rejectSubmission($sub, 'spam', 1);

        $sub->refresh();
        $this->assertSame(ProfileSubmissionStatus::Rejected, $sub->status);
        $this->assertSame('spam', $sub->rejection_reason);
        $this->assertSame(1, $sub->reviewed_by);
    }

    #[Test]
    public function moderation_reject_submission_ignores_non_pending(): void
    {
        $svc = app(ModerationService::class);
        $sub = ProfileSubmission::create([
            'display_name' => 'Already Rejected',
            'category' => 'test',
            'status' => ProfileSubmissionStatus::Rejected,
            'rejection_reason' => 'old',
            'reviewed_by' => 1,
            'reviewed_at' => now(),
        ]);

        $svc->rejectSubmission($sub, 'new reason', 2);

        $sub->refresh();
        $this->assertSame('old', $sub->rejection_reason);
    }

    #[Test]
    public function moderation_resolve_report_updates_status(): void
    {
        $svc = app(ModerationService::class);
        $profile = Profile::create([
            'display_name' => 'Report Profile',
            'slug' => 'report-profile',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);
        $report = ProfileReport::create([
            'profile_id' => $profile->id,
            'reason' => 'spam',
            'status' => ProfileReportStatus::Open,
        ]);

        $svc->resolveReport($report, 'resolved_content_removed', 1);

        $report->refresh();
        $this->assertSame(ProfileReportStatus::Resolved, $report->status);
        $this->assertSame('resolved_content_removed', $report->resolution);
        $this->assertSame(1, $report->reviewed_by);
    }

    #[Test]
    public function moderation_resolve_report_ignores_non_open(): void
    {
        $svc = app(ModerationService::class);
        $profile = Profile::create([
            'display_name' => 'Report Profile 2',
            'slug' => 'report-profile-2',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);
        $report = ProfileReport::create([
            'profile_id' => $profile->id,
            'reason' => 'spam',
            'status' => ProfileReportStatus::Resolved,
            'resolution' => 'old',
            'reviewed_by' => 1,
            'reviewed_at' => now(),
        ]);

        $svc->resolveReport($report, 'new', 2);

        $report->refresh();
        $this->assertSame('old', $report->resolution);
    }

    #[Test]
    public function moderation_verify_claim_updates_claim_and_profile(): void
    {
        $svc = app(ModerationService::class);
        $profile = Profile::create([
            'display_name' => 'Claim Profile',
            'slug' => 'claim-profile',
            'category' => 'test',
            'status' => ProfileStatus::Active,
            'verification_status' => VerificationStatus::Unverified,
        ]);
        $claim = ProfileClaim::create([
            'profile_id' => $profile->id,
            'claimant_name' => 'Claimant',
            'claimant_email' => 'claim@test.com',
            'status' => ProfileClaimStatus::Pending,
        ]);

        $svc->verifyClaim($claim, 1);

        $claim->refresh();
        $profile->refresh();

        $this->assertSame(ProfileClaimStatus::Verified, $claim->status);
        $this->assertSame(1, $claim->reviewed_by);
        $this->assertSame(VerificationStatus::Verified, $profile->verification_status);
    }

    #[Test]
    public function moderation_verify_claim_ignores_non_pending(): void
    {
        $svc = app(ModerationService::class);
        $profile = Profile::create([
            'display_name' => 'Claim Profile 2',
            'slug' => 'claim-profile-2',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);
        $claim = ProfileClaim::create([
            'profile_id' => $profile->id,
            'claimant_name' => 'Claimant2',
            'claimant_email' => 'claim2@test.com',
            'status' => ProfileClaimStatus::Verified,
            'reviewed_by' => 1,
            'reviewed_at' => now(),
        ]);

        $svc->verifyClaim($claim, 2);

        $claim->refresh();
        $this->assertSame(1, $claim->reviewed_by);
    }

    // ─── AuditService ────────────────────────────────────────────────────────

    #[Test]
    public function audit_service_creates_log_entry(): void
    {
        $svc = app(AuditService::class);

        $log = $svc->log('test_event', 'test_entity', 42, ['key' => 'value'], 'system', null);

        $this->assertNotNull($log->id);
        $this->assertSame('test_event', $log->event_type);
        $this->assertSame('test_entity', $log->entity_type);
        $this->assertSame(42, $log->entity_id);
        $this->assertSame(['key' => 'value'], $log->metadata);
        $this->assertSame('system', $log->actor_type);
        $this->assertNull($log->actor_id);
    }

    #[Test]
    public function audit_service_creates_with_actor(): void
    {
        $svc = app(AuditService::class);

        $log = $svc->log('admin_action', 'profile', 1, [], 'admin', 5);

        $this->assertSame('admin', $log->actor_type);
        $this->assertSame(5, $log->actor_id);
    }

    // ─── FeatureFlagsService ─────────────────────────────────────────────────

    #[Test]
    public function feature_flags_service_is_enabled(): void
    {
        $svc = app(FeatureFlagsService::class);
        Cache::flush();
        cache()->forget('feature_flag:payments_enabled');
        cache()->forget('feature_flag:payments_disabled');
        cache()->forget('feature_flag:nonexistent');

        FeatureFlag::create(['key' => 'payments_enabled', 'value' => true]);
        FeatureFlag::create(['key' => 'payments_disabled', 'value' => false]);

        $this->assertTrue($svc->isEnabled('payments_enabled'));
        $this->assertFalse($svc->isEnabled('payments_disabled'));
        $this->assertFalse($svc->isEnabled('nonexistent'));
        Cache::forget('feature_flag:nonexistent');
        $this->assertTrue($svc->isEnabled('nonexistent', true));
    }

    #[Test]
    public function feature_flags_service_value(): void
    {
        $svc = app(FeatureFlagsService::class);
        Cache::flush();
        cache()->forget('feature_flag:payments_enabled');
        cache()->forget('feature_flag:payments_disabled');
        cache()->forget('feature_flag:nonexistent');

        FeatureFlag::create(['key' => 'custom_value', 'value' => 'hello']);

        $this->assertSame('hello', $svc->value('custom_value'));
        $this->assertSame('default', $svc->value('nonexistent', 'default'));
    }

    #[Test]
    public function feature_flags_service_set_creates_or_updates(): void
    {
        $svc = app(FeatureFlagsService::class);
        Cache::flush();
        cache()->forget('feature_flag:payments_enabled');
        cache()->forget('feature_flag:payments_disabled');
        cache()->forget('feature_flag:nonexistent');

        $svc->set('new_flag', true, 1);
        $this->assertTrue($svc->isEnabled('new_flag'));

        $svc->set('new_flag', false, 1);
        $this->assertFalse($svc->isEnabled('new_flag'));
    }

    // ─── RankingSettingsService ──────────────────────────────────────────────

    #[Test]
    public function ranking_settings_service_defaults_creates_if_missing(): void
    {
        $svc = app(RankingSettingsService::class);

        $this->assertNull(RankingSetting::where('scope', 'default')->first());

        $defaults = $svc->defaults();

        $this->assertNotNull($defaults);
        $this->assertSame('default', $defaults->scope);
        $this->assertSame(1000, $defaults->minimum_support_clp);
        $this->assertSame(500000, $defaults->maximum_support_clp);
    }

    #[Test]
    public function ranking_settings_service_defaults_returns_existing(): void
    {
        $svc = app(RankingSettingsService::class);

        $existing = RankingSetting::create([
            'scope' => 'default',
            'minimum_support_clp' => 2000,
        ]);

        $result = $svc->defaults();

        $this->assertSame($existing->id, $result->id);
        $this->assertSame(2000, $result->minimum_support_clp);
    }

    #[Test]
    public function ranking_settings_service_update_defaults(): void
    {
        $svc = app(RankingSettingsService::class);
        $svc->defaults();

        $updated = $svc->updateDefaults([
            'minimum_support_clp' => 3000,
            'maximum_support_clp' => 200000,
        ]);

        $this->assertSame(3000, $updated->minimum_support_clp);
        $this->assertSame(200000, $updated->maximum_support_clp);
    }

    #[Test]
    public function ranking_settings_service_frozen_configuration(): void
    {
        $svc = app(RankingSettingsService::class);
        $defaults = $svc->defaults();

        $config = $svc->frozenConfiguration($defaults);

        $this->assertArrayHasKey('period_type', $config);
        $this->assertArrayHasKey('minimum_support_clp', $config);
        $this->assertArrayHasKey('maximum_support_clp', $config);
        $this->assertArrayHasKey('show_real_amounts', $config);
        $this->assertArrayHasKey('show_supporter_count', $config);
        $this->assertArrayHasKey('max_public_positions', $config);
        $this->assertArrayHasKey('category_scope', $config);
        $this->assertArrayHasKey('sharing_enabled', $config);
        $this->assertArrayHasKey('community_profiles_enabled', $config);
        $this->assertArrayHasKey('promotional_credits_enabled', $config);
    }

    #[Test]
    public function ranking_settings_service_assert_category_invariant_all_must_have_null_category(): void
    {
        $svc = app(RankingSettingsService::class);

        $this->expectException(\InvalidArgumentException::class);
        $svc->assertCategoryInvariant('all', 'music');
    }

    #[Test]
    public function ranking_settings_service_assert_category_invariant_single_must_have_category(): void
    {
        $svc = app(RankingSettingsService::class);

        $this->expectException(\InvalidArgumentException::class);
        $svc->assertCategoryInvariant('single', null);
    }

    #[Test]
    public function ranking_settings_service_assert_category_invariant_single_must_have_non_empty_category(): void
    {
        $svc = app(RankingSettingsService::class);

        $this->expectException(\InvalidArgumentException::class);
        $svc->assertCategoryInvariant('single', '  ');
    }

    #[Test]
    public function ranking_settings_service_assert_category_invariant_valid_combinations(): void
    {
        $svc = app(RankingSettingsService::class);

        $svc->assertCategoryInvariant('all', null);
        $svc->assertCategoryInvariant('single', 'music');
        $svc->assertCategoryInvariant(null, null);

        $this->assertTrue(true);
    }

    // ─── ShareService ────────────────────────────────────────────────────────

    #[Test]
    public function share_service_generates_for_approved_real_transaction(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-share-svc',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Share SVC Test',
            'slug' => 'share-svc-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $tx = SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 5000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
        ]);

        $svc = app(ShareService::class);
        $share = $svc->generateFor($tx);

        $this->assertNotNull($share);
        $this->assertSame($profile->id, $share->profile_id);
        $this->assertSame($tx->id, $share->support_transaction_id);
        $this->assertSame('share_trophy', $share->source);
        $this->assertSame($period->code, $share->period_code);
    }

    #[Test]
    public function share_service_returns_null_for_pending_transaction(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-share-pending',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Share Pending',
            'slug' => 'share-pending',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $tx = SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 1000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Pending,
            'payment_gateway' => PaymentGateway::MercadoPago,
        ]);

        $svc = app(ShareService::class);
        $share = $svc->generateFor($tx);

        $this->assertNull($share);
    }

    #[Test]
    public function share_service_returns_null_for_promotional_transaction(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-share-promo',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Share Promo',
            'slug' => 'share-promo',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $tx = SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 1000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Promotional,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
        ]);

        $svc = app(ShareService::class);
        $share = $svc->generateFor($tx);

        $this->assertNull($share);
    }

    #[Test]
    public function share_service_is_idempotent(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-share-idem',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Share Idem',
            'slug' => 'share-idem',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $tx = SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 3000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
        ]);

        $svc = app(ShareService::class);
        $share1 = $svc->generateFor($tx);
        $share2 = $svc->generateFor($tx);

        $this->assertNotNull($share1);
        $this->assertNotNull($share2);
        $this->assertSame($share1->id, $share2->id);
    }

    // ─── PaymentLimitsService ────────────────────────────────────────────────

    #[Test]
    public function payment_limits_service_constants(): void
    {
        $this->assertSame(1000, PaymentLimitsService::MIN_CLP);
        $this->assertSame(500000, PaymentLimitsService::MAX_CLP);
        $this->assertSame(1000000, PaymentLimitsService::DAILY_THRESHOLD_CLP);
    }

    #[Test]
    public function payment_limits_service_resolve_payer_identity(): void
    {
        $svc = app(PaymentLimitsService::class);
        $request = \Illuminate\Http\Request::create('/test', 'POST', [
            'checkout_token' => 'test-token-123',
            'payer_email' => 'Test@Example.COM',
        ]);

        $identity = $svc->resolvePayerIdentity($request);

        $this->assertObjectHasProperty('gatewayPayerId', $identity);
        $this->assertObjectHasProperty('payerReferenceHash', $identity);
        $this->assertObjectHasProperty('emailHash', $identity);
        $this->assertObjectHasProperty('signedCookie', $identity);
        $this->assertNotNull($identity->payerReferenceHash);
        $this->assertNotNull($identity->emailHash);
    }

    #[Test]
    public function payment_limits_service_resolve_payer_identity_no_email(): void
    {
        $svc = app(PaymentLimitsService::class);
        $request = \Illuminate\Http\Request::create('/test', 'POST', [
            'checkout_token' => 'test-token-456',
        ]);

        $identity = $svc->resolvePayerIdentity($request);

        $this->assertNull($identity->emailHash);
    }

    // ─── SupportTransactionService ───────────────────────────────────────────

    #[Test]
    public function support_transaction_service_assert_amount_within_limits_passes(): void
    {
        $svc = app(SupportTransactionService::class);

        $svc->assertAmountWithinLimits(5000);
        $svc->assertAmountWithinLimits(1000);
        $svc->assertAmountWithinLimits(500000);

        $this->assertTrue(true);
    }

    #[Test]
    public function support_transaction_service_assert_amount_within_limits_throws_below_min(): void
    {
        $this->expectException(ValidationException::class);

        $svc = app(SupportTransactionService::class);
        $svc->assertAmountWithinLimits(500);
    }

    #[Test]
    public function support_transaction_service_assert_amount_within_limits_throws_above_max(): void
    {
        $this->expectException(ValidationException::class);

        $svc = app(SupportTransactionService::class);
        $svc->assertAmountWithinLimits(600000);
    }

    #[Test]
    public function support_transaction_service_assert_amount_uses_period_config(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-limits',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => ['minimum_support_clp' => 5000, 'maximum_support_clp' => 10000],
        ]);

        $svc = app(SupportTransactionService::class);

        $this->expectException(ValidationException::class);
        $svc->assertAmountWithinLimits(4000, $period);
    }

    // ─── RankingPeriodService ────────────────────────────────────────────────

    #[Test]
    public function ranking_period_service_active_period_returns_null_when_none(): void
    {
        $svc = app(RankingPeriodService::class);
        $this->assertNull($svc->activePeriod());
    }

    #[Test]
    public function ranking_period_service_active_period_returns_active(): void
    {
        RankingPeriod::create([
            'code' => 'W2026-36-active',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $active = $svc->activePeriod();

        $this->assertNotNull($active);
        $this->assertSame(RankingPeriodStatus::Active, $active->status);
    }

    #[Test]
    public function ranking_period_service_period_for_creates_new_when_none_exists(): void
    {
        $svc = app(RankingPeriodService::class);
        $at = CarbonImmutable::parse('2026-09-03 12:00:00', 'UTC');

        $period = $svc->periodFor($at);

        $this->assertNotNull($period);
        $this->assertSame(RankingPeriodStatus::Scheduled, $period->status);
        $this->assertStringStartsWith('W', $period->code);
    }

    #[Test]
    public function ranking_period_service_period_for_returns_existing(): void
    {
        $existing = RankingPeriod::create([
            'code' => 'W2026-36-existing',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $at = CarbonImmutable::parse('2026-09-03 12:00:00', 'UTC');

        $period = $svc->periodFor($at);

        $this->assertSame($existing->id, $period->id);
    }

    #[Test]
    public function ranking_period_service_transition_throws_on_invalid(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Transición inválida');

        $period = RankingPeriod::create([
            'code' => 'W2026-36-invalid',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $svc->transition($period, RankingPeriodStatus::Draft);
    }

    #[Test]
    public function ranking_period_service_transition_valid_draft_to_scheduled(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-draft-sched',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Draft,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $result = $svc->transition($period, RankingPeriodStatus::Scheduled);

        $this->assertSame(RankingPeriodStatus::Scheduled, $result->status);
    }

    #[Test]
    public function ranking_period_service_transition_valid_scheduled_to_active(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-sched-act',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Scheduled,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $result = $svc->transition($period, RankingPeriodStatus::Active);

        $this->assertSame(RankingPeriodStatus::Active, $result->status);
    }

    #[Test]
    public function ranking_period_service_transition_valid_active_to_closed_pending(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-act-cp',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $result = $svc->transition($period, RankingPeriodStatus::ClosedPendingSettlement);

        $this->assertSame(RankingPeriodStatus::ClosedPendingSettlement, $result->status);
        $this->assertNotNull($result->closed_at);
    }

    #[Test]
    public function ranking_period_service_transition_valid_cp_to_closed(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-cp-closed',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::ClosedPendingSettlement,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $result = $svc->transition($period, RankingPeriodStatus::Closed);

        $this->assertSame(RankingPeriodStatus::Closed, $result->status);
        $this->assertNotNull($result->settled_at);
    }

    #[Test]
    public function ranking_period_service_transition_valid_closed_to_snapshotted(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-closed-snap',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Closed,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $result = $svc->transition($period, RankingPeriodStatus::Snapshotted);

        $this->assertSame(RankingPeriodStatus::Snapshotted, $result->status);
        $this->assertNotNull($result->snapshotted_at);
    }

    #[Test]
    public function ranking_period_service_transition_valid_draft_to_cancelled(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-draft-cancel',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Draft,
            'configuration' => [],
        ]);

        $svc = app(RankingPeriodService::class);
        $result = $svc->transition($period, RankingPeriodStatus::Cancelled);

        $this->assertSame(RankingPeriodStatus::Cancelled, $result->status);
        $this->assertNotNull($result->cancelled_at);
    }

    // ─── RankingService ──────────────────────────────────────────────────────

    #[Test]
    public function ranking_service_active_period_ranking_empty_when_no_period(): void
    {
        $svc = app(RankingService::class);
        $result = $svc->activePeriodRanking();

        $this->assertSame([], $result);
    }

    #[Test]
    public function ranking_service_ranking_for_period_returns_ranked_profiles(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-rank',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $p1 = Profile::create([
            'display_name' => 'Rank 1',
            'slug' => 'rank-1',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $p2 = Profile::create([
            'display_name' => 'Rank 2',
            'slug' => 'rank-2',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $p1->id,
            'amount_clp' => 5000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'payer1',
            'ranking_qualified_at' => now(),
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $p2->id,
            'amount_clp' => 3000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'payer2',
            'ranking_qualified_at' => now(),
        ]);

        $svc = app(RankingService::class);
        $ranking = $svc->rankingForPeriod($period->id);

        $this->assertCount(2, $ranking);
        $this->assertSame(1, $ranking[0]['position']);
        $this->assertSame($p1->id, $ranking[0]['profile_id']);
        $this->assertSame(5000, $ranking[0]['total_real_clp']);
        $this->assertSame(2, $ranking[1]['position']);
        $this->assertSame($p2->id, $ranking[1]['profile_id']);
        $this->assertSame(3000, $ranking[1]['total_real_clp']);
    }

    #[Test]
    public function ranking_service_ranking_excludes_suspended_profiles(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-suspend',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $active = Profile::create([
            'display_name' => 'Active P',
            'slug' => 'active-p',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        $suspended = Profile::create([
            'display_name' => 'Suspended P',
            'slug' => 'suspended-p',
            'category' => 'test',
            'status' => ProfileStatus::Suspended,
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $active->id,
            'amount_clp' => 1000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'p-a',
            'ranking_qualified_at' => now(),
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $suspended->id,
            'amount_clp' => 99999,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'p-s',
            'ranking_qualified_at' => now(),
        ]);

        $svc = app(RankingService::class);
        $ranking = $svc->rankingForPeriod($period->id);

        $this->assertCount(1, $ranking);
        $this->assertSame($active->id, $ranking[0]['profile_id']);
    }

    #[Test]
    public function ranking_service_ranking_excludes_pending_and_failed_transactions(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-excl',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Excl Test',
            'slug' => 'excl-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 1000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'p1',
            'ranking_qualified_at' => now(),
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 5000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Pending,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'p2',
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 3000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Failed,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'p3',
        ]);

        $svc = app(RankingService::class);
        $ranking = $svc->rankingForPeriod($period->id);

        $this->assertCount(1, $ranking);
        $this->assertSame(1000, $ranking[0]['total_real_clp']);
    }

    #[Test]
    public function ranking_service_period_compact(): void
    {
        $period = RankingPeriod::create([
            'code' => 'W2026-36-compact',
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [],
        ]);

        $profile = Profile::create([
            'display_name' => 'Compact Test',
            'slug' => 'compact-test',
            'category' => 'test',
            'status' => ProfileStatus::Active,
        ]);

        SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => 2000,
            'currency' => \App\Enums\Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'payer_reference_hash' => 'p1',
            'ranking_qualified_at' => now(),
        ]);

        $svc = app(RankingService::class);
        $compact = $svc->periodCompact();

        $this->assertIsArray($compact);
        $this->assertCount(1, $compact);
        $this->assertArrayHasKey('rank', $compact[0]);
        $this->assertArrayHasKey('slug', $compact[0]);
        $this->assertArrayHasKey('total_real_clp', $compact[0]);
    }

    // ─── LocalMercadoPagoStore ───────────────────────────────────────────────

    #[Test]
    public function local_store_create_and_find(): void
    {
        $store = app(\App\Services\Payments\LocalMercadoPagoStore::class);

        $store->createPending('tok-123', 5000, 'ext-123');
        $found = $store->find('tok-123');

        $this->assertNotNull($found);
        $this->assertSame('tok-123', $found['token']);
        $this->assertSame(5000, $found['amount']);
        $this->assertSame(PaymentGatewayConfirmationStatus::Pending->value, $found['status']);
    }

    #[Test]
    public function local_store_find_returns_null_for_unknown(): void
    {
        $store = app(\App\Services\Payments\LocalMercadoPagoStore::class);

        $this->assertNull($store->find('nonexistent'));
    }

    #[Test]
    public function local_store_mark_approved(): void
    {
        $store = app(\App\Services\Payments\LocalMercadoPagoStore::class);

        $store->createPending('tok-456', 3000, 'ext-456');
        $store->markApproved('tok-456');

        $found = $store->find('tok-456');
        $this->assertSame(PaymentGatewayConfirmationStatus::Approved->value, $found['status']);
        $this->assertNotNull($found['approved_at']);
    }

    #[Test]
    public function local_store_mark_failed(): void
    {
        $store = app(\App\Services\Payments\LocalMercadoPagoStore::class);

        $store->createPending('tok-789', 2000, 'ext-789');
        $store->markFailed('tok-789');

        $found = $store->find('tok-789');
        $this->assertSame(PaymentGatewayConfirmationStatus::Failed->value, $found['status']);
        $this->assertNull($found['approved_at']);
    }

    // ─── LocalMercadoPagoGateway ─────────────────────────────────────────────

    #[Test]
    public function local_gateway_create_returns_url_and_token(): void
    {
        $gw = app(\App\Services\Payments\LocalMercadoPagoGateway::class);

        $result = $gw->create(5000, 'ext-ref', 'session-1');

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertStringStartsWith('local_mp_', $result['token']);
    }

    #[Test]
    public function local_gateway_confirm_returns_pending_for_created(): void
    {
        $gw = app(\App\Services\Payments\LocalMercadoPagoGateway::class);

        $created = $gw->create(3000, 'ext', 'sess');
        $conf = $gw->confirm($created['token']);

        $this->assertSame(PaymentGatewayConfirmationStatus::Pending->value, $conf['status']);
        $this->assertSame(3000, $conf['amount']);
    }

    #[Test]
    public function local_gateway_confirm_returns_failed_for_unknown(): void
    {
        $gw = app(\App\Services\Payments\LocalMercadoPagoGateway::class);

        $conf = $gw->confirm('nonexistent-token');

        $this->assertSame(PaymentGatewayConfirmationStatus::Failed->value, $conf['status']);
        $this->assertSame(0, $conf['amount']);
    }

    #[Test]
    public function local_gateway_is_authorized(): void
    {
        $gw = app(\App\Services\Payments\LocalMercadoPagoGateway::class);

        $this->assertTrue($gw->isAuthorized(['status' => PaymentGatewayConfirmationStatus::Approved->value]));
        $this->assertFalse($gw->isAuthorized(['status' => PaymentGatewayConfirmationStatus::Pending->value]));
        $this->assertFalse($gw->isAuthorized(['status' => PaymentGatewayConfirmationStatus::Failed->value]));
        $this->assertFalse($gw->isAuthorized(['status' => 'unknown']));
    }

    #[Test]
    public function local_gateway_status_info_returns_same_as_confirm(): void
    {
        $gw = app(\App\Services\Payments\LocalMercadoPagoGateway::class);

        $created = $gw->create(2000, 'ext', 'sess');
        $status = $gw->statusInfo($created['token']);
        $confirm = $gw->confirm($created['token']);

        $this->assertSame($confirm, $status);
    }

    // ─── Helper functions ────────────────────────────────────────────────────

    #[Test]
    public function money_clp_formats_correctly(): void
    {
        $this->assertSame('$0', money_clp(0));
        $this->assertSame('$1.000', money_clp(1000));
        $this->assertSame('$43.900', money_clp(43900));
        $this->assertSame('$500.000', money_clp(500000));
        $this->assertSame('$0', money_clp(null));
    }

    #[Test]
    public function audit_helper_function(): void
    {
        $log = audit('test_event', 'test_entity', 1, ['key' => 'val']);

        $this->assertNotNull($log);
        $this->assertSame('test_event', $log->event_type);
        $this->assertSame('test_entity', $log->entity_type);
        $this->assertSame(1, $log->entity_id);
    }

    #[Test]
    public function analytics_helper_is_noop(): void
    {
        analytics('test', ['foo' => 'bar']);
        $this->assertTrue(true);
    }
}
