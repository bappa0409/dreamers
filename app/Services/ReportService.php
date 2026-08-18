<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Investment;
use App\Models\Land;
use App\Models\Project;
use App\Models\Poll;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /*
    |--------------------------------------------------------------------------
    | Member Report
    |--------------------------------------------------------------------------
    */

    public function memberReport()
    {
        return [
            'total' => Member::count(),

            'active' => Member::where('status', 'active')->count(),

            'pending' => Member::where('status', 'pending')->count(),

            'inactive' => Member::where('status', 'inactive')->count(),

            'suspended' => Member::where('status', 'suspended')->count(),

            'rejected' => Member::where('status', 'rejected')->count(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Finance Report
    |--------------------------------------------------------------------------
    */

    public function financeReport()
    {
        $accounts = Account::where('is_active', true)
            ->get();

        $totalDebit = DB::table('transaction_entries')
            ->sum('debit');

        $totalCredit = DB::table('transaction_entries')
            ->sum('credit');

        return [
            'total_accounts' => Account::count(),

            'active_accounts' => Account::where(
                'is_active',
                true
            )->count(),

            'total_debit' => $totalDebit,

            'total_credit' => $totalCredit,

            'accounts' => $accounts,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Investment Report
    |--------------------------------------------------------------------------
    */

    public function investmentReport()
    {
        return [
            'total_investments' => Investment::count(),

            'pending' => Investment::where(
                'status',
                'pending'
            )->count(),

            'active' => Investment::where(
                'status',
                'active'
            )->count(),

            'completed' => Investment::where(
                'status',
                'completed'
            )->count(),

            'cancelled' => Investment::where(
                'status',
                'cancelled'
            )->count(),

            'total_amount' => Investment::sum('amount'),

            'total_expected_return' =>
                Investment::sum('expected_return'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Land Report
    |--------------------------------------------------------------------------
    */

    public function landReport()
    {
        return [
            'total_land' => Land::count(),

            'planned' => Land::where(
                'status',
                'planned'
            )->count(),

            'negotiating' => Land::where(
                'status',
                'negotiating'
            )->count(),

            'purchased' => Land::where(
                'status',
                'purchased'
            )->count(),

            'sold' => Land::where(
                'status',
                'sold'
            )->count(),

            'cancelled' => Land::where(
                'status',
                'cancelled'
            )->count(),

            'total_purchase_price' =>
                Land::sum('purchase_price'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Project Report
    |--------------------------------------------------------------------------
    */

    public function projectReport()
    {
        return [
            'total_projects' => Project::count(),

            'planned' => Project::where(
                'status',
                'planned'
            )->count(),

            'active' => Project::where(
                'status',
                'active'
            )->count(),

            'on_hold' => Project::where(
                'status',
                'on_hold'
            )->count(),

            'completed' => Project::where(
                'status',
                'completed'
            )->count(),

            'cancelled' => Project::where(
                'status',
                'cancelled'
            )->count(),

            'total_budget' =>
                Project::sum('budget'),

            'total_actual_cost' =>
                Project::sum('actual_cost'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Poll Report
    |--------------------------------------------------------------------------
    */

    public function pollReport()
    {
        $polls = Poll::with([
            'options.votes'
        ])->get();

        return [
            'total_polls' => Poll::count(),

            'active_polls' => Poll::where(
                'is_active',
                true
            )->count(),

            'polls' => $polls,
        ];
    }
}