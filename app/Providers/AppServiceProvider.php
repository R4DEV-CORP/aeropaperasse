<?php

namespace App\Providers;

use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Observers\BadgeObserver;
use App\Observers\BadgeRequestObserver;
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
        // Enregistrer les observers
        Badge::observe(BadgeObserver::class);
        BadgeRequest::observe(BadgeRequestObserver::class);
        // BadgeRequest::observe(BadgeRequesterObserver::class);
    }
}
