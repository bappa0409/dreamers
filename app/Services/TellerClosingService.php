<?php

namespace App\Services;

use App\Models\TellerClosing;
use App\Models\TellerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TellerClosingService
{
    public function getTodaySummary(
        int $tellerId
    ): array{
        return $this->summary(
            $tellerId,
            now()->toDateString()
        );
    }

    public function summary(
        int $tellerId,
        string $date
    ): array{
        $previousClosing=TellerClosing::query()
            ->where(
                'teller_id',
                $tellerId
            )
            ->where(
                'status',
                'closed'
            )
            ->whereDate(
                'closing_date',
                '<',
                $date
            )
            ->latest(
                'closing_date'
            )
            ->first();

        $openingBalance=$previousClosing
            ?(float)$previousClosing->actual_balance
            :0;

        $received=(float)TellerTransaction::query()
            ->where(
                'teller_id',
                $tellerId
            )
            ->whereDate(
                'transaction_date',
                $date
            )
            ->where(
                'type',
                'receive'
            )
            ->where(
                'status',
                'completed'
            )
            ->sum('amount');

        $paid=(float)TellerTransaction::query()
            ->where(
                'teller_id',
                $tellerId
            )
            ->whereDate(
                'transaction_date',
                $date
            )
            ->where(
                'type',
                'payment'
            )
            ->where(
                'status',
                'completed'
            )
            ->sum('amount');

        return[
            'date'=>$date,
            'opening_balance'=>$openingBalance,
            'total_received'=>$received,
            'total_paid'=>$paid,
            'expected_balance'=>
                $openingBalance+
                $received-
                $paid
        ];
    }

    public function close(
        int $tellerId,
        float $actualBalance,
        ?string $notes=null
    ): TellerClosing{
        return DB::transaction(function()use(
            $tellerId,
            $actualBalance,
            $notes
        ){
            $date=now()->toDateString();

            $existing=TellerClosing::query()
                ->where(
                    'teller_id',
                    $tellerId
                )
                ->whereDate(
                    'closing_date',
                    $date
                )
                ->lockForUpdate()
                ->first();

            if(
                $existing&&
                $existing->status==='closed'
            ){
                throw ValidationException::withMessages([
                    'closing'=>[
                        'Teller is already closed for today.'
                    ]
                ]);
            }

            $summary=$this->summary(
                $tellerId,
                $date
            );

            $difference=round(
                $actualBalance-
                $summary['expected_balance'],
                2
            );

            $closing=TellerClosing::updateOrCreate(
                [
                    'teller_id'=>$tellerId,
                    'closing_date'=>$date
                ],
                [
                    'opening_balance'=>
                        $summary['opening_balance'],

                    'total_received'=>
                        $summary['total_received'],

                    'total_paid'=>
                        $summary['total_paid'],

                    'expected_balance'=>
                        $summary['expected_balance'],

                    'actual_balance'=>$actualBalance,

                    'difference'=>$difference,

                    'status'=>'closed',

                    'notes'=>$notes,

                    'closed_at'=>now(),

                    'closed_by'=>$tellerId
                ]
            );

            return $closing->fresh([
                'teller',
                'closedBy'
            ]);
        });
    }

    public function reopen(
        int $tellerId,
        string $date
    ): TellerClosing{
        return DB::transaction(function()use(
            $tellerId,
            $date
        ){
            $closing=TellerClosing::query()
                ->where(
                    'teller_id',
                    $tellerId
                )
                ->whereDate(
                    'closing_date',
                    $date
                )
                ->lockForUpdate()
                ->first();

            if(!$closing){
                throw ValidationException::withMessages([
                    'closing'=>[
                        'Closing record not found.'
                    ]
                ]);
            }

            if($closing->status!=='closed'){
                throw ValidationException::withMessages([
                    'closing'=>[
                        'Only a closed teller day can be reopened.'
                    ]
                ]);
            }

            $closing->update([
                'status'=>'reopened',
                'closed_at'=>null
            ]);

            return $closing->fresh([
                'teller',
                'closedBy'
            ]);
        });
    }
}