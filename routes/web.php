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

Route::get('/sanctum/csrf-cookie', function (Request $request) {
    \Log::info('Setting CSRF cookie');

    // Définir le cookie CSRF
    return response()->noContent()->withCookie(cookie('XSRF-TOKEN', csrf_token(), 60 * 60 * 24));
});
// Route::get('/sanctum/csrf-cookie', function (Request $request) {
//     \Log::info('Setting CSRF cookie');
//     return response()->noContent();
// });
