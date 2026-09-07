<?php

namespace App\Providers;

use Carbon\Carbon;
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
        // La interfaz pública está en español; Carbon también debe renderizar
        // duraciones y nombres de días en ese idioma.
        Carbon::setLocale('es');

        // Share the live header stats on the site header partial so every page
        // (including legal pages) keeps the same top bar as home/categories.
        View::composer('partials.site-header', HeaderStatsComposer::class);
    }
}
