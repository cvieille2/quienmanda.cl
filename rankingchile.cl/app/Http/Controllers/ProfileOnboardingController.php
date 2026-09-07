<?php

namespace App\Http\Controllers;

use App\Enums\ProfileSubmissionStatus;
use App\Enums\SupportTransactionStatus;
use App\Models\FeatureFlag;
use App\Models\ProfileCategory;
use App\Models\ProfileSubmission;
use App\Models\Region;
use App\Services\FeatureFlagsService;
use App\Services\PaymentLimitsService;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\ProfileCreationService;
use App\Services\ProfileSubmissionService;
use App\Services\RankingProjectionService;
use App\Services\SupportTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileOnboardingController extends Controller
{
    public function __construct(
        private ProfileSubmissionService $submissions,
        private ProfileCreationService $creation,
        private RankingProjectionService $projection,
        private SupportTransactionService $transactions,
        private PaymentGatewayInterface $gateway,
        private PaymentLimitsService $limits,
        private FeatureFlagsService $flags,
    ) {}

    public function index(Request $request): View
    {
        $positionPricing = $this->projection->projectAvailablePositions();

        $prefill = [
            'source' => $request->query('source', ''),
            'position' => (int) $request->query('position', 3),
            'amount' => (int) $request->query('amount', 0),
            'category' => (string) $request->query('category', ''),
            'region_id' => $request->query('region_id'),
        ];

        return view('entrar', [
            'paymentsEnabled' => $this->flags->isEnabled(FeatureFlag::KEY_PAYMENTS_ENABLED),
            'wizardSteps' => [
                ['title' => 'Pega tu perfil', 'hint' => 'El resto lo sacamos nosotros.'],
                ['title' => 'Tu proyecto', 'hint' => 'Nombre, descripción y redes.'],
                ['title' => 'Destino de tráfico', 'hint' => 'Obligatorio — o usa tu perfil como destino.'],
                ['title' => 'Posición y costo', 'hint' => 'Elige subir o publicar gratis.'],
                ['title' => 'Antes / después', 'hint' => 'Revisa el flujo completo.'],
            ],
            'positionPricing' => $positionPricing,
            'projectCategories' => ProfileCategory::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
            'regions' => Region::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
            'prefill' => $prefill,
            'defaultDraft' => [
                'source' => '',
                'handle' => 'tu-cuenta',
                'display_name' => '',
                'summary' => '',
                'destination_url' => '',
                'use_profile_as_destination' => false,
                'avatar_url' => '',
                'position' => $prefill['position'],
                'category' => $prefill['category'],
                'region_id' => $prefill['region_id'],
                'free_publish' => false,
            ],
            'legalMicrocopy' => [
                'No pedimos contraseña ni acceso a tus redes.',
                'La posición visible se confirma cuando el checkout queda confirmado.',
                'PUBLICAR GRATIS no usa pago y sigue siendo una publicación competitiva.',
                'La visibilidad externa no está garantizada.',
                'El destino final debe ser una URL pública y válida.',
            ],
        ]);
    }

    public function detect(Request $request)
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:60'],
            'region_id' => ['nullable', 'integer', Rule::exists('regions', 'id')],
            'source_url' => ['required', 'string', 'max:500'],
            'use_profile_as_destination' => ['nullable', 'boolean'],
            'destination_url' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn () => ! $request->boolean('use_profile_as_destination'))],
            'submitted_email' => ['nullable', 'email', 'max:191'],
            'links' => ['nullable', 'array'],
            'links.*.url' => ['nullable', 'string', 'max:500'],
            'links.*.label' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $submission = $this->submissions->create([
                ...$data,
                'category' => $data['category'] ?? 'General',
                'submitted_by_session_id' => $request->session()->getId(),
            ]);
        } catch (\InvalidArgumentException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'invalid_source_url', 'message' => $exception->getMessage()], 422);
            }

            return back()->withErrors(['source_url' => $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'submission_id' => $submission->id,
                'status' => $submission->status->value,
                'redirect_url' => route('entrar.show', $submission),
            ]);
        }

        return redirect()->route('entrar.show', $submission);
    }

    public function show(ProfileSubmission $submission): View
    {
        $submission->loadMissing(['profileCategory', 'links', 'profile', 'duplicateProfile']);

        return view('entrar.show', [
            'submission' => $submission,
            'profile' => $submission->profile,
            'isApproved' => $submission->status === ProfileSubmissionStatus::Approved,
        ]);
    }

    public function confirm(ProfileSubmission $submission)
    {
        if ($submission->duplicate_profile_id) {
            return $this->duplicateResponse($submission);
        }

        $profile = $this->creation->createFromSubmission($submission);

        if (request()->expectsJson()) {
            return response()->json([
                'profile_id' => $profile->id,
                'slug' => $profile->slug,
                'redirect_url' => route('profile.show', $profile->slug),
            ]);
        }

        return redirect()->route('profile.show', $profile->slug);
    }

    public function position(Request $request, ProfileSubmission $submission)
    {
        if ($submission->duplicate_profile_id) {
            return $this->duplicateResponse($submission);
        }

        $data = $request->validate([
            'amount_clp' => ['required', 'integer', 'min:1'],
        ]);

        $profile = $submission->profile ?? $this->creation->createFromSubmission($submission);
        $projection = $this->projection->project($profile, (int) $data['amount_clp']);

        if ($request->expectsJson()) {
            return response()->json($projection);
        }

        return view('entrar.position', [
            'submission' => $submission,
            'profile' => $profile,
            'projection' => $projection,
        ]);
    }

    public function checkout(Request $request, ProfileSubmission $submission)
    {
        if (! $this->flags->isEnabled(FeatureFlag::KEY_PAYMENTS_ENABLED)) {
            return $request->expectsJson()
                ? response()->json(['error' => 'checkout_disabled'], 503)
                : response('checkout_disabled', 503);
        }

        if ($submission->duplicate_profile_id) {
            return $this->duplicateResponse($submission);
        }

        $data = $request->validate([
            'amount_clp' => ['required', 'integer', 'min:1'],
            'supporter_name' => ['nullable', 'string', 'max:64'],
            'is_anonymous' => ['nullable', 'boolean'],
            'age_declared_18' => ['required', 'accepted'],
            'payer_email' => ['nullable', 'email', 'max:191'],
        ]);

        $profile = $submission->profile ?? $this->creation->createFromSubmission($submission);
        $this->transactions->assertAmountWithinLimits((int) $data['amount_clp']);
        $identity = $this->limits->resolvePayerIdentity($request);
        $this->limits->assertCanCheckout($request, (int) $data['amount_clp']);

        $tx = $this->transactions->createCheckout(
            $profile->id,
            (int) $data['amount_clp'],
            $identity,
            [
                'supporter_name' => $data['supporter_name'] ?? $profile->display_name,
                'is_anonymous' => (bool) $request->boolean('is_anonymous', true),
            ]
        );

        $checkout = $this->gateway->create($tx->amount_clp, $tx->external_reference, $tx->id.'-'.uniqid(), [
            'subject' => $profile->display_name,
        ]);

        $tx->update([
            'provider_transaction_id' => $checkout['token'],
            'provider_order_id' => $checkout['token'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'transaction_id' => $tx->id,
                'external_reference' => $tx->external_reference,
                'checkout_url' => $checkout['url'],
                'status' => SupportTransactionStatus::Pending->value,
                'profile_slug' => $profile->slug,
            ]);
        }

        return redirect()->away($checkout['url']);
    }

    private function duplicateResponse(ProfileSubmission $submission)
    {
        $payload = ['error' => 'duplicate_profile', 'duplicate_profile_id' => $submission->duplicate_profile_id];

        return request()->expectsJson()
            ? response()->json($payload, 422)
            : back()->withErrors($payload);
    }
}
