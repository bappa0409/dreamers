<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;

Route::middleware(['auth', 'member'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [MemberController::class, 'profile'])->name('profile');

    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [MemberController::class, 'subscriptions'])->name('subscriptions');
        Route::get('/payments', [MemberController::class, 'subscriptionPayments'])->name('subscription-payments');
    });

    Route::get('/shares', [MemberController::class, 'shares'])->name('shares');

    Route::prefix('investments')->group(function () {
        Route::get('/', [MemberController::class, 'investments'])->name('investments');
        Route::get('/active', [MemberController::class, 'activeInvestments'])->name('investments.active');
        Route::get('/completed', [MemberController::class, 'completedInvestments'])->name('investments.completed');
    });

    Route::get('/notices', [MemberController::class, 'notices'])->name('notices');
    Route::get('/polls', [MemberController::class, 'polls'])->name('polls');
    Route::get('/notifications', [MemberController::class, 'notifications'])->name('notifications');
});