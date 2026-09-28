<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportFlagController;
use App\Http\Controllers\ReportMapController;
use App\Http\Controllers\ReportSupportController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// GeoJSON for the public map: reports inside the visible map area.
Route::get('/map/reports', ReportMapController::class)
    ->middleware('throttle:60,1')
    ->name('map.reports');

// Creating, supporting and flagging require login. The rules are in ReportPolicy.
Route::middleware('auth')->group(function () {
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('reports.store');

    Route::post('/reports/{report}/support', ReportSupportController::class)
        ->middleware('throttle:30,1')
        ->name('reports.support');
    Route::post('/reports/{report}/flag', ReportFlagController::class)
        ->middleware('throttle:10,1')
        ->name('reports.flag');
});

// Report detail page: public.
Route::get('/reports/{report}', [ReportController::class, 'show'])
    ->whereNumber('report')
    ->name('reports.show');

// Login with Google. "login" is the route Laravel redirects guests to.
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/login', [GoogleAuthController::class, 'redirect'])->name('login');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [GoogleAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
