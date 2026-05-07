<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
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
* Route sociétés
*/

Route::get('/clients', function () {
    return view('clients.index');
})->middleware('auth');

Route::get('/clients/{slug}', function ($slug) {
    return view('clients.show', ['slug' => $slug]);
})->middleware('auth')->name('clients.view');

/*
* Routes demande d'activité
*/

Route::livewire('/activity-requests', 'pages::activity-requests.index')
    ->middleware('auth')
    ->name('activity-requests.index');

Route::livewire('/activity-requests/form/{activityRequestId?}', 'pages::activity-requests.form')
    ->middleware('auth')
    ->whereNumber('activityRequestId')
    ->name('activity-requests.form');

Route::livewire('/activity-requests/{activityRequestId}', 'pages::activity-requests.show')
    ->middleware('auth')
    ->whereNumber('activityRequestId')
    ->name('activity-requests.show');

/*
* Routes badge requests
*/

Route::livewire('/badge-requests', 'pages::badge-requests.index')
    ->middleware('auth')
    ->name('badge-requests.index');

Route::livewire('/badge-requests/form/{badgeRequestId?}', 'pages::badge-requests.form')
    ->middleware('auth')
    ->whereNumber('badgeRequestId')
    ->name('badge-requests.form');

Route::livewire('/badge-requests/{badgeRequestId}', 'pages::badge-requests.show')
    ->middleware('auth')
    ->whereNumber('badgeRequestId')
    ->name('badge-requests.show');

/*
* Routes badge management
*/

Route::livewire('/badge-management', 'pages::badge-management.index')
    ->middleware('auth')
    ->name('badge-management.index');

Route::livewire('/badge-management/form/{mode?}', 'pages::badge-management.form')
    ->middleware('auth')
    ->where('mode', 'linked|standalone')
    ->name('badge-management.form');

Route::livewire('/badge-management/{badgeId}', 'pages::badge-management.show')
    ->middleware('auth')
    ->whereNumber('badgeId')
    ->name('badge-management.show');

/*
* Routes coworkers
*/

Route::livewire('/coworkers', 'pages::coworkers.index')
    ->middleware('auth')
    ->name('coworkers.index');

Route::livewire('/coworkers/form/{coworkerId?}', 'pages::coworkers.form')
    ->middleware('auth')
    ->whereNumber('coworkerId')
    ->name('coworkers.form');

Route::livewire('/coworkers/{coworkerId}', 'pages::coworkers.show')
    ->middleware('auth')
    ->whereNumber('coworkerId')
    ->name('coworkers.show');

/*
* Routes trainings
*/

Route::get('/trainings', function () {
    if (auth()->user()->isClient() && ! auth()->user()->can_access_formation) {
        return redirect()->route('clients.view', ['slug' => auth()->user()->client->slug]);
    }

    return view('trainings.index');
})->middleware('auth');

Route::get('/trainings/client/{slug}', function ($slug) {
    if (auth()->user()->isClient() && ! auth()->user()->can_access_formation) {
        return redirect()->route('clients.view', ['slug' => auth()->user()->client->slug]);
    }

    return view('trainings.client', ['slug' => $slug]);
})->middleware('auth')->name('training.client');

/*
*
* Routes vehicle passes
*/

Route::get('/vehicle-pass', function () {
    if (auth()->user()->isClient()) {
        return redirect()->route('clients.view', ['slug' => auth()->user()->client->slug]);
    }

    return view('vehicle-pass.index');
})->middleware('auth');
