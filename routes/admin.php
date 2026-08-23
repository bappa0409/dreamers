<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::middleware('auth')->prefix('admin')->name('admin.')->controller(AdminController::class)->group(function () {

    Route::get('/roles', 'roles')->middleware('permission:Role.view')->name('roles');
    Route::get('/user-roles', 'userRoles')->middleware('permission:Role.view')->name('user-roles');
    Route::get('/users', 'users')->middleware('permission:User.view')->name('users');

    Route::get('/members/create', 'createMember')->middleware('permission:Member.create')->name('members.create');
    Route::get('/members', 'members')->middleware('permission:Member.view')->name('members');

    Route::get('/nominees','nominees')
    ->middleware('permission:Nominee.view')
    ->name('nominees');

    Route::get(
    '/member-exits',
    'memberExits'
)
    ->middleware('permission:MemberExit.view')
    ->name('member-exits');

    Route::get('/welfare','welfare')
    ->middleware('permission:Welfare.view')
    ->name('welfare');

    Route::get(
    '/feedback-support',
    'feedbackSupport'
)
    ->middleware(
        'permission:FeedbackSupport.view'
    )
    ->name('feedback-support');

    Route::get('/investments', 'investments')->middleware('permission:Investment.view')->name('investments');

    Route::prefix('finance')->middleware('permission:Finance.view')->group(function () {
        Route::get('/', 'finance')->name('finance');
        Route::get('/incomes', 'incomes')->name('incomes');
        Route::get('/expenses', 'expenses')->name('expenses');
        Route::get('/charges', 'charges')->name('charges');
        Route::get('/assets', 'assets')->name('assets');
        Route::get('/accounts', 'accounts')->name('finance.accounts');
        Route::get('/journals', 'journals')->name('finance.journals');
        Route::get('/ledger', 'ledger')->name('finance.ledger');
        Route::get('/trial-balance', 'trialBalance')->name('finance.trial-balance');
        Route::get('/balance-sheet', 'balanceSheet')->name('finance.balance-sheet');
        Route::get('/profit-loss', 'profitLoss')->name('finance.profit-loss');
        Route::get('/subscription-payments', 'subscriptionPayments')->name('finance.subscription-payments');
        Route::get('/share-purchases', 'sharePurchases')->name('finance.share-purchases');
    });

    Route::get('/land', 'land')->middleware('permission:Land.view')->name('land');
    Route::get('/projects', 'projects')->middleware('permission:Project.view')->name('projects');
    Route::get('/loans','loans')
    ->middleware('permission:Loan.view')
    ->name('loans');

     // Association Activities
    Route::get('/tours', 'tours')->middleware('permission:Tour.view')->name('tours');
    Route::get('/meetings', 'meetings')->middleware('permission:Meeting.view')->name('meetings');
    Route::get('/committees','committees')
    ->middleware('permission:Committee.view')
    ->name('committees');

    Route::get('/polls', 'polls')->middleware('permission:Poll.view')->name('polls');
    Route::get('/notices', 'notices')->middleware('permission:Notice.view')->name('notices');
    Route::get('/notifications', 'notifications')->middleware('permission:Notification.view')->name('notifications');
    Route::get('/mailing', 'mailing')->middleware('permission:Mailing.view')->name('mailing');

    Route::get('/approvals', 'approvals')->middleware('permission:Approval.view')->name('approvals');
    Route::get('/approval-workflows', 'approvalWorkflows')->middleware('permission:Approval.view')->name('approval-workflows');

    Route::get('/reports', 'reports')->middleware('permission:Report.view')->name('reports');
    Route::get('/documents', 'documents')->middleware('permission:Document.view')->name('documents');

    Route::get('/landing-page', 'landingPage')->middleware('permission:Setting.view')->name('landing-page');
    Route::get('/settings', 'settings')->middleware('permission:Setting.view')->name('settings');
    Route::get('/backups', 'backups')->middleware('permission:Backup.view')->name('backups');
    Route::get('/activity-logs', 'activityLogs')->middleware('permission:Audit.view')->name('activity-logs');
});