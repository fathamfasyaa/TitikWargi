<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// Login with Google. "login" is the route Laravel redirects guests to.
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/login', [GoogleAuthController::class, 'redirect'])->name('login');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [GoogleAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
