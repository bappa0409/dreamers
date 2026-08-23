<?php

namespace App\Services;

use App\Models\Member;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MemberDashboardService
{
    private const PROFIT_LOSS_CACHE='finance:association:profit-loss';

    public function summary(Member $member): array
    {
        $shareEnabled=$this->shareEnabled();

        /*
        |--------------------------------------------------------------------------
        | Active subscription + plan
        |--------------------------------------------------------------------------
        */
        $activeSubscription=$member->subscriptions()
            ->select([
                'id',
                'member_id',
                'subscription_plan_id',
                'start_date',
                'end_date',
                'is_active',
            ])
            ->where('is_active',true)
            ->with([
                'plan:id,name,amount,due_day',
            ])
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Current month's due
        |--------------------------------------------------------------------------
        */
        $currentDue=SubscriptionDue::query()
            ->select([
                'id',
                'member_subscription_id',
                'amount',
                'paid_amount',
                'fine_amount',
                'status',
            ])
            ->whereHas(
                'subscription',
                fn($q)=>$q->where('member_id',$member->id)
            )
            ->where('year',now()->year)
            ->where('month',now()->month)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Total verified subscription deposits
        |--------------------------------------------------------------------------
        */
        $subscriptionDeposited=round(
            (float)SubscriptionPayment::query()
                ->where('member_id',$member->id)
                ->where('status','verified')
                ->sum('amount'),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | All due aggregate in one query
        |--------------------------------------------------------------------------
        */
        $dueSummary=SubscriptionDue::query()
            ->whereHas(
                'subscription',
                fn($q)=>$q->where('member_id',$member->id)
            )
            ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN status IN ('unpaid','partial','overdue')
                            THEN GREATEST(amount-paid_amount,0)
                            ELSE 0
                        END
                    ),
                    0
                ) AS outstanding,

                COALESCE(
                    SUM(
                        CASE
                            WHEN status IN ('unpaid','partial','overdue')
                            THEN fine_amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS fine
            ")
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Shares aggregate in ONE query
        |--------------------------------------------------------------------------
        |
        | Previously:
        | active sum
        | active count
        | pending count
        | active sum again
        |
        | Now all calculated once.
        |
        */
        $shareSummary=$shareEnabled
            ?DB::table('member_shares')
                ->where('member_id',$member->id)
                ->selectRaw("
                    SUM(
                        CASE
                            WHEN status='active'
                            THEN 1
                            ELSE 0
                        END
                    ) AS active_count,

                    SUM(
                        CASE
                            WHEN status='pending'
                            THEN 1
                            ELSE 0
                        END
                    ) AS pending_count,

                    COALESCE(
                        SUM(
                            CASE
                                WHEN status='active'
                                THEN purchase_amount
                                ELSE 0
                            END
                        ),
                        0
                    ) AS active_value
                ")
                ->first()
            :null;

        $activeShares=$shareEnabled
            ?(int)($shareSummary?->active_count??0)
            :0;

        $pendingShares=$shareEnabled
            ?(int)($shareSummary?->pending_count??0)
            :0;

        $activeShareValue=$shareEnabled
            ?round(
                (float)($shareSummary?->active_value??0),
                2
            )
            :0;

        /*
        |--------------------------------------------------------------------------
        | Monthly payable
        |--------------------------------------------------------------------------
        */
        $monthlyPayable=0;

        if($activeSubscription?->plan){
            $multiplier=$shareEnabled
                ?max($activeShares,1)
                :1;

            $monthlyPayable=round(
                (float)$activeSubscription->plan->amount*
                $multiplier,
                2
            );
        }

        $currentAmount=round(
            (float)($currentDue?->amount??0),
            2
        );

        $currentPaid=round(
            (float)($currentDue?->paid_amount??0),
            2
        );

        $currentOutstanding=max(
            round(
                $currentAmount-$currentPaid,
                2
            ),
            0
        );

        /*
        |--------------------------------------------------------------------------
        | Association-wide financial summary
        |--------------------------------------------------------------------------
        |
        | This value is identical for every member. Cache it briefly instead of
        | calculating the complete ledger aggregate on every dashboard request.
        |
        */
        $profitLoss=$this->associationProfitLoss();

        return[
            'total_deposited'=>round(
                $subscriptionDeposited+
                $activeShareValue,
                2
            ),

            'subscription_deposited'=>$subscriptionDeposited,

            'share_deposited'=>$activeShareValue,

            'current_due'=>$currentAmount,

            'current_paid'=>$currentPaid,

            'current_outstanding'=>$currentOutstanding,

            'current_fine'=>round(
                (float)($currentDue?->fine_amount??0),
                2
            ),

            'total_outstanding'=>round(
                (float)($dueSummary?->outstanding??0),
                2
            ),

            'total_fine'=>round(
                (float)($dueSummary?->fine??0),
                2
            ),

            'active_shares'=>$activeShares,

            'pending_shares'=>$pendingShares,

            'active_share_value'=>$activeShareValue,

            'monthly_payable'=>$monthlyPayable,

            'association_income'=>$profitLoss['income'],

            'association_expense'=>$profitLoss['expense'],

            'association_profit_loss'=>$profitLoss['profit_loss'],

            'association_profit_loss_type'=>
                $profitLoss['profit_loss']>=0
                    ?'profit'
                    :'loss',
        ];
    }

    public function forgetFinancialCache(): void
    {
        Cache::forget(
            self::PROFIT_LOSS_CACHE
        );
    }

    protected function associationProfitLoss(): array
    {
        return Cache::remember(
            self::PROFIT_LOSS_CACHE,
            now()->addMinutes(5),
            function(){
                $summary=DB::table(
                    'transaction_entries as te'
                )
                    ->join(
                        'transactions as t',
                        't.id',
                        '=',
                        'te.transaction_id'
                    )
                    ->join(
                        'accounts as a',
                        'a.id',
                        '=',
                        'te.account_id'
                    )
                    ->where(
                        't.status',
                        'posted'
                    )
                    ->whereIn(
                        'a.type',
                        [
                            'income',
                            'expense',
                        ]
                    )
                    ->selectRaw("
                        COALESCE(
                            SUM(
                                CASE
                                    WHEN a.type='income'
                                    THEN te.credit-te.debit
                                    ELSE 0
                                END
                            ),
                            0
                        ) AS income,

                        COALESCE(
                            SUM(
                                CASE
                                    WHEN a.type='expense'
                                    THEN te.debit-te.credit
                                    ELSE 0
                                END
                            ),
                            0
                        ) AS expense
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
        );
    }

    protected function shareEnabled(): bool
    {
        return filter_var(
            setting(
                'share_enabled',
                false
            ),
            FILTER_VALIDATE_BOOLEAN
        );
    }
}