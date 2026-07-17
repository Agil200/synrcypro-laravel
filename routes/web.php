<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Login
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLogin'])
    ->name('login');

/*
|--------------------------------------------------------------------------
| Google Login
|--------------------------------------------------------------------------
*/

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])
    ->name('auth.google');

Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

/*
|--------------------------------------------------------------------------
| Guest Login
|--------------------------------------------------------------------------
*/

Route::post('/auth/guest', [AuthController::class, 'loginAsGuest'])
    ->name('auth.guest');

/*
|--------------------------------------------------------------------------
| Halaman yang Membutuhkan Login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Utama
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Menu Manpower
    |--------------------------------------------------------------------------
    */

    Route::get('/manpower', function () {
        return view('manpower');
    })->name('manpower');

    /*
    |--------------------------------------------------------------------------
    | Menu People Development
    |--------------------------------------------------------------------------
    */

    Route::get('/people-development', function () {
        return view('people-development');
    })->name('people-development');

    /*
    |--------------------------------------------------------------------------
    | Menu Database
    |--------------------------------------------------------------------------
    */

    Route::get('/database', function () {
        return view('database');
    })->name('database');

    /*
    |--------------------------------------------------------------------------
    | Menu Admin All
    |--------------------------------------------------------------------------
    */

    Route::get('/admin-all', function () {
        return view('admin-all');
    })->name('admin-all');

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});