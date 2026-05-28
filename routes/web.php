<?php

use App\Services\Auth\UserRedirectService;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Every web route is served in a tenant context: the host (app.* / client*.*)
| is resolved to a tenant by InitializeTenancyByDomain, and central domains are
| rejected. Authenticated app pages are additionally gated by tenant membership.
| See docs/multi-tenant-migration.md (Routing, Auth model).
|
*/

Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function (UserRedirectService $redirectService) {
        if (auth()->check()) {
            return redirect($redirectService->getRedirectPath(auth()->user()));
        }

        return redirect()->route('auth.login');
    });

    /*
    * Routes pour la connexion, la vérification 2FA et le changement de mot de passe
    */
    Route::livewire('/login', 'pages::auth.login')->name('auth.login');

    Route::livewire('/verify-2fa', 'pages::auth.verify-2fa')->name('auth.verify-2fa');

    Route::livewire('/change-password', 'pages::auth.change-password')->name('auth.change-password');

    Route::livewire('/forgot-password', 'pages::auth.forgot-password')->name('auth.forgot-password');

    Route::livewire('/reset-password', 'pages::auth.reset-password')->name('auth.reset-password');

    Route::get('/logout', function () {
        auth()->logout();

        return redirect()->route('auth.login');
    })->middleware('auth')->name('auth.logout');

    /*
    * Espace "pas d'accès à ce tenant" — authentifié mais volontairement hors du gate
    * tenant.member pour éviter une boucle de redirection.
    */
    Route::livewire('/no-access', 'pages::auth.no-access')
        ->middleware('auth')
        ->name('tenant.no-access');

    /*
    * Choix de l'espace — authentifié mais hors du gate tenant.member : permet à un user
    * sans accès au tenant courant (ex. login sur le portail `app`) de choisir un tenant
    * auquel il appartient. Voir docs/multi-tenant-migration.md.
    */
    Route::livewire('/choose-tenant', 'pages::auth.choose-tenant')
        ->middleware('auth')
        ->name('tenant.choose');

    /*
    * Application authentifiée — chaque page est filtrée par l'appartenance au tenant courant.
    */
    Route::middleware(['auth', 'tenant.member'])->group(function () {
        /*
        * Routes sociétés
        */
        Route::livewire('/companies', 'pages::companies.index')
            ->name('companies.index');

        Route::livewire('/companies/form/{companyId?}', 'pages::companies.form')
            ->whereNumber('companyId')
            ->name('companies.form');

        Route::livewire('/companies/{companyId}', 'pages::companies.show')
            ->whereNumber('companyId')
            ->name('companies.show');

        /*
        * Routes demande d'activité
        */
        Route::livewire('/activity-requests', 'pages::activity-requests.index')
            ->name('activity-requests.index');

        Route::livewire('/activity-requests/form/{activityRequestId?}', 'pages::activity-requests.form')
            ->whereNumber('activityRequestId')
            ->name('activity-requests.form');

        Route::livewire('/activity-requests/{activityRequestId}', 'pages::activity-requests.show')
            ->whereNumber('activityRequestId')
            ->name('activity-requests.show');

        /*
        * Routes badge requests
        */
        Route::livewire('/badge-requests', 'pages::badge-requests.index')
            ->name('badge-requests.index');

        Route::livewire('/badge-requests/form/{badgeRequestId?}', 'pages::badge-requests.form')
            ->whereNumber('badgeRequestId')
            ->name('badge-requests.form');

        Route::livewire('/badge-requests/{badgeRequestId}', 'pages::badge-requests.show')
            ->whereNumber('badgeRequestId')
            ->name('badge-requests.show');

        /*
        * Routes badge management
        */
        Route::livewire('/badge-management', 'pages::badge-management.index')
            ->name('badge-management.index');

        Route::livewire('/badge-management/form/{mode?}', 'pages::badge-management.form')
            ->where('mode', 'linked|standalone')
            ->name('badge-management.form');

        Route::livewire('/badge-management/{badgeId}', 'pages::badge-management.show')
            ->whereNumber('badgeId')
            ->name('badge-management.show');

        /*
        * Routes coworkers
        */
        Route::livewire('/coworkers', 'pages::coworkers.index')
            ->name('coworkers.index');

        Route::livewire('/coworkers/form/{coworkerId?}', 'pages::coworkers.form')
            ->whereNumber('coworkerId')
            ->name('coworkers.form');

        Route::livewire('/coworkers/{coworkerId}', 'pages::coworkers.show')
            ->whereNumber('coworkerId')
            ->name('coworkers.show');

        /*
        * Routes trainings
        */
        Route::livewire('/trainings', 'pages::trainings.index')
            ->name('trainings.index');

        Route::livewire('/trainings/{companyId}', 'pages::trainings.show')
            ->whereNumber('companyId')
            ->name('trainings.show');

        /*
        * Routes laissez-passer
        */
        Route::livewire('/vehicle-pass', 'pages::vehicle-pass.index')
            ->name('vehicle-pass.index');

        Route::livewire('/vehicle-pass/form/{mode?}', 'pages::vehicle-pass.form')
            ->where('mode', 'linked|standalone')
            ->name('vehicle-pass.form');

        Route::livewire('/vehicle-pass/{vehiclePassId}', 'pages::vehicle-pass.show')
            ->whereNumber('vehiclePassId')
            ->name('vehicle-pass.show');
    });
});
