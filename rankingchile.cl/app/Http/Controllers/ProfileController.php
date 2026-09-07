<?php

namespace App\Http\Controllers;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Services\FeatureFlagsService;
use App\Services\PaymentLimitsService;
use App\Services\RankingPeriodService;
use App\Services\RankingProjectionService;
use App\Services\RankingService;
use App\Services\ProfileViewService;
use App\Support\RankingProfileViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(
        Request $request,
        string $slug,
        RankingPeriodService $periods,
        RankingService $ranking,
        FeatureFlagsService $flags,
        RankingProjectionService $projectionService,
        ProfileViewService $profileViews,
    ): View {
        $profile = Profile::where('slug', $slug)->where('status', ProfileStatus::Active->value)->firstOrFail();

        $profileViews->record($profile, [
            'session_id' => $request->session()->getId(),
            'referrer' => $request->headers->get('referer'),
        ]);

        $period = $periods->closeExpiredAndSettle();
        $rankings = $ranking->rankingForPeriod($period?->id, true) ?? [];
        $leader = $rankings[0] ?? null;

        $entry = collect($rankings)->firstWhere('profile_id', $profile->id);
        $rank = $entry['position'] ?? null;
        $cfg = $period?->configuration ?? [];

        // --- Projection to #1 for this profile ---
        $moveProjection = $projectionService->projectProfileMove(
            profileId: $profile->id,
            rankingPeriodId: $period?->id,
            targetPosition: 1,
        );

        // --- View Model for the profile ---
        $viewModel = $entry
            ? RankingProfileViewModel::fromRankingRow($entry)
            : null;
        $headerSearchProfiles = collect($rankings)->map(fn (array $row) => [
            'name' => $row['display_name'],
            'slug' => $row['slug'],
            'category' => $row['category'],
        ])->values()->all();

        // --- Countdown helpers ---
        $periodEndsAt = $period?->ends_at?->timestamp;
        $periodRemainingText = $period?->ends_at
            ? $period->ends_at->diffForHumans(['parts' => 3, 'short' => false])
            : null;

        return view('profile', [
            'profile'              => $profile,
            'period'               => $period,
            'ranking'              => $rankings,
            'leader'               => $leader,
            'entry'                => $entry,
            'rank'                 => $rank,
            'viewModel'            => $viewModel,
            'headerSearchProfiles'  => $headerSearchProfiles,
            'projection'           => $moveProjection,
            'periodEndsAt'         => $periodEndsAt,
            'periodRemainingText'  => $periodRemainingText,
            'limits'               => [
                'min' => (int) ($cfg['minimum_support_clp'] ?? PaymentLimitsService::MIN_CLP),
                'max' => (int) ($cfg['maximum_support_clp'] ?? PaymentLimitsService::MAX_CLP),
            ],
            'paymentsEnabled'      => $flags->isEnabled(\App\Models\FeatureFlag::KEY_PAYMENTS_ENABLED),
            'sharingEnabled'       => $flags->isEnabled(\App\Models\FeatureFlag::KEY_SHARING_ENABLED, true),
            'checkoutToken'        => Str::random(32),
        ]);
    }
}
