<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
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
        // Set Carbon locale to Indonesian
        Carbon::setLocale('id');

        // Force HTTPS only outside local/testing to avoid broken Vite assets
        // when running local dev over HTTP.
        if (
            ! app()->environment(['local', 'testing'])
            && (env('FORCE_HTTPS', false) || str_starts_with(config('app.url'), 'https://'))
        ) {
            URL::forceScheme('https');
        }
    }
}
