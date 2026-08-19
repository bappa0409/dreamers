<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordSetupController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/',fn()=>view('landing.index'))->name('home');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function(){
    Route::get('/login',[LoginController::class,'showLogin'])->name('login');
    Route::post('/login',[LoginController::class,'login'])->middleware('throttle:5,1')->name('login.submit');

    Route::get('/setup-password',[PasswordSetupController::class,'show'])->name('password.setup');
    Route::post('/setup-password',[PasswordSetupController::class,'setup'])->middleware('throttle:5,1')->name('password.setup.submit');

    Route::get('/forgot-password',[PasswordResetController::class,'showForgotForm'])->name('password.request');
    Route::post('/forgot-password',[PasswordResetController::class,'sendResetLink'])->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password',[PasswordResetController::class,'showResetForm'])->name('password.reset');
    Route::post('/reset-password',[PasswordResetController::class,'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Common
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function(){
    Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::post('/logout',[LoginController::class,'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function(){
    Route::get('/roles',[AdminController::class,'roles'])->middleware('permission:Role.view')->name('roles');
    Route::get('/user-roles',[AdminController::class,'userRoles'])->middleware('permission:Role.view')->name('user-roles');

    Route::get('/members',[AdminController::class,'members'])->middleware('permission:Member.view')->name('members');
    Route::get('/members/create',[AdminController::class,'createMember'])->middleware('permission:Member.create')->name('members.create');

    Route::get('/investments',[AdminController::class,'investments'])->middleware('permission:Investment.view')->name('investments');

    Route::get('/finance',[AdminController::class,'finance'])->middleware('permission:Finance.view')->name('finance');

    Route::get('/land',[AdminController::class,'land'])->middleware('permission:Land.view')->name('land');
    Route::get('/projects',[AdminController::class,'projects'])->middleware('permission:Project.view')->name('projects');
    Route::get('/polls',[AdminController::class,'polls'])->middleware('permission:Poll.view')->name('polls');

    Route::get('/notices',[AdminController::class,'notices'])->middleware('permission:Notice.view')->name('notices');
    Route::get('/notifications',[AdminController::class,'notifications'])->middleware('permission:Notification.view')->name('notifications');

    Route::get('/reports',[AdminController::class,'reports'])->middleware('permission:Report.view')->name('reports');
    Route::get('/documents',[AdminController::class,'documents'])->middleware('permission:Document.view')->name('documents');
    Route::get('/approvals',[AdminController::class,'approvals'])->middleware('permission:Approval.view')->name('approvals');

    Route::get('/mailing',[AdminController::class,'mailing'])->middleware('permission:Mailing.view')->name('mailing');
    Route::get('/users',[AdminController::class,'users'])->middleware('permission:User.view')->name('users');

    Route::get('/landing-page',[AdminController::class,'landingPage'])->middleware('permission:Setting.view')->name('landing-page');
    Route::get('/settings',[AdminController::class,'settings'])->middleware('permission:Setting.view')->name('settings');
    Route::get('/backups',[AdminController::class,'backups'])->middleware('permission:Backup.view')->name('backups');
    Route::get('/activity-logs',[AdminController::class,'activityLogs'])->middleware('permission:Audit.view')->name('activity-logs');
});

/*
|--------------------------------------------------------------------------
| Member Personal Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','member'])->prefix('member')->name('member.')->group(function(){
    Route::get('/profile',[MemberController::class,'profile'])->name('profile');
    Route::get('/investments',[MemberController::class,'investments'])->name('investments');
    Route::get('/projects',[MemberController::class,'projects'])->name('projects');
    Route::get('/land-investments',[MemberController::class,'landInvestments'])->name('land-investments');
    Route::get('/polls',[MemberController::class,'polls'])->name('polls');
    Route::get('/notices',[MemberController::class,'notices'])->name('notices');
});