<?php

namespace App\Http\Controllers;

use App\Services\FeatureFlagsService;
use App\Services\RankingPeriodService;
use App\Services\RankingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __invoke(
        RankingPeriodService $periods,
        RankingService $ranking,
        FeatureFlagsService $flags,
    ): View {
        $period = $periods->closeExpiredAndSettle();
        $rankings = $ranking->rankingForPeriod($period?->id, true) ?? [];
        $cfg = $period?->configuration ?? [];

        return view('home', [
            'period'             => $period,
            'ranking'            => $rankings,
            'leader'             => $rankings[0] ?? null,
            'limits'             => [
                'min' => (int) ($cfg['minimum_support_clp'] ?? \App\Services\PaymentLimitsService::MIN_CLP),
                'max' => (int) ($cfg['maximum_support_clp'] ?? \App\Services\PaymentLimitsService::MAX_CLP),
            ],
            'paymentsEnabled'    => $flags->isEnabled(\App\Models\FeatureFlag::KEY_PAYMENTS_ENABLED),
            'sharingEnabled'     => $flags->isEnabled(\App\Models\FeatureFlag::KEY_SHARING_ENABLED, true),
            'checkoutToken'      => Str::random(32),
        ]);
    }
}
