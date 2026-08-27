<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;

Route::middleware(['auth', 'member'])->prefix('member')->name('member.')->group(function () {

    // Dashboard & Profile
    Route::get('/dashboard', [MemberController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [MemberController::class, 'profile'])->name('profile');
    Route::get('/nominees', [MemberController::class, 'nominees'])->name('nominees');

    // Subscriptions
    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [MemberController::class, 'subscriptions'])->name('subscriptions');
        Route::get('/payments', [MemberController::class, 'subscriptionPayments'])->name('subscription-payments');
    });

    // Shares & Investments
    Route::get('/shares', [MemberController::class, 'shares'])->name('shares');

    Route::prefix('investments')->group(function () {
        Route::get('/', [MemberController::class, 'investments'])->name('investments');
        Route::get('/active', [MemberController::class, 'activeInvestments'])->name('investments.active');
        Route::get('/completed', [MemberController::class, 'completedInvestments'])->name('investments.completed');
    });

    // Activities
    Route::get('/notices', [MemberController::class, 'notices'])->name('notices');
    Route::get('/polls', [MemberController::class, 'polls'])->name('polls');
    Route::get('/tours', [MemberController::class, 'tours'])->name('tours');
    Route::get('/meetings', [MemberController::class, 'meetings'])->name('meetings');
    Route::get('/loans', [MemberController::class, 'loans'])->name('loans');

    // Notifications
    Route::get('/notifications', [MemberController::class, 'notifications'])->name('notifications');

    // Welfare, Exit & Support
    Route::get('/welfare', [MemberController::class, 'welfare'])->name('welfare');
    Route::get('/exit', [MemberController::class, 'exit'])->name('exit');
    Route::get('/feedback-support', [MemberController::class, 'feedbackSupport'])->name('feedback-support');
});