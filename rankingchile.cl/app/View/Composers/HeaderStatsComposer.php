<?php

namespace App\View\Composers;

use App\Services\HeaderStatsService;
use Illuminate\View\View;

class HeaderStatsComposer
{
    public function __construct(
        private HeaderStatsService $headerStats,
    ) {}

    /**
     * Share header stats when the controller did not explicitly provide them,
     * keeping the site header consistent across every page (home, categories,
     * legal pages, etc.).
     */
    public function compose(View $view): void
    {
        if (! $view->offsetExists('headerStats')) {
            $view->with('headerStats', $this->headerStats->forActivePeriod());
        }
    }
}
