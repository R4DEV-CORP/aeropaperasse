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

/*
* Routes badge management
*/

Route::get('/badge-management', function () {
    return view('badge-management.index');
})->middleware('auth');

/*
* Routes coworkers
*/

Route::get('/coworkers', function () {
    return view('coworkers.index');
})->middleware('auth');

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
