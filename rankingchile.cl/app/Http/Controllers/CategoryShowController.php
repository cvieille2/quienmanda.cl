<?php

namespace App\Http\Controllers;

use App\Models\ProfileCategory;
use App\Services\HeaderStatsService;
use App\Services\RankingPeriodService;
use App\Services\RankingProjectionService;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryShowController extends Controller
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
        private HeaderStatsService $headerStats,
        private RankingProjectionService $projection,
    ) {}

    public function __invoke(Request $request, string $slug): View
    {
        $category = ProfileCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $period = $this->periods->activePeriod();
        $allRanking = $period ? $this->ranking->rankingForPeriod($period->id, true) : [];

        // Filter ranking by this category
        $categoryRanking = collect($allRanking)->filter(fn ($r) => $r['category'] === $category->name)->values()->all();

        $categories = ProfileCategory::where('is_active', true)
            ->whereHas('profiles', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        return view('category-show', [
            'category'         => $category,
            'period'           => $period,
            'ranking'          => $categoryRanking,
            'leader'           => $categoryRanking[0] ?? null,
            'limits' => [
                'min' => (int) ($period?->configuration['minimum_support_clp'] ?? 1000),
                'max' => (int) ($period?->configuration['maximum_support_clp'] ?? 500000),
            ],
            'headerStats'      => $this->headerStats->forActivePeriod(),
            'categories'       => $categories,
            'activeCategorySlug' => $slug,
            'positionPricing'  => $this->projection->projectAvailablePositions($period),
            'paymentsEnabled'  => \App\Models\FeatureFlag::enabled(\App\Models\FeatureFlag::KEY_PAYMENTS_ENABLED),
            'checkoutToken'    => \Illuminate\Support\Str::random(32),
        ]);
    }
}
