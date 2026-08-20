<?php

namespace App\Services;

use App\Models\Member;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;

class MemberDashboardService
{
    public function summary(Member $member): array
    {
        $member->loadMissing([
            'subscriptions.plan',
            'shares',
        ]);

        $currentDue=SubscriptionDue::query()
            ->whereHas('subscription',fn($q)=>$q->where('member_id',$member->id))
            ->where('year',now()->year)
            ->where('month',now()->month)
            ->first();

        $subscriptionDeposited=round(
            (float)SubscriptionPayment::query()
                ->where('member_id',$member->id)
                ->where('status','verified')
                ->sum('amount'),
            2
        );

        $shareDeposited=round(
            (float)$member->shares()
                ->where('status','active')
                ->sum('purchase_amount'),
            2
        );

        $totalDeposited=round(
            $subscriptionDeposited+$shareDeposited,
            2
        );

        $dueSummary=SubscriptionDue::query()
            ->whereHas('subscription',fn($q)=>$q->where('member_id',$member->id))
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status IN ('unpaid','partial','overdue') THEN amount-paid_amount ELSE 0 END),0) as outstanding,
                COALESCE(SUM(fine_amount),0) as fine
            ")
            ->first();

        $totalOutstanding=round(
            (float)($dueSummary?->outstanding??0),
            2
        );

        $totalFine=round(
            (float)($dueSummary?->fine??0),
            2
        );

        $shareEnabled=$this->shareEnabled();

        $activeShares=$shareEnabled
            ?$member->activeShares()->count()
            :0;

        $pendingShares=$shareEnabled
            ?$member->shares()->where('status','pending')->count()
            :0;

        $activeShareValue=$shareEnabled
            ?round(
                (float)$member->activeShares()->sum('purchase_amount'),
                2
            )
            :0;

        $activeSubscription=$member->subscriptions
            ->firstWhere('is_active',true);

        $monthlyPayable=0;

        if($activeSubscription?->plan){
            $multiplier=$shareEnabled
                ?max($activeShares,1)
                :1;

            $monthlyPayable=round(
                (float)$activeSubscription->plan->amount*$multiplier,
                2
            );
        }

        $profitLoss=$this->associationProfitLoss();

        return[
            'total_deposited'=>$totalDeposited,
            'subscription_deposited'=>$subscriptionDeposited,
            'share_deposited'=>$shareDeposited,

            'current_due'=>round(
                (float)($currentDue?->amount??0),
                2
            ),

            'current_paid'=>round(
                (float)($currentDue?->paid_amount??0),
                2
            ),

            'current_outstanding'=>$currentDue
                ?max(
                    round(
                        (float)$currentDue->amount-
                        (float)$currentDue->paid_amount,
                        2
                    ),
                    0
                )
                :0,

            'total_outstanding'=>$totalOutstanding,
            'total_fine'=>$totalFine,

            'current_fine'=>round(
                (float)($currentDue?->fine_amount??0),
                2
            ),

            'active_shares'=>$activeShares,
            'pending_shares'=>$pendingShares,
            'active_share_value'=>$activeShareValue,

            'monthly_payable'=>$monthlyPayable,

            'association_income'=>$profitLoss['income'],
            'association_expense'=>$profitLoss['expense'],
            'association_profit_loss'=>$profitLoss['profit_loss'],
            'association_profit_loss_type'=>$profitLoss['profit_loss']>=0
                ?'profit'
                :'loss',
        ];
    }

    protected function associationProfitLoss(): array
    {
        $summary=DB::table('transaction_entries as te')
            ->join('accounts as a','a.id','=','te.account_id')
            ->join('transactions as t','t.id','=','te.transaction_id')
            ->where('t.status','posted')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN a.type='income' THEN te.credit-te.debit ELSE 0 END),0) as income,
                COALESCE(SUM(CASE WHEN a.type='expense' THEN te.debit-te.credit ELSE 0 END),0) as expense
            ")
            ->first();

        $income=round(
            (float)($summary?->income??0),
            2
        );

        $expense=round(
            (float)($summary?->expense??0),
            2
        );

        return[
            'income'=>$income,
            'expense'=>$expense,
            'profit_loss'=>round(
                $income-$expense,
                2
            ),
        ];
    }

    protected function shareEnabled(): bool
    {
        return filter_var(
            setting('share_enabled',false),
            FILTER_VALIDATE_BOOLEAN
        );
    }
}