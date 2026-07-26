<?php

namespace App\Providers;

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
        // APP_URL is fixed to http://localhost for local web use, but the mobile
        // app reaches this same backend through a Cloudflare quick tunnel whose
        // hostname changes on every restart. If route()/URL::signedRoute() always
        // built off APP_URL, every link handed to the mobile client would point at
        // "localhost" — unreachable from a phone. Building the root off the actual
        // incoming request instead makes generated (and signed) URLs correct for
        // whichever host the request actually arrived on, web or tunneled.
        if (!$this->app->runningInConsole()) {
            URL::forceRootUrl(request()->getSchemeAndHttpHost());
        }
    }
}
