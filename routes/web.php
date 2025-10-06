<?php

use Illuminate\Http\Request;
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
