<?php

namespace App\Http\Controllers;

use App\Models\ProfileCategory;
use App\Services\FeatureFlagsService;
use App\Services\HeaderStatsService;
use App\Services\PaymentLimitsService;
use App\Services\RankingPeriodService;
use App\Services\RankingProjectionService;
use App\Services\RankingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
        private FeatureFlagsService $flags,
        private HeaderStatsService $headerStats,
        private RankingProjectionService $projection,
    ) {}

    public function __invoke(): View
    {
        $period = $this->periods->closeExpiredAndSettle();
        $rankings = $this->ranking->rankingForPeriod($period?->id, true) ?? [];
        $cfg = $period?->configuration ?? [];

        $categories = ProfileCategory::where('is_active', true)
            ->whereHas('profiles', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $positionPricing = $this->projection->projectAvailablePositions($period);

        return view('home', [
            'period'             => $period,
            'ranking'            => $rankings,
            'leader'             => $rankings[0] ?? null,
            'limits'             => [
                'min' => (int) ($cfg['minimum_support_clp'] ?? PaymentLimitsService::MIN_CLP),
                'max' => (int) ($cfg['maximum_support_clp'] ?? PaymentLimitsService::MAX_CLP),
            ],
            'paymentsEnabled'    => $this->flags->isEnabled(\App\Models\FeatureFlag::KEY_PAYMENTS_ENABLED),
            'sharingEnabled'     => $this->flags->isEnabled(\App\Models\FeatureFlag::KEY_SHARING_ENABLED, true),
            'checkoutToken'      => Str::random(32),
            'headerStats'        => $this->headerStats->forActivePeriod(),
            'categories'         => $categories,
            'activeCategorySlug' => null,
            'positionPricing'    => $positionPricing,
        ]);
    }
}
