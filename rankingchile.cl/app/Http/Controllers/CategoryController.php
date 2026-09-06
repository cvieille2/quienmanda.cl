<?php

namespace App\Http\Controllers;

use App\Services\RankingPeriodService;
use App\Services\RankingService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private RankingPeriodService $periods,
        private RankingService $ranking,
    ) {}

    public function __invoke(Request $request)
    {
        $period = $this->periods->activePeriod();
        $allRanking = $period
            ? $this->ranking->rankingForPeriod($period->id, false)
            : [];

        // Agrupar por categoría y tomar top 3 de cada una
        $grouped = collect($allRanking)
            ->groupBy('category')
            ->map(fn ($profiles, $category) => [
                'name'     => $category,
                'slug'     => str($category)->slug()->toString(),
                'total'    => $profiles->sum('total_real_clp'),
                'count'    => $profiles->count(),
                'top3'     => $profiles->take(3)->values()->all(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();

        // "Más activas": las 3 categorías con más monto total
        $hotCategories = collect($grouped)->take(3)->values()->all();

        return view('categories', [
            'period'        => $period,
            'categories'    => $grouped,
            'hotCategories' => $hotCategories,
        ]);
    }
}
