<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
    return view('welcome');
});

/*
* Routes pour la connexion, la vérification 2FA et le changement de mot de passe
*/
Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

Route::get('/verify-2fa', function () {
    return view('auth.verify-2fa');
});

Route::get('/change-password', function () {
    return view('auth.change-password');
});

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

Route::get('/activity-requests', function () {
    return view('activity-requests.index');
})->middleware('auth');

/*
* Routes badge requests
*/

Route::get('/badge-requests', function () {
    return view('badge-requests.index');
})->middleware('auth');

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
    return view('trainings.index');
})->middleware('auth');

Route::get('/trainings/client/{slug}', function ($slug) {
    return view('trainings.client', ['slug' => $slug]);
})->middleware('auth')->name('training.client');

/*
*
* Routes vehicle passes
*/

Route::get('/vehicle-pass', function () {
    return view('vehicle-pass.index');
})->middleware('auth');
