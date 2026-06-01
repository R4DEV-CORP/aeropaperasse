<?php

namespace App\Providers;

use App\Http\Middleware\EnsureTenantMembership;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Models\PersonalAccessToken;
use App\Observers\BadgeObserver;
use App\Observers\BadgeRequestObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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
        /**
         * Re-run tenancy initialization on every Livewire update request.
         *
         * The web routes wrap pages in InitializeTenancyByDomain, but Livewire's
         * /livewire/update endpoint doesn't go through that group — only middleware
         * present in this list is replayed from the originating route. Without this,
         * a Livewire action on a tenant page loses tenant context: the default DB
         * connection falls back to `central` and queries on tenant tables (clients,
         * coworkers, …) 500 with "Base table not found in aeropaperasse_central".
         */
        Livewire::addPersistentMiddleware([
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureTenantMembership::class,
        ]);

        // Sanctum's `personal_access_tokens` table is central; pin the model so
        // `createToken()` during login doesn't try to write to `tenant_<id>.personal_access_tokens`.
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Enregistrer les observers
        // Badge::observe(BadgeObserver::class);
        // Désactivé temporairement pour le seeding
        // BadgeRequest::observe(BadgeRequestObserver::class);
        // BadgeRequest::observe(BadgeRequesterObserver::class);
    }
}
