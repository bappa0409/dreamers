<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\LandController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MailCampaignController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\TellerController;
use App\Http\Controllers\Api\TellerClosingController;
use App\Http\Controllers\Api\TellerManagementController;
use App\Http\Controllers\Api\TellerDashboardController;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\MemberDashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserRoleController;


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Authentication Routes
    |--------------------------------------------------------------------------
    */

    Route::post('/login', [
        AuthController::class,
        'login'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Protected Authentication Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [
            AuthController::class,
            'logout'
        ]);

        Route::get('/profile', [
            AuthController::class,
            'profile'
        ]);
    });
});


/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ]);

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
    Route::put('/members/{member}',[MemberController::class,'update'])->middleware('permission:Member.update,Member.edit');
    Route::patch('/members/{member}',[MemberController::class,'update'])->middleware('permission:Member.update,Member.edit');
    Route::delete('/members/{member}',[MemberController::class,'destroy'])->middleware('permission:Member.delete');
    Route::post('/members/{member}/suspend',[MemberController::class,'suspend'])->middleware('permission:Member.update,Member.edit');
    Route::post('/members/{member}/activate',[MemberController::class,'activate'])->middleware('permission:Member.update,Member.edit');
    Route::get('/members/{member}/roles',[MemberController::class,'roles'])
    ->middleware('permission:Role.view');
    Route::post('/members/{member}/roles',[MemberController::class,'assignRole'])
    ->middleware('permission:Role.update,Role.edit');
    Route::delete('/members/{member}/roles',[MemberController::class,'removeRole'])
    ->middleware('permission:Role.update,Role.edit');

    Route::post('/members/{member}/send-password-setup',[MemberController::class,'sendPasswordSetup'])->middleware('permission:Member.update');


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

    Route::apiResource('investments', InvestmentController::class);


    /*
    |--------------------------------------------------------------------------
    | Lands
    |--------------------------------------------------------------------------
    */

    Route::apiResource('lands', LandController::class);


    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    Route::apiResource('projects', ProjectController::class);


    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    */

    Route::get('/roles',[RoleController::class,'index'])
    ->middleware('permission:Role.view');

    Route::get('/roles/permissions',[RoleController::class,'permissions'])
        ->middleware('permission:Role.view');

    Route::post('/roles',[RoleController::class,'store'])
        ->middleware('permission:Role.create');

    Route::get('/roles/{role}',[RoleController::class,'show'])
        ->middleware('permission:Role.view');

    Route::put('/roles/{role}',[RoleController::class,'update'])
        ->middleware('permission:Role.update,Role.edit');

    Route::patch('/roles/{role}',[RoleController::class,'update'])
        ->middleware('permission:Role.update,Role.edit');

    Route::delete('/roles/{role}',[RoleController::class,'destroy'])
        ->middleware('permission:Role.delete');

    Route::put('/roles/{role}/permissions',[RoleController::class,'syncPermissions'])
        ->middleware('permission:Role.update,Role.edit');


    /*
    |--------------------------------------------------------------------------
    | Polls
    |--------------------------------------------------------------------------
    */

    Route::apiResource('polls', PollController::class);

    Route::post(
        '/polls/{poll}/vote',
        [PollController::class, 'vote']
    );

    Route::get(
        '/polls/{poll}/results',
        [PollController::class, 'results']
    );


    /*
    |--------------------------------------------------------------------------
    | Notices
    |--------------------------------------------------------------------------
    */

    Route::apiResource('notices', NoticeController::class);

    Route::patch(
        '/notices/{notice}/toggle-publish',
        [NoticeController::class, 'togglePublish']
    );


    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */

    Route::prefix('finance')->group(function () {

        Route::get('/accounts', [
            AccountingController::class,
            'accounts'
        ])->middleware('permission:Finance.view');

        Route::get('/accounts/{account}', [
            AccountingController::class,
            'account'
        ])->middleware('permission:Finance.view');

        Route::get('/cash-bank', [
            AccountingController::class,
            'cashBank'
        ])->middleware('permission:Finance.view');

        Route::get('/income-expense', [
            AccountingController::class,
            'incomeExpense'
        ])->middleware('permission:Finance.view');

        Route::get('/accounts/{account}/ledger', [
            AccountingController::class,
            'ledger'
        ])->middleware('permission:Finance.view');

        Route::get('/trial-balance', [
            AccountingController::class,
            'trialBalance'
        ])->middleware('permission:Finance.view');

        Route::get('/profit-loss', [
            AccountingController::class,
            'profitAndLoss'
        ])->middleware('permission:Finance.view');

        Route::get('/dashboard', [
            AccountingController::class,
            'dashboard'
        ])->middleware('permission:Finance.view');
    });


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->group(function () {

        Route::get('/', [
            NotificationController::class,
            'index'
        ]);

        Route::get('/unread', [
            NotificationController::class,
            'unread'
        ]);

        Route::get('/unread-count', [
            NotificationController::class,
            'unreadCount'
        ]);

        Route::patch('/{id}/read', [
            NotificationController::class,
            'markAsRead'
        ]);

        Route::patch('/read-all', [
            NotificationController::class,
            'markAllAsRead'
        ]);

        Route::delete('/{id}', [
            NotificationController::class,
            'destroy'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Mail Campaigns
    |--------------------------------------------------------------------------
    */

    Route::prefix('mail-campaigns')->group(function () {

        Route::get('/', [
            MailCampaignController::class,
            'index'
        ]);

        Route::post('/', [
            MailCampaignController::class,
            'store'
        ]);

        Route::get('/{mailCampaign}', [
            MailCampaignController::class,
            'show'
        ]);

        Route::delete('/{mailCampaign}', [
            MailCampaignController::class,
            'destroy'
        ]);

        Route::post('/{mailCampaign}/recipients', [
            MailCampaignController::class,
            'addRecipient'
        ]);

        Route::post('/{mailCampaign}/send', [
            MailCampaignController::class,
            'send'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::prefix('activity-logs')->group(function () {

        Route::get('/', [
            ActivityLogController::class,
            'index'
        ]);

        Route::get('/{activityLog}', [
            ActivityLogController::class,
            'show'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    Route::prefix('language')->group(function () {

        Route::get('/', [
            LanguageController::class,
            'current'
        ]);

        Route::post('/switch', [
            LanguageController::class,
            'switch'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::prefix('reports')->group(function () {

        Route::get('/', [
            ReportController::class,
            'index'
        ]);

        Route::get('/members', [
            ReportController::class,
            'members'
        ]);

        Route::get('/finance', [
            ReportController::class,
            'finance'
        ]);

        Route::get('/investments', [
            ReportController::class,
            'investments'
        ]);

        Route::get('/land', [
            ReportController::class,
            'land'
        ]);

        Route::get('/projects', [
            ReportController::class,
            'projects'
        ]);

        Route::get('/polls', [
            ReportController::class,
            'polls'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */

    Route::prefix('documents')->group(function () {

        Route::get('/', [
            DocumentController::class,
            'index'
        ]);

        Route::post('/', [
            DocumentController::class,
            'store'
        ]);

        Route::get('/{document}', [
            DocumentController::class,
            'show'
        ]);

        Route::put('/{document}', [
            DocumentController::class,
            'update'
        ]);

        Route::get('/{document}/download', [
            DocumentController::class,
            'download'
        ]);

        Route::delete('/{document}', [
            DocumentController::class,
            'destroy'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    // Route::prefix('settings')->group(function () {

    //     Route::get('/', [
    //         SettingController::class,
    //         'index'
    //     ]);

    //     Route::get('/public', [
    //         SettingController::class,
    //         'publicSettings'
    //     ]);

    //     Route::get('/{key}', [
    //         SettingController::class,
    //         'show'
    //     ]);

    //     Route::post('/', [
    //         SettingController::class,
    //         'store'
    //     ]);

    //     Route::delete('/{key}', [
    //         SettingController::class,
    //         'destroy'
    //     ]);
    // });

    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->middleware('permission:Setting.view');
        Route::get('/public', [SettingController::class, 'publicSettings']);
        Route::post('/upload', [SettingController::class, 'uploadImage'])->middleware('permission:Setting.update');
        Route::get('/{key}', [SettingController::class, 'show'])->middleware('permission:Setting.view');
        Route::post('/', [SettingController::class, 'store'])->middleware('permission:Setting.update');
        Route::delete('/{key}', [SettingController::class, 'destroy'])->middleware('permission:Setting.update');
    });


    /*
    |--------------------------------------------------------------------------
    | Approvals
    |--------------------------------------------------------------------------
    */
    Route::prefix('approvals')->group(function(){
        Route::get('/',[ApprovalController::class,'index'])->middleware('permission:Approval.view');
        Route::get('/statistics',[ApprovalController::class,'statistics'])->middleware('permission:Approval.view');
        Route::get('/{approval}',[ApprovalController::class,'show'])->middleware('permission:Approval.view');
        Route::post('/{approval}/approve',[ApprovalController::class,'approve'])->middleware('permission:Approval.approve');
        Route::post('/{approval}/reject',[ApprovalController::class,'reject'])->middleware('permission:Approval.reject');
        Route::post('/{approval}/cancel',[ApprovalController::class,'cancel'])->middleware('permission:Approval.update');
    });


    /*
    |--------------------------------------------------------------------------
    | Teller
    |--------------------------------------------------------------------------
    */

    Route::prefix('teller')->group(function () {

        Route::get('/dashboard', [
            TellerDashboardController::class,
            'index'
        ])->middleware('permission:Finance.view');

        Route::post('/receive', [
            TellerController::class,
            'receive'
        ])->middleware('permission:Finance.create');

        Route::post('/payment', [
            TellerController::class,
            'payment'
        ])->middleware('permission:Finance.create');

        Route::get('/balance', [
            TellerController::class,
            'balance'
        ])->middleware('permission:Finance.view');

        Route::get('/transactions', [
            TellerController::class,
            'transactions'
        ])->middleware('permission:Finance.view');

        Route::get('/closing/summary', [
            TellerClosingController::class,
            'summary'
        ])->middleware('permission:Finance.view');

        Route::post('/closing', [
            TellerClosingController::class,
            'close'
        ])->middleware('permission:Finance.create');

        Route::post('/closing/{date}/reopen', [
            TellerClosingController::class,
            'reopen'
        ])->middleware('permission:Finance.update');
    });


    /*
    |--------------------------------------------------------------------------
    | Teller Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('teller-management')->group(function () {

        Route::get('/', [
            TellerManagementController::class,
            'index'
        ])->middleware('permission:User.view');

        Route::get('/{user}', [
            TellerManagementController::class,
            'show'
        ])->middleware('permission:User.view');

        Route::get('/{user}/transactions', [
            TellerManagementController::class,
            'transactions'
        ])->middleware('permission:Finance.view');

        Route::get('/{user}/closings', [
            TellerManagementController::class,
            'closings'
        ])->middleware('permission:Finance.view');

        Route::patch('/{user}/toggle-status', [
            TellerManagementController::class,
            'toggleStatus'
        ])->middleware('permission:User.update');
    });


    /*
    |--------------------------------------------------------------------------
    | Member Dashboard
    |--------------------------------------------------------------------------
    */

    Route::prefix('member')->group(function () {

        Route::get('/dashboard', [
            MemberDashboardController::class,
            'index'
        ])->middleware('permission:Member.view');

        Route::get('/profile', [
            MemberDashboardController::class,
            'profile'
        ])->middleware('permission:Member.view');

        Route::put('/profile', [
            MemberDashboardController::class,
            'updateProfile'
        ])->middleware('permission:Member.update');

        Route::get('/investments', [
            MemberDashboardController::class,
            'investments'
        ])->middleware('permission:Investment.view');

        Route::get('/projects', [
            MemberDashboardController::class,
            'projects'
        ])->middleware('permission:Project.view');

        Route::get('/land-investments', [
            MemberDashboardController::class,
            'landInvestments'
        ])->middleware('permission:Land.view');

        Route::get('/polls', [
            MemberDashboardController::class,
            'polls'
        ])->middleware('permission:Poll.view');
    });
});
