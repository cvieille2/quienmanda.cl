<?php

namespace App\Http\Controllers;

use App\Models\ProfileCategory;
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

        // Base de categorías: first-class activas + categorías legacy presentes en el ranking.
        // Así una categoría sin perfiles todavía aparece en el listado (CTA "ser el primero").
        $knownNames = ProfileCategory::query()->active()->pluck('name');
        $rankedNames = collect($allRanking)->pluck('category')->filter();

        $names = $knownNames
            ->concat($rankedNames->diff($knownNames))
            ->unique()
            ->values();

        $grouped = $names
            ->map(fn (string $category) => [
                'name'  => $category,
                'slug'  => str($category)->slug()->toString(),
                'ranked' => collect($allRanking)->where('category', $category),
            ])
            ->map(fn (array $cat) => [
                'name'  => $cat['name'],
                'slug'  => $cat['slug'],
                'total' => $cat['ranked']->sum('total_real_clp'),
                'count' => $cat['ranked']->count(),
                'top3'  => $cat['ranked']->take(3)->values()->all(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();

        // "Más activas": las 3 categorías con más monto total (solo con perfiles).
        $hotCategories = collect($grouped)
            ->filter(fn (array $cat) => $cat['count'] > 0)
            ->take(3)
            ->values()
            ->all();
        $headerSearchProfiles = collect($allRanking)->map(fn (array $row) => [
            'name' => $row['display_name'],
            'slug' => $row['slug'],
            'category' => $row['category'],
        ])->values()->all();

        return view('categories', [
            'period'        => $period,
            'categories'    => $grouped,
            'hotCategories' => $hotCategories,
            'headerSearchProfiles' => $headerSearchProfiles,
        ]);
    }
}
