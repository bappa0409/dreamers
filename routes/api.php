<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\ApprovalWorkflowController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\LandController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\MailCampaignController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MemberDashboardController;
use App\Http\Controllers\Api\MemberShareController;
use App\Http\Controllers\Api\MemberSubscriptionAdminController;
use App\Http\Controllers\Api\MemberSubscriptionController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\NotificationCampaignController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SubscriptionPaymentController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\TellerClosingController;
use App\Http\Controllers\Api\TellerController;
use App\Http\Controllers\Api\TellerDashboardController;
use App\Http\Controllers\Api\TellerManagementController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\MemberChargeController;
use App\Http\Controllers\Api\ChargePaymentController;
use App\Http\Controllers\Api\MemberInvestmentController;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function(){
    Route::post('/login',[AuthController::class,'login']);
    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout',[AuthController::class,'logout']);
        Route::get('/profile',[AuthController::class,'profile']);
    });
});

/*
|--------------------------------------------------------------------------
| Protected API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function(){

    // Dashboard
    Route::get('/dashboard',[DashboardController::class,'index']);

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    Route::get('/users',[UserController::class,'index'])->middleware('permission:User.view');
    Route::post('/users',[UserController::class,'store'])->middleware('permission:User.create');
    Route::get('/users/{user}',[UserController::class,'show'])->middleware('permission:User.view');
    Route::put('/users/{user}',[UserController::class,'update'])->middleware('permission:User.update');
    Route::patch('/users/{user}',[UserController::class,'update'])->middleware('permission:User.update');
    Route::patch('/users/{user}/toggle-status',[UserController::class,'toggleStatus'])->middleware('permission:User.update');
    Route::post('/users/{user}/reset-password',[UserController::class,'resetPassword'])->middleware('permission:User.update');
    Route::delete('/users/{user}',[UserController::class,'destroy'])->middleware('permission:User.delete');

    /*
    |--------------------------------------------------------------------------
    | Members
    |--------------------------------------------------------------------------
    */

    Route::get('/members',[MemberController::class,'index'])->middleware('permission:Member.view');
    Route::post('/members',[MemberController::class,'store'])->middleware('permission:Member.create');
    Route::get('/members/{member}',[MemberController::class,'show'])->middleware('permission:Member.view');
    Route::put('/members/{member}',[MemberController::class,'update'])
    ->middleware('permission:Member.update');

Route::patch('/members/{member}',[MemberController::class,'update'])
    ->middleware('permission:Member.update');

Route::post('/members/{member}/suspend',[MemberController::class,'suspend'])
    ->middleware('permission:Member.update');

Route::post('/members/{member}/activate',[MemberController::class,'activate'])
    ->middleware('permission:Member.update');
    Route::post('/members/{member}/send-password-setup',[MemberController::class,'sendPasswordSetup'])->middleware('permission:Member.update');

    /*
    |--------------------------------------------------------------------------
    | Member Roles
    |--------------------------------------------------------------------------
    */

    Route::get('/members/{member}/roles',[MemberController::class,'roles'])->middleware('permission:Role.view');
    Route::post('/members/{member}/roles',[MemberController::class,'assignRole'])->middleware('permission:Role.update,Role.edit');
    Route::delete('/members/{member}/roles',[MemberController::class,'removeRole'])->middleware('permission:Role.update,Role.edit');

    /*
    |--------------------------------------------------------------------------
    | Member Shares - Admin
    |--------------------------------------------------------------------------
    */

    Route::get('/member-shares',[MemberShareController::class,'adminIndex'])->middleware('permission:Finance.view');
    Route::get('/member-shares/{memberShare}',[MemberShareController::class,'show'])->middleware('permission:Finance.view');
    Route::post('/member-shares/{memberShare}/verify',[MemberShareController::class,'verify'])->middleware('permission:Finance.update');
    Route::post('/member-shares/{memberShare}/reject',[MemberShareController::class,'reject'])->middleware('permission:Finance.update');
    Route::get('/members/{member}/shares',[MemberShareController::class,'index'])->middleware('permission:Member.view');
    Route::post('/members/{member}/shares',[MemberShareController::class,'store'])->middleware('permission:Finance.create');

    /*
    |--------------------------------------------------------------------------
    | User Role Assignment
    |--------------------------------------------------------------------------
    */

    Route::get('/user-roles',[UserRoleController::class,'index'])->middleware('permission:Role.view');
    Route::get('/user-roles/available',[UserRoleController::class,'roles'])->middleware('permission:Role.view');
    Route::get('/user-roles/{user}',[UserRoleController::class,'show'])->middleware('permission:Role.view');
    Route::put('/user-roles/{user}',[UserRoleController::class,'sync'])->middleware('permission:Role.update,Role.edit');

    /*
    |--------------------------------------------------------------------------
    | Investments
    |--------------------------------------------------------------------------
    */

    Route::prefix('investments')->group(function(){
        Route::get('/statistics',[InvestmentController::class,'statistics'])->middleware('permission:Investment.view');
        Route::get('/options',[InvestmentController::class,'options'])->middleware('permission:Investment.view');
        Route::get('/',[InvestmentController::class,'index'])->middleware('permission:Investment.view');
        Route::post('/',[InvestmentController::class,'store'])->middleware('permission:Investment.create');
        Route::get('/{investment}',[InvestmentController::class,'show'])->middleware('permission:Investment.view');
        Route::put('/{investment}',[InvestmentController::class,'update'])->middleware('permission:Investment.update');
        Route::patch('/{investment}',[InvestmentController::class,'update'])->middleware('permission:Investment.update');
        Route::post('/{investment}/cancel',[InvestmentController::class,'cancel'])->middleware('permission:Investment.update');
        Route::delete('/{investment}',[InvestmentController::class,'destroy'])->middleware('permission:Investment.delete');
        Route::post('/{investment}/returns',[InvestmentController::class,'storeReturn'])->middleware('permission:Investment.update');
        Route::put('/returns/{investmentReturn}',[InvestmentController::class,'updateReturn'])->middleware('permission:Investment.update');
        Route::patch('/returns/{investmentReturn}',[InvestmentController::class,'updateReturn'])->middleware('permission:Investment.update');
        Route::delete('/returns/{investmentReturn}',[InvestmentController::class,'destroyReturn'])->middleware('permission:Investment.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Lands
    |--------------------------------------------------------------------------
    */

    Route::prefix('lands')->group(function(){
        Route::get('/statistics',[LandController::class,'statistics'])
            ->middleware('permission:Land.view');

        Route::get('/options',[LandController::class,'options'])
            ->middleware('permission:Land.view');

        Route::get('/',[LandController::class,'index'])
            ->middleware('permission:Land.view');

        Route::post('/',[LandController::class,'store'])
            ->middleware('permission:Land.create');

        Route::get('/{land}',[LandController::class,'show'])
            ->middleware('permission:Land.view');

        Route::put('/{land}',[LandController::class,'update'])
            ->middleware('permission:Land.update');

        Route::patch('/{land}',[LandController::class,'update'])
            ->middleware('permission:Land.update');

        Route::post('/{land}/valuations',[LandController::class,'addValuation'])
            ->middleware('permission:Land.update');

        Route::post('/{land}/documents',[LandController::class,'uploadDocument'])
            ->middleware('permission:Land.update');

        Route::delete('/{land}/documents/{landDocument}',[LandController::class,'deleteDocument'])
            ->middleware('permission:Land.update');

        Route::post('/{land}/sell',[LandController::class,'sell'])
            ->middleware('permission:Land.update');

        Route::delete('/{land}',[LandController::class,'destroy'])
            ->middleware('permission:Land.delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    Route::prefix('projects')->group(function(){
        Route::get('/statistics',[ProjectController::class,'statistics'])->middleware('permission:Project.view');
        Route::get('/members',[ProjectController::class,'members'])->middleware('permission:Project.view');
        Route::get('/',[ProjectController::class,'index'])->middleware('permission:Project.view');
        Route::post('/',[ProjectController::class,'store'])->middleware('permission:Project.create');
        Route::get('/{project}',[ProjectController::class,'show'])->middleware('permission:Project.view');
        Route::put('/{project}',[ProjectController::class,'update'])->middleware('permission:Project.update');
        Route::patch('/{project}',[ProjectController::class,'update'])->middleware('permission:Project.update');
        Route::post('/{project}/members',[ProjectController::class,'storeMember'])->middleware('permission:Project.update');
        Route::patch('/{project}/members/{projectMember}',[ProjectController::class,'updateMember'])->middleware('permission:Project.update');
        Route::delete('/{project}/members/{projectMember}',[ProjectController::class,'destroyMember'])->middleware('permission:Project.update');
        Route::delete('/{project}',[ProjectController::class,'destroy'])->middleware('permission:Project.delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    */

    Route::get('/roles',[RoleController::class,'index'])->middleware('permission:Role.view');
    Route::get('/roles/permissions',[RoleController::class,'permissions'])->middleware('permission:Role.view');
    Route::post('/roles',[RoleController::class,'store'])->middleware('permission:Role.create');
    Route::get('/roles/{role}',[RoleController::class,'show'])->middleware('permission:Role.view');
    Route::put('/roles/{role}',[RoleController::class,'update'])
    ->middleware('permission:Role.update');
    Route::patch('/roles/{role}',[RoleController::class,'update'])
    ->middleware('permission:Role.update');
    Route::delete('/roles/{role}',[RoleController::class,'destroy'])->middleware('permission:Role.delete');
    Route::put('/roles/{role}/permissions',[RoleController::class,'syncPermissions'])
    ->middleware('permission:Role.update');

    /*
    |--------------------------------------------------------------------------
    | Polls
    |--------------------------------------------------------------------------
    */

    Route::prefix('polls')->group(function(){
        Route::get('/statistics',[PollController::class,'statistics'])->middleware('permission:Poll.view');
        Route::get('/',[PollController::class,'index'])->middleware('permission:Poll.view');
        Route::post('/',[PollController::class,'store'])->middleware('permission:Poll.create');
        Route::get('/{poll}',[PollController::class,'show'])->middleware('permission:Poll.view');
        Route::put('/{poll}',[PollController::class,'update'])->middleware('permission:Poll.update');
        Route::patch('/{poll}',[PollController::class,'update'])->middleware('permission:Poll.update');
        Route::patch('/{poll}/toggle',[PollController::class,'toggle'])->middleware('permission:Poll.update');
        Route::post('/{poll}/vote',[PollController::class,'vote']);
        Route::get('/{poll}/results',[PollController::class,'results'])->middleware('permission:Poll.view');
        Route::delete('/{poll}',[PollController::class,'destroy'])->middleware('permission:Poll.delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Notices
    |--------------------------------------------------------------------------
    */

    Route::prefix('notices')->group(function(){
        Route::get('/',[NoticeController::class,'index'])->middleware('permission:Notice.view');
        Route::post('/',[NoticeController::class,'store'])->middleware('permission:Notice.create');
        Route::get('/{notice}',[NoticeController::class,'show'])->middleware('permission:Notice.view');
        Route::put('/{notice}',[NoticeController::class,'update'])->middleware('permission:Notice.update');
        Route::patch('/{notice}',[NoticeController::class,'update'])->middleware('permission:Notice.update');
        Route::delete('/{notice}',[NoticeController::class,'destroy'])->middleware('permission:Notice.delete');
        Route::patch('/{notice}/toggle-publish',[NoticeController::class,'togglePublish'])->middleware('permission:Notice.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Accounts & Finance
    |--------------------------------------------------------------------------
    */

    Route::prefix('finance')->group(function(){

        // Dashboard
        Route::get('/dashboard',[AccountingController::class,'dashboard'])->middleware('permission:Finance.view');

        // Transactions
        Route::get('/transactions/summary',[FinanceController::class,'summary'])->middleware('permission:Finance.view');
        Route::get('/transactions/options',[FinanceController::class,'options'])->middleware('permission:Finance.view');
        Route::get('/transactions',[FinanceController::class,'index'])->middleware('permission:Finance.view');
        Route::post('/transactions',[FinanceController::class,'store'])->middleware('permission:Finance.create');
        Route::get('/transactions/{transaction}',[FinanceController::class,'show'])->middleware('permission:Finance.view');
        Route::post('/transactions/{transaction}/reverse',[FinanceController::class,'reverse'])->middleware('permission:Finance.update');

        // Chart of Accounts
        Route::get('/accounts/summary',[AccountingController::class,'accountSummary'])->middleware('permission:Finance.view');
        Route::get('/accounts/options',[AccountingController::class,'accountOptions'])->middleware('permission:Finance.view');
        Route::get('/accounts/{account}/options',[AccountingController::class,'accountOptions'])->middleware('permission:Finance.view');
        Route::get('/accounts',[AccountingController::class,'accounts'])->middleware('permission:Finance.view');
        Route::post('/accounts',[AccountingController::class,'storeAccount'])->middleware('permission:Finance.create');
        Route::get('/accounts/{account}',[AccountingController::class,'account'])->middleware('permission:Finance.view');
        Route::put('/accounts/{account}',[AccountingController::class,'updateAccount'])->middleware('permission:Finance.update');
        Route::patch('/accounts/{account}',[AccountingController::class,'updateAccount'])->middleware('permission:Finance.update');
        Route::patch('/accounts/{account}/toggle',[AccountingController::class,'toggleAccount'])->middleware('permission:Finance.update');
        Route::delete('/accounts/{account}',[AccountingController::class,'destroyAccount'])->middleware('permission:Finance.delete');

        // Ledger
        Route::get('/ledger/accounts',[AccountingController::class,'ledgerAccounts'])->middleware('permission:Finance.view');
        Route::get('/accounts/{account}/ledger',[AccountingController::class,'ledger'])->middleware('permission:Finance.view');

        // Financial Reports
        // Route::get('/cash-bank',[AccountingController::class,'cashBank'])->middleware('permission:Finance.view');
        // Route::get('/income-expense',[AccountingController::class,'incomeExpense'])->middleware('permission:Finance.view');
        Route::get('/trial-balance',[AccountingController::class,'trialBalance'])->middleware('permission:Finance.view');
        Route::get('/profit-loss',[AccountingController::class,'profitLoss'])->middleware('permission:Finance.view');
        Route::get('/balance-sheet',[AccountingController::class,'balanceSheet'])->middleware('permission:Finance.view');

        // Charges
        Route::prefix('charges')->group(function(){
    Route::get(
        '/summary',
        [MemberChargeController::class,'summary']
    )->middleware('permission:Finance.view');

    Route::get(
        '/options',
        [MemberChargeController::class,'options']
    )->middleware('permission:Finance.view');

    Route::get(
        '/',
        [MemberChargeController::class,'index']
    )->middleware('permission:Finance.view');

    Route::post(
        '/',
        [MemberChargeController::class,'store']
    )->middleware('permission:Finance.create');

    Route::get(
        '/{memberCharge}',
        [MemberChargeController::class,'show']
    )->middleware('permission:Finance.view');

    Route::put(
        '/{memberCharge}',
        [MemberChargeController::class,'update']
    )->middleware('permission:Finance.update');

    Route::patch(
        '/{memberCharge}',
        [MemberChargeController::class,'update']
    )->middleware('permission:Finance.update');

    Route::post(
        '/{memberCharge}/pay',
        [MemberChargeController::class,'pay']
    )->middleware('permission:Finance.create');

    Route::post(
        '/{memberCharge}/cancel',
        [MemberChargeController::class,'cancel']
    )->middleware('permission:Finance.update');

    Route::post(
        '/{memberCharge}/waive',
        [MemberChargeController::class,'waive']
    )->middleware('permission:Finance.update');

    Route::delete(
        '/{memberCharge}',
        [MemberChargeController::class,'destroy']
    )->middleware('permission:Finance.delete');
});

// Charge Payments
Route::prefix('charge-payments')->group(function(){
    Route::get(
        '/summary',
        [ChargePaymentController::class,'summary']
    )->middleware('permission:Finance.view');

    Route::get(
        '/',
        [ChargePaymentController::class,'index']
    )->middleware('permission:Finance.view');

    Route::get(
        '/{chargePayment}',
        [ChargePaymentController::class,'show']
    )->middleware('permission:Finance.view');
});

        // Assets
        Route::prefix('assets')->group(function(){
            Route::get('/summary',[AssetController::class,'summary'])->middleware('permission:Finance.view');
            Route::get('/options',[AssetController::class,'options'])->middleware('permission:Finance.view');
            Route::get('/',[AssetController::class,'index'])->middleware('permission:Finance.view');
            Route::post('/',[AssetController::class,'store'])->middleware('permission:Finance.create');
            Route::get('/{asset}',[AssetController::class,'show'])->middleware('permission:Finance.view');
            Route::post('/{asset}/sell',[AssetController::class,'sell'])->middleware('permission:Finance.update');
            Route::post('/{asset}/dispose',[AssetController::class,'dispose'])->middleware('permission:Finance.update');
            Route::post('/{asset}/cancel',[AssetController::class,'cancel'])->middleware('permission:Finance.update');

            Route::post(
    '/{asset}/depreciate',
    [AssetController::class,'depreciate']
)->middleware('permission:Finance.update');

Route::get(
    '/{asset}/depreciations',
    [AssetController::class,'depreciations']
)->middleware('permission:Finance.view');
        });

        // Subscription Plans
        Route::prefix('subscription-plans')->group(function(){
            Route::get('/',[SubscriptionPlanController::class,'index'])->middleware('permission:Finance.view');
            Route::post('/',[SubscriptionPlanController::class,'store'])->middleware('permission:Finance.create');
            Route::put('/{subscriptionPlan}',[SubscriptionPlanController::class,'update'])->middleware('permission:Finance.update');
            Route::patch('/{subscriptionPlan}',[SubscriptionPlanController::class,'update'])->middleware('permission:Finance.update');
            Route::delete('/{subscriptionPlan}',[SubscriptionPlanController::class,'destroy'])->middleware('permission:Finance.delete');
        });

        // Income
        Route::prefix('incomes')->group(function(){
    Route::get(
        '/options',
        [IncomeController::class,'options']
    )->middleware('permission:Finance.view');

    Route::get(
        '/',
        [IncomeController::class,'index']
    )->middleware('permission:Finance.view');

    Route::post(
        '/',
        [IncomeController::class,'store']
    )->middleware('permission:Finance.create');

    Route::get(
        '/{income}',
        [IncomeController::class,'show']
    )->middleware('permission:Finance.view');

    Route::put(
        '/{income}',
        [IncomeController::class,'update']
    )->middleware('permission:Finance.update');

    Route::patch(
        '/{income}',
        [IncomeController::class,'update']
    )->middleware('permission:Finance.update');

    Route::post(
        '/{income}/cancel',
        [IncomeController::class,'cancel']
    )->middleware('permission:Finance.update');

    Route::delete(
        '/{income}',
        [IncomeController::class,'destroy']
    )->middleware('permission:Finance.delete');
        });

        // Expenses
        Route::prefix('expenses')->group(function(){
    Route::get(
        '/options',
        [ExpenseController::class,'options']
    )->middleware('permission:Finance.view');

    Route::get(
        '/',
        [ExpenseController::class,'index']
    )->middleware('permission:Finance.view');

    Route::post(
        '/',
        [ExpenseController::class,'store']
    )->middleware('permission:Finance.create');

    Route::get(
        '/{expense}',
        [ExpenseController::class,'show']
    )->middleware('permission:Finance.view');

    Route::put(
        '/{expense}',
        [ExpenseController::class,'update']
    )->middleware('permission:Finance.update');

    Route::patch(
        '/{expense}',
        [ExpenseController::class,'update']
    )->middleware('permission:Finance.update');

    Route::post(
        '/{expense}/cancel',
        [ExpenseController::class,'cancel']
    )->middleware('permission:Finance.update');

    Route::delete(
        '/{expense}',
        [ExpenseController::class,'destroy']
    )->middleware('permission:Finance.delete');
});

        // Monthly Subscriptions
        Route::prefix('subscriptions')->group(function(){
            Route::get('/summary',[MemberSubscriptionAdminController::class,'summary'])->middleware('permission:Finance.view');
            Route::get('/members',[MemberSubscriptionAdminController::class,'members'])->middleware('permission:Finance.view');
            Route::post('/generate-bulk',[MemberSubscriptionAdminController::class,'generateBulk'])->middleware('permission:Finance.create');
            Route::get('/',[MemberSubscriptionAdminController::class,'index'])->middleware('permission:Finance.view');
            Route::post('/',[MemberSubscriptionAdminController::class,'store'])->middleware('permission:Finance.create');
            Route::post('/{memberSubscription}/generate-due',[MemberSubscriptionAdminController::class,'generateDue'])->middleware('permission:Finance.create');
            Route::post('/{memberSubscription}/deactivate',[MemberSubscriptionAdminController::class,'deactivate'])->middleware('permission:Finance.update');
        });

        // Subscription Payments
        Route::prefix('subscription-payments')->group(function(){
            Route::get('/',[SubscriptionPaymentController::class,'index'])->middleware('permission:Finance.view');
            Route::get('/{subscriptionPayment}',[SubscriptionPaymentController::class,'show'])->middleware('permission:Finance.view');
            Route::post('/{subscriptionPayment}/verify',[SubscriptionPaymentController::class,'verify'])->middleware('permission:Finance.update');
            Route::post('/{subscriptionPayment}/reject',[SubscriptionPaymentController::class,'reject'])->middleware('permission:Finance.update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Campaigns
    |--------------------------------------------------------------------------
    */

    Route::prefix('notification-campaigns')->group(function(){
        Route::get('/',[NotificationCampaignController::class,'index'])->middleware('permission:Notification.view');
        Route::get('/statistics',[NotificationCampaignController::class,'statistics'])->middleware('permission:Notification.view');
        Route::get('/recipients',[NotificationCampaignController::class,'recipients'])->middleware('permission:Notification.send');
        Route::post('/send',[NotificationCampaignController::class,'send'])->middleware('permission:Notification.send');
        Route::get('/{campaign}',[NotificationCampaignController::class,'show'])->middleware('permission:Notification.view');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->group(function(){
        Route::get('/',[NotificationController::class,'index']);
        Route::get('/unread',[NotificationController::class,'unread']);
        Route::get('/unread-count',[NotificationController::class,'unreadCount']);
        Route::patch('/read-all',[NotificationController::class,'markAllAsRead']);
        Route::patch('/{id}/read',[NotificationController::class,'markAsRead']);
        Route::delete('/{id}',[NotificationController::class,'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Mailing
    |--------------------------------------------------------------------------
    */

    Route::prefix('mail-campaigns')->group(function(){
        Route::get('/',[MailCampaignController::class,'index'])->middleware('permission:Mail.view');
        Route::get('/recipients',[MailCampaignController::class,'recipients'])->middleware('permission:Mail.view');
        Route::post('/',[MailCampaignController::class,'store'])->middleware('permission:Mail.create');
        Route::get('/{mailCampaign}',[MailCampaignController::class,'show'])->middleware('permission:Mail.view');
        Route::put('/{mailCampaign}',[MailCampaignController::class,'update'])->middleware('permission:Mail.update');
        Route::post('/{mailCampaign}/recipients',[MailCampaignController::class,'addRecipients'])->middleware('permission:Mail.update');
        Route::delete('/{mailCampaign}/recipients/{recipient}',[MailCampaignController::class,'removeRecipient'])->middleware('permission:Mail.update');
        Route::post('/{mailCampaign}/send',[MailCampaignController::class,'send'])->middleware('permission:Mail.send');
        Route::delete('/{mailCampaign}',[MailCampaignController::class,'destroy'])->middleware('permission:Mail.delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::prefix('activity-logs')->middleware('permission:Audit.view')->group(function(){
        Route::get('/',[ActivityLogController::class,'index']);
        Route::get('/filters',[ActivityLogController::class,'filters']);
        Route::get('/{activityLog}',[ActivityLogController::class,'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    Route::prefix('language')->group(function(){
        Route::get('/',[LanguageController::class,'current']);
        Route::post('/switch',[LanguageController::class,'switch']);
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::prefix('reports')->middleware('permission:Report.view')->group(function(){
        Route::get('/summary',[ReportController::class,'summary']);
        Route::get('/branding',[ReportController::class,'branding']);
        Route::get('/{module}',[ReportController::class,'module'])->whereIn('module',['members','finance','investments','land','projects','polls','notices']);
        Route::get('/{module}/export/{format}',[ReportController::class,'export'])->whereIn('module',['members','finance','investments','land','projects','polls','notices'])->whereIn('format',['pdf']);
    });

    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */

    Route::prefix('documents')->group(function(){
        Route::get('/',[DocumentController::class,'index'])->middleware('permission:Document.view');
        Route::get('/filters',[DocumentController::class,'filters'])->middleware('permission:Document.view');
        Route::post('/',[DocumentController::class,'store'])->middleware('permission:Document.create');
        Route::get('/{document}/preview',[DocumentController::class,'preview'])->middleware('permission:Document.view');
        Route::get('/{document}/download',[DocumentController::class,'download'])->middleware('permission:Document.view');
        Route::get('/{document}',[DocumentController::class,'show'])->middleware('permission:Document.view');
        Route::post('/{document}',[DocumentController::class,'update'])->middleware('permission:Document.update');
        Route::put('/{document}',[DocumentController::class,'update'])->middleware('permission:Document.update');
        Route::delete('/{document}',[DocumentController::class,'destroy'])->middleware('permission:Document.delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::prefix('settings')->group(function(){
        Route::get('/',[SettingController::class,'index'])->middleware('permission:Setting.view');
        Route::get('/public',[SettingController::class,'publicSettings']);
        Route::post('/upload',[SettingController::class,'uploadImage'])->middleware('permission:Setting.update');
        Route::get('/{key}',[SettingController::class,'show'])->middleware('permission:Setting.view');
        Route::post('/',[SettingController::class,'store'])->middleware('permission:Setting.update');
        Route::delete('/{key}',[SettingController::class,'destroy'])->middleware('permission:Setting.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Backup
    |--------------------------------------------------------------------------
    */

    Route::prefix('backups')->group(function(){
        Route::get('/',[BackupController::class,'index'])->middleware('permission:Backup.view');
        Route::post('/',[BackupController::class,'store'])->middleware('permission:Backup.create');
        Route::get('/{backup}/download',[BackupController::class,'download'])->middleware('permission:Backup.view');
        Route::delete('/{backup}',[BackupController::class,'destroy'])->middleware('permission:Backup.delete');
        Route::post('/import',[BackupController::class,'import'])->middleware('permission:Backup.import');
    });

    /*
    |--------------------------------------------------------------------------
    | Approvals
    |--------------------------------------------------------------------------
    */

    Route::prefix('approvals')->group(function(){
        Route::get('/',[ApprovalController::class,'index'])->middleware('permission:Approval.view');
        Route::get('/{approvalRequest}',[ApprovalController::class,'show'])->middleware('permission:Approval.view');
        Route::post('/{approvalRequest}/approve',[ApprovalController::class,'approve'])->middleware('permission:Approval.approve');
        Route::post('/{approvalRequest}/reject',[ApprovalController::class,'reject'])->middleware('permission:Approval.reject');
        Route::post('/{approvalRequest}/cancel',[ApprovalController::class,'cancel'])->middleware('permission:Approval.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Approval Workflows
    |--------------------------------------------------------------------------
    */

    Route::prefix('approval-workflows')->group(function(){
        Route::get('/approvers',[ApprovalWorkflowController::class,'approvers'])->middleware('permission:Approval.update');
        Route::get('/',[ApprovalWorkflowController::class,'index'])->middleware('permission:Approval.view');
        Route::post('/',[ApprovalWorkflowController::class,'store'])->middleware('permission:Approval.update');
        Route::put('/{approvalWorkflow}',[ApprovalWorkflowController::class,'update'])->middleware('permission:Approval.update');
        Route::patch('/{approvalWorkflow}/toggle',[ApprovalWorkflowController::class,'toggle'])->middleware('permission:Approval.update');
        Route::delete('/{approvalWorkflow}',[ApprovalWorkflowController::class,'destroy'])->middleware('permission:Approval.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Teller
    |--------------------------------------------------------------------------
    */

    Route::prefix('teller')->group(function () {
        Route::get('/dashboard', [TellerDashboardController::class, 'index'])
            ->middleware('permission:Finance.view');

        Route::get('/options', [TellerController::class, 'options'])
            ->middleware('permission:Finance.view');

        Route::get('/balance', [TellerController::class, 'balance'])
            ->middleware('permission:Finance.view');

        Route::get('/transactions', [TellerController::class, 'transactions'])
            ->middleware('permission:Finance.view');

        Route::post('/receive', [TellerController::class, 'receive'])
            ->middleware('permission:Finance.create');

        Route::post('/payment', [TellerController::class, 'payment'])
            ->middleware('permission:Finance.create');

        Route::post('/transactions/{tellerTransaction}/cancel', [TellerController::class, 'cancel'])
            ->middleware('permission:Finance.update');

        Route::get('/closing/summary', [TellerClosingController::class, 'summary'])
            ->middleware('permission:Finance.view');

        Route::post('/closing', [TellerClosingController::class, 'close'])
            ->middleware('permission:Finance.create');

        Route::post('/closing/{date}/reopen', [TellerClosingController::class, 'reopen'])
            ->middleware('permission:Finance.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Teller Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('teller-management')->group(function(){
        Route::get('/',[TellerManagementController::class,'index'])->middleware('permission:User.view');
        Route::get('/{user}',[TellerManagementController::class,'show'])->middleware('permission:User.view');
        Route::get('/{user}/transactions',[TellerManagementController::class,'transactions'])->middleware('permission:Finance.view');
        Route::get('/{user}/closings',[TellerManagementController::class,'closings'])->middleware('permission:Finance.view');
        Route::patch('/{user}/toggle-status',[TellerManagementController::class,'toggleStatus'])->middleware('permission:User.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Member Portal
    |--------------------------------------------------------------------------
    */

    Route::prefix('member')->middleware('member')->group(function(){
        Route::get('/dashboard',[MemberDashboardController::class,'index']);
        Route::get('/profile',[MemberDashboardController::class,'profile']);
        Route::put('/profile',[MemberDashboardController::class,'updateProfile']);
        Route::get('/polls',[MemberDashboardController::class,'polls']);
        Route::get('/notices',[MemberDashboardController::class,'notices']);

        Route::prefix('subscriptions')->group(function(){
            Route::get(
                '/payments',
                [MemberSubscriptionController::class,'payments']
            );

            Route::get(
                '/',
                [MemberSubscriptionController::class,'index']
            );

            Route::post(
                '/dues/{subscriptionDue}/pay',
                [MemberSubscriptionController::class,'pay']
            );


        });

        Route::prefix('shares')->group(function(){
            Route::get('/',[MemberShareController::class,'myShares']);
            Route::post('/',[MemberShareController::class,'purchase']);
        });

        Route::prefix('investments')->group(function(){
            Route::get(
                '/',
                [MemberInvestmentController::class,'index']
            );

            Route::get(
                '/{investment}',
                [MemberInvestmentController::class,'show']
            );
        });
    });
});