<?php

namespace App\Http\Controllers;

use App\Domain\Ranking\RankingContext;
use App\Enums\RankingPeriodWindow;
use App\Services\RankingPeriodService;
use App\Services\RankingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProfileDirectoryController extends Controller
{
    public function __invoke(
        Request $request,
        RankingPeriodService $periods,
        RankingService $ranking,
    ): View {
        $period = $periods->activePeriod();
        // Mantener el mismo origen que la home: la ventana vigente, no el
        // acumulado crudo de un período interno que puede no coincidir con
        // los filtros públicos actuales.
        $rows = $ranking->getRanking(new RankingContext(RankingPeriodWindow::WEEK), true);

        return view('profile-directory', [
            'profiles' => array_slice($rows, 0, 100),
            'rankingTotal' => count($rows),
            'headerSearchProfiles' => array_map(static fn (array $row) => [
                'name' => $row['display_name'],
                'slug' => $row['slug'],
                'category' => $row['category'],
            ], $rows),
            'periodLabel' => $period ? 'Ranking actual' : 'Ranking',
        ]);
    }
}
