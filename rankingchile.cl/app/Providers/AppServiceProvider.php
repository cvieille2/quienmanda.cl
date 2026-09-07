<?php

namespace App\Providers;

use App\View\Composers\HeaderStatsComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share the live header stats on the site header partial so every page
        // (including legal pages) keeps the same top bar as home/categories.
        View::composer('partials.site-header', HeaderStatsComposer::class);
    }
}
