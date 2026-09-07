<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\PaymentGateway;
use App\Enums\ProfileClaimStatus;
use App\Enums\ProfileStatus;
use App\Enums\ProfileSubmissionStatus;
use App\Enums\ProfileType;
use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Enums\VerificationStatus;
use App\Enums\OutboundClickSource;
use App\Enums\ProfileLinkType;
use App\Models\AuditLog;
use App\Models\FeatureFlag;
use App\Models\OutboundClickEvent;
use App\Models\Profile;
use App\Models\ProfileClaim;
use App\Models\ProfileSubmission;
use App\Models\Region;
use App\Models\RankingPeriod;
use App\Models\SupportTransaction;
use App\Services\ModerationService;
use App\Services\OutboundClickService;
use App\Services\ProfileCreationService;
use App\Services\RankingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileOnboardingPhase2Test extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function onboarding_schema_uses_submissions_and_claims_without_a_rankings_table(): void
    {
        $this->assertTrue(Schema::hasTable('profiles'));
        $this->assertTrue(Schema::hasTable('profile_submissions'));
        $this->assertTrue(Schema::hasTable('profile_claims'));
        $this->assertTrue(Schema::hasTable('ranking_periods'));
        $this->assertTrue(Schema::hasTable('ranking_snapshots'));
        $this->assertFalse(Schema::hasTable('rankings'));
    }

    #[Test]
    public function approved_submission_is_free_and_does_not_create_financial_records(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $submission = ProfileSubmission::create([
            'display_name' => 'Nueva figura',
            'category' => 'politica',
            'profile_image_url' => 'https://cdn.example.test/profile.jpg',
            'featured_media_url' => 'https://cdn.example.test/video.mp4',
            'submitted_by_session_id' => 'session-'.Str::ulid(),
            'submitted_email_hash' => hash('sha256', 'persona@example.com'),
            'status' => ProfileSubmissionStatus::Pending,
        ]);

        app(ModerationService::class)->approveSubmission($submission, 99);

        $submission->refresh();

        $this->assertSame(ProfileSubmissionStatus::Approved, $submission->status);
        $this->assertSame(0, SupportTransaction::count());
        $this->assertSame(1, AuditLog::count());
        $this->assertSame('profile_created', AuditLog::query()->firstOrFail()->event_type);
    }

    #[Test]
    public function claim_moderation_is_independent_from_submission_moderation(): void
    {
        $profile = $this->createProfile('Claimed profile');
        $submission = ProfileSubmission::create([
            'display_name' => 'Submitted profile',
            'category' => 'cultura',
            'status' => ProfileSubmissionStatus::Pending,
        ]);
        $claim = ProfileClaim::create([
            'profile_id' => $profile->id,
            'claimant_name' => 'Maria Claimant',
            'claimant_email' => 'claimant@example.com',
            'status' => ProfileClaimStatus::Pending,
        ]);

        app(ModerationService::class)->verifyClaim($claim, 7);

        $this->assertSame(ProfileClaimStatus::Verified, $claim->fresh()->status);
        $this->assertSame(ProfileSubmissionStatus::Pending, $submission->fresh()->status);
        $this->assertSame(VerificationStatus::Verified, $profile->fresh()->verification_status);
    }

    #[Test]
    public function paid_approval_can_reorder_the_ranking(): void
    {
        $period = $this->createActivePeriod();
        $alpha = $this->createProfile('Alpha');
        $beta = $this->createProfile('Beta');

        $this->createApprovedSupport($period, $alpha, 1000, '2026-09-02 10:00:00');
        $this->createApprovedSupport($period, $beta, 3000, '2026-09-02 11:00:00');

        $ranking = app(RankingService::class)->rankingForPeriod($period->id, true);

        $this->assertStringStartsWith('beta-', $ranking[0]['slug']);
        $this->assertStringStartsWith('alpha-', $ranking[1]['slug']);

        $this->createApprovedSupport($period, $alpha, 2500, '2026-09-02 12:00:00');

        $ranking = app(RankingService::class)->rankingForPeriod($period->id, true);

        $this->assertStringStartsWith('alpha-', $ranking[0]['slug']);
        $this->assertSame(3500, $ranking[0]['total_real_clp']);
        $this->assertStringStartsWith('beta-', $ranking[1]['slug']);
        $this->assertSame(3000, $ranking[1]['total_real_clp']);
    }

    #[Test]
    public function entrar_route_prefills_source_from_query_string(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $response = $this->get(route('entrar.index', [
            'source' => 'https://visitandopuntaarenas.cl',
            'position' => 1,
            'amount' => 1000,
        ]));

        $response->assertOk();
        $response->assertViewHas('defaultDraft', function (array $draft): bool {
            return $draft['source'] === 'https://visitandopuntaarenas.cl';
        });
    }

    #[Test]
    public function entrar_route_loads_the_self_service_wizard(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $response = $this->get(route('entrar.index'));

        $response->assertOk();
        $response->assertSee('Onboarding self-service');
        $response->assertSee('PUBLICAR GRATIS');
        $response->assertSee('Pega una URL o handle');
    }

    #[Test]
    public function onboarding_detects_profile_by_url_or_handle(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $response = $this->postJson(route('entrar.detectar'), [
            'display_name' => 'Mi Cuenta Nueva',
            'source_url' => 'https://instagram.com/mi-cuenta',
            'use_profile_as_destination' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', ProfileSubmissionStatus::Pending->value)
            ->assertJsonStructure(['submission_id', 'status', 'redirect_url']);

        $submission = ProfileSubmission::findOrFail($response->json('submission_id'));

        $this->assertSame('instagram', $submission->source_type);
        $this->assertSame('https://instagram.com/mi-cuenta', $submission->normalized_url);
        $this->assertSame('Instagram · mi-cuenta', $submission->detected_title);
        $this->assertSame('Mi Cuenta Nueva', $submission->display_name);
    }

    #[Test]
    public function onboarding_requires_a_destination_url_unless_profile_is_used_as_destination(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $this->postJson(route('entrar.detectar'), [
            'display_name' => 'Sin destino',
            'source_url' => 'https://instagram.com/sin-destino',
            'use_profile_as_destination' => false,
        ])->assertStatus(422)->assertJsonValidationErrors(['destination_url']);

        $response = $this->postJson(route('entrar.detectar'), [
            'display_name' => 'Destino perfil',
            'source_url' => 'https://instagram.com/destino-perfil',
            'use_profile_as_destination' => true,
        ]);

        $response->assertOk();
        $submission = ProfileSubmission::findOrFail($response->json('submission_id'));
        $this->assertTrue($submission->use_profile_as_destination);
        $this->assertCount(1, $submission->links);
        $this->assertSame('https://instagram.com/destino-perfil', $submission->links->first()->original_url);

        $profile = app(ProfileCreationService::class)->createFromSubmission($submission);
        $this->assertTrue($profile->use_profile_as_destination);
        $this->assertSame(route('profile.show', $profile->slug), app(OutboundClickService::class)->resolveDestinationUrl($profile));
    }

    #[Test]
    public function onboarding_accepts_region_and_optional_category_selection(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $region = Region::create([
            'name' => 'Metropolitana de Santiago',
            'slug' => 'metropolitana-de-santiago',
            'sort_order' => 15,
        ]);

        $response = $this->postJson(route('entrar.detectar'), [
            'display_name' => 'Proyecto Regional',
            'source_url' => 'https://instagram.com/proyecto-regional',
            'destination_url' => 'https://proyecto-regional.cl',
            'use_profile_as_destination' => false,
            'region_id' => $region->id,
        ]);

        $response->assertOk();

        $submission = ProfileSubmission::findOrFail($response->json('submission_id'));
        $this->assertSame($region->id, $submission->region_id);
        $this->assertSame('General', $submission->category);

        $profile = app(ProfileCreationService::class)->createFromSubmission($submission);
        $this->assertSame($region->id, $profile->region_id);
    }

    #[Test]
    public function preview_is_editable_and_destination_is_saved_as_destination_link(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $response = $this->postJson(route('entrar.detectar'), [
            'display_name' => 'Nombre Editable',
            'source_url' => 'https://instagram.com/origen-real',
            'destination_url' => 'https://tusitio.cl/landing',
        ]);

        $response->assertOk();
        $submission = ProfileSubmission::findOrFail($response->json('submission_id'));

        $this->assertSame('Nombre Editable', $submission->display_name);
        $this->assertTrue($submission->links()->where('link_type', 'destination')->exists());

        $destination = $submission->links()->where('link_type', 'destination')->firstOrFail();
        $this->assertSame('https://tusitio.cl/landing', $destination->original_url);
    }

    #[Test]
    public function duplicate_detection_rejects_same_source_url(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        $profile = $this->createProfile('Persona Existente');
        $profile->links()->create([
            'link_type' => 'social',
            'original_url' => 'https://instagram.com/persona-existente',
            'normalized_url' => 'https://instagram.com/persona-existente',
            'host' => 'instagram.com',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->postJson(route('entrar.detectar'), [
            'display_name' => 'Persona Existente',
            'source_url' => 'https://instagram.com/persona-existente',
            'use_profile_as_destination' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', ProfileSubmissionStatus::Rejected->value);

        $submission = ProfileSubmission::findOrFail($response->json('submission_id'));
        $this->assertSame($profile->id, $submission->duplicate_profile_id);
        $this->assertSame('duplicate_profile', $submission->rejection_reason);
    }

    #[Test]
    public function onboarding_rejects_ssrf_and_blocks_localhost_private_hosts(): void
    {
        FeatureFlag::create([
            'key' => FeatureFlag::KEY_COMMUNITY_SUBMISSION,
            'value' => true,
        ]);

        foreach (['https://localhost', 'https://127.0.0.1/admin', 'https://192.168.1.1', 'http://169.254.169.254/latest/meta-data', 'https://10.0.0.5'] as $blockedUrl) {
            $response = $this->postJson(route('entrar.detectar'), [
                'display_name' => 'Intentos SSRF',
                'source_url' => $blockedUrl,
                'use_profile_as_destination' => true,
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('error', 'invalid_source_url');
        }

        $this->assertSame(0, ProfileSubmission::count());
    }

    #[Test]
    public function outbound_click_event_is_recorded_and_redirects_to_destination(): void
    {
        $profile = $this->createProfile('Perfil Con Destino');
        $profile->links()->create([
            'link_type' => ProfileLinkType::Destination->value,
            'label' => 'Destino',
            'original_url' => 'https://destino-ejemplo.cl/landing',
            'normalized_url' => 'https://destino-ejemplo.cl/landing',
            'host' => 'destino-ejemplo.cl',
            'is_primary' => false,
            'sort_order' => 999,
        ]);

        $response = $this->get(route('outbound.click', $profile->slug));

        $response->assertRedirect('https://destino-ejemplo.cl/landing');

        $event = OutboundClickEvent::firstOrFail();
        $this->assertSame($profile->id, $event->profile_id);
        $this->assertSame('profile', $event->source->value);
        $this->assertSame('https://destino-ejemplo.cl/landing', $event->destination_url);
    }

    #[Test]
    public function outbound_click_from_ranking_source_is_recorded_with_utm(): void
    {
        $period = $this->createActivePeriod();
        $profile = $this->createProfile('Perfil Ranking');
        $profile->links()->create([
            'link_type' => ProfileLinkType::Destination->value,
            'label' => 'Destino',
            'original_url' => 'https://campana.cl',
            'normalized_url' => 'https://campana.cl',
            'host' => 'campana.cl',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get(route('outbound.click', $profile->slug).'?src=ranking&utm_source=home&utm_medium=card&utm_campaign=w26');

        $response->assertRedirect('https://campana.cl');

        $event = OutboundClickEvent::firstOrFail();
        $this->assertSame($profile->id, $event->profile_id);
        $this->assertSame($period->id, $event->ranking_period_id);
        $this->assertSame(OutboundClickSource::Ranking, $event->source);
        $this->assertSame('home', $event->utm_source);
        $this->assertSame('card', $event->utm_medium);
        $this->assertSame('w26', $event->utm_campaign);
    }

    #[Test]
    public function outbound_click_without_destination_returns_404(): void
    {
        $profile = $this->createProfile('Sin Destino');

        $this->get(route('outbound.click', $profile->slug))->assertNotFound();
        $this->assertSame(0, OutboundClickEvent::count());
    }

    private function createActivePeriod(): RankingPeriod
    {
        return RankingPeriod::create([
            'code' => 'W'.substr((string) Str::ulid(), 0, 8),
            'period_type' => RankingPeriodType::Weekly,
            'starts_at' => CarbonImmutable::parse('2026-09-01 00:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-09-08 00:00:00', 'UTC'),
            'status' => RankingPeriodStatus::Active,
            'configuration' => [
                'minimum_support_clp' => 1000,
                'maximum_support_clp' => 500000,
            ],
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

    private function createApprovedSupport(RankingPeriod $period, Profile $profile, int $amount, string $qualifiedAt): SupportTransaction
    {
        return SupportTransaction::create([
            'ranking_period_id' => $period->id,
            'profile_id' => $profile->id,
            'amount_clp' => $amount,
            'currency' => Currency::CLP,
            'type' => SupportTransactionType::Real,
            'status' => SupportTransactionStatus::Approved,
            'payment_gateway' => PaymentGateway::MercadoPago,
            'external_reference' => (string) Str::ulid(),
            'provider_transaction_id' => 'pref-'.Str::ulid(),
            'provider_order_id' => 'order-'.Str::ulid(),
            'checkout_created_at' => CarbonImmutable::parse('2026-09-02 09:00:00', 'UTC'),
            'provider_approved_at' => CarbonImmutable::parse($qualifiedAt, 'UTC'),
            'webhook_received_at' => CarbonImmutable::parse($qualifiedAt, 'UTC'),
            'ranking_qualified_at' => CarbonImmutable::parse($qualifiedAt, 'UTC'),
        ]);
    }
}
