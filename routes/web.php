<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordSetupController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::controller(WebsiteController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/activities', 'activities')->name('activities');
    Route::get('/transparency', 'transparency')->name('transparency');
    Route::get('/faq', 'faq')->name('faq');
});

Route::controller(SeoController::class)->group(function () {
    Route::get('/sitemap.xml', 'sitemap')->name('sitemap');
    Route::get('/robots.txt', 'robots')->name('robots');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.submit');

    Route::get('/setup-password', [PasswordSetupController::class, 'show'])->name('password.setup');
    Route::post('/setup-password', [PasswordSetupController::class, 'setup'])->middleware('throttle:5,1')->name('password.setup.submit');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Common
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
     Route::get('/change-password', [ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/change-password', [ForcePasswordChangeController::class, 'update'])->middleware('throttle:5,1')->name('password.force-change.submit');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});