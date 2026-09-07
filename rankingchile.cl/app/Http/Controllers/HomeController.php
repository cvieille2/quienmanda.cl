<?php

namespace App\Http\Controllers;

use App\Domain\Ranking\RankingContext;
use App\Enums\RankingPeriodWindow;
use App\Models\ProfileCategory;
use App\Services\FeatureFlagsService;
use App\Services\PaymentLimitsService;
use App\Services\RankingProjectionService;
use App\Services\RankingStatsService;
use App\Services\RankingService;
use App\Support\RankingProfileViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __construct(
        private RankingService $ranking,
        private FeatureFlagsService $flags,
        private RankingProjectionService $projection,
        private RankingStatsService $stats,
    ) {}

    public function __invoke(Request $request): View
    {
        $period = RankingPeriodWindow::fromQuery($request->query('period'));
        $limit = max(20, min((int) $request->integer('limit', 20), 100));
        $selectedCategory = $this->resolveCategory($request->query('category'));
        $context = new RankingContext($period, $selectedCategory?->id);

        $rankingPayload = $this->ranking->getRankingForFilters(
            context: 'home',
            category: $selectedCategory?->slug,
            period: $period->value,
            limit: $limit,
        );

        $rankings = $rankingPayload['ranking'];
        $stats = $this->stats->getStats($context);
        $totalProfiles = $rankingPayload['total'];

        $categories = ProfileCategory::where('is_active', true)
            ->whereHas('profiles', fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $positionPricing = $this->projection->projectAvailablePositionsForContext($context);
        $hasCompetitiveActivity = count($rankings) > 0;

        $profileIds = array_column($rankings, 'profile_id');
        $projections = $this->projection->projectMovesForProfilesForContext($profileIds, $context);

        $viewModels = RankingProfileViewModel::collection($rankings, $projections);
        $heroProjection = collect($positionPricing)->firstWhere('position', 1) ?? ($positionPricing[0] ?? null);
        $heroState = $hasCompetitiveActivity
            ? [
                'mode' => 'active',
                'headline' => 'ALGUIEN YA MANDA',
                'subheadline' => '¿VAS A DEJARLO ARRIBA?',
                'ctaLabel' => 'ROBAR EL #1',
            ]
            : [
                'mode' => 'empty',
                'headline' => 'EL #1 ESTÁ LIBRE',
                'subheadline' => 'Ocúpalo antes que otro.',
                'ctaLabel' => 'TOMAR EL #1',
            ];

        $periodHeadline = $selectedCategory
            ? $this->headlineForCategory($selectedCategory->displayName(), $period)
            : $period->headline();

        $homeQuery = function (?string $categorySlug, RankingPeriodWindow $periodValue, int $limitValue) use ($selectedCategory, $limit): array {
            $query = [];

            if ($categorySlug !== null) {
                $query['category'] = $categorySlug;
            }

            if ($periodValue !== RankingPeriodWindow::WEEK) {
                $query['period'] = $periodValue->value;
            }

            if ($limitValue !== 20) {
                $query['limit'] = $limitValue;
            }

            return $query;
        };

        $categoryFilters = collect([
            [
                'label' => 'Todos',
                'slug' => null,
                'active' => $selectedCategory === null,
                'url' => route('home', $homeQuery(null, $period, $limit)),
            ],
        ])->merge(
            $categories->map(fn (ProfileCategory $category) => [
                'label' => $category->displayName(),
                'slug' => $category->slug,
                'active' => $selectedCategory?->slug === $category->slug,
                'url' => route('home', $homeQuery($category->slug, $period, $limit)),
            ])
        );

        $periodFilters = collect(RankingPeriodWindow::cases())->map(fn (RankingPeriodWindow $option) => [
            'label' => $option->label(),
            'value' => $option->value,
            'active' => $period === $option,
            'url' => route('home', $homeQuery($selectedCategory?->slug, $option, $limit)),
        ]);

        $loadMoreUrl = $totalProfiles > $limit
            ? route('home', $homeQuery($selectedCategory?->slug, $period, min($limit + 20, $totalProfiles)))
            : null;

        return view('home', [
            'currentPeriod'        => $period,
            'currentCategory'      => $selectedCategory,
            'periodHeadline'       => $periodHeadline,
            'periodLabel'          => $period->label(),
            'periodStatsLabel'     => $period->statsLabel(),
            'ranking'              => $rankings,
            'rankingTotal'         => $totalProfiles,
            'hasCompetitiveActivity' => $hasCompetitiveActivity,
            'limit'                => $limit,
            'hasMoreProfiles'      => $totalProfiles > $limit,
            'nextLimit'            => min($limit + 20, $totalProfiles),
            'leader'               => $rankings[0] ?? null,
            'limits'               => [
                'min' => PaymentLimitsService::MIN_CLP,
                'max' => PaymentLimitsService::MAX_CLP,
            ],
            'paymentsEnabled'      => $this->flags->isEnabled(\App\Models\FeatureFlag::KEY_PAYMENTS_ENABLED),
            'sharingEnabled'       => $this->flags->isEnabled(\App\Models\FeatureFlag::KEY_SHARING_ENABLED, true),
            'checkoutToken'        => Str::random(32),
            'headerStats'          => $stats,
            'headerStatsLabel'     => $period->statsLabel(),
            'categories'           => $categories,
            'activeCategorySlug'   => $selectedCategory?->slug,
            'categoryFilters'      => $categoryFilters,
            'periodFilters'        => $periodFilters,
            'loadMoreUrl'          => $loadMoreUrl,
            'positionPricing'      => $positionPricing,
            'viewModels'           => $viewModels,
            'heroProjection'       => $heroProjection,
            'heroState'            => $heroState,
        ]);
    }

    private function resolveCategory(?string $value): ?ProfileCategory
    {
        if ($value === null || $value === '') {
            return null;
        }

        $query = ProfileCategory::query()->where('slug', $value);

        if (ctype_digit($value)) {
            $query->orWhereKey((int) $value);
        }

        return $query->first();
    }

    private function headlineForCategory(string $category, RankingPeriodWindow $period): string
    {
        return match ($period) {
            RankingPeriodWindow::TODAY => "¿Quién manda en {$category} hoy?",
            RankingPeriodWindow::WEEK => "¿Quién manda en {$category} esta semana?",
            RankingPeriodWindow::MONTH => "¿Quién manda en {$category} este mes?",
            RankingPeriodWindow::YEAR => "¿Quién manda en {$category} este año?",
        };
    }
}
