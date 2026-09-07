<?php

namespace App\Http\Controllers;

use App\Services\CategoryPageService;
use App\Services\PaymentLimitsService;
use App\Services\RankingProjectionService;
use App\Support\RankingProfileViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryShowController extends Controller
{
    public function __construct(
        private CategoryPageService $page,
        private RankingProjectionService $projection,
    ) {}

    /**
     * Legacy URL /categorias/{slug} → 301 redirect to canonical /categoria/{slug}.
     */
    public function legacy(string $slug): RedirectResponse
    {
        return redirect()->route('category.show', $slug, 301);
    }

    /**
     * Canonical category home page.
     */
    public function show(Request $request, string $slug): View
    {
        $category = $this->page->resolve($slug)
            ?? abort(404, 'Categoría no encontrada.');

        $data = $this->page->show($category);

        $period = $data['period'];
        $ranking = $data['ranking'];
        $positionPricing = $data['positionPricing'];

        // ── Batch projections for all visible category profiles ──
        $profileIds = array_column($ranking, 'profile_id');
        $projections = $this->projection->projectMovesForProfiles($profileIds, $period);

        // ── ViewModels for category ranking rows ──
        $viewModels = RankingProfileViewModel::collection($ranking, $projections);
        $headerSearchProfiles = collect($ranking)->map(fn (array $row) => [
            'name' => $row['display_name'],
            'slug' => $row['slug'],
            'category' => $row['category'],
        ])->values()->all();

        // ── Hero projection (first position from positionPricing) ──
        $heroProjection = $positionPricing[0] ?? null;

        // ── Period timing for countdown ──
        $periodEndsAt = $period?->ends_at?->timestamp * 1000 ?? null;

        // ── Period remaining text (e.g. "Quedan 3d 5h") ──
        $periodRemainingText = $period?->ends_at
            ? 'Quedan ' . $period->ends_at->diffForHumans(null, null, true)
            : null;

        // ── Limits with constant fallbacks (no magic numbers) ──
        $cfg = $period?->configuration ?? [];

        return view('category-show', array_merge($data, [
            'viewModels'           => $viewModels,
            'headerSearchProfiles'  => $headerSearchProfiles,
            'heroProjection'       => $heroProjection,
            'periodEndsAt'         => $periodEndsAt,
            'periodRemainingText'  => $periodRemainingText,
            'limits'               => [
                'min' => (int) ($cfg['minimum_support_clp'] ?? PaymentLimitsService::MIN_CLP),
                'max' => (int) ($cfg['maximum_support_clp'] ?? PaymentLimitsService::MAX_CLP),
            ],
        ]));
    }
}
