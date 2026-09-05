<?php

namespace App\Http\Controllers;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Services\FeatureFlagsService;
use App\Services\RankingPeriodService;
use App\Services\RankingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(
        string $slug,
        RankingPeriodService $periods,
        RankingService $ranking,
        FeatureFlagsService $flags,
    ): View {
        $profile = Profile::where('slug', $slug)->where('status', ProfileStatus::Active->value)->firstOrFail();

        $period = $periods->closeExpiredAndSettle();
        $rankings = $ranking->rankingForPeriod($period?->id, true) ?? [];
        $leader = $rankings[0] ?? null;

        $entry = collect($rankings)->firstWhere('profile_id', $profile->id);
        $rank = $entry['position'] ?? null;

        return view('profile', [
            'profile'          => $profile,
            'period'           => $period,
            'ranking'          => $rankings,
            'leader'           => $leader,
            'entry'            => $entry,
            'rank'             => $rank,
            'paymentsEnabled'  => $flags->isEnabled(\App\Models\FeatureFlag::KEY_PAYMENTS_ENABLED),
            'sharingEnabled'   => $flags->isEnabled(\App\Models\FeatureFlag::KEY_SHARING_ENABLED, true),
            'checkoutToken'    => Str::random(32),
        ]);
    }
}
