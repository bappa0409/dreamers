<?php

namespace App\Console\Commands;

use App\Models\MemberSubscription;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class GenerateMonthlySubscriptionDues extends Command
{
    protected $signature='subscriptions:generate-dues
        {--year= : Subscription year}
        {--month= : Subscription month}';

    protected $description='Generate monthly subscription dues for all eligible active member subscriptions';

    public function handle(
        SubscriptionService $subscriptionService
    ): int{
        $year=(int)($this->option('year')?:now()->year);
        $month=(int)($this->option('month')?:now()->month);

        if($month<1||$month>12){
            $this->error('Month must be between 1 and 12.');
            return self::FAILURE;
        }

        if($year<2000||$year>2100){
            $this->error('Invalid year.');
            return self::FAILURE;
        }

        $period=Carbon::create($year,$month,1);

        $generated=0;
        $existing=0;
        $failed=0;

        $this->info(
            'Generating subscription dues for '.
            $period->format('F Y').'...'
        );

        MemberSubscription::query()
            ->where('is_active',true)
            ->whereDate(
                'start_date',
                '<=',
                $period->copy()->endOfMonth()->toDateString()
            )
            ->where(function($query)use($period){
                $query->whereNull('end_date')
                    ->orWhereDate(
                        'end_date',
                        '>=',
                        $period->copy()
                            ->startOfMonth()
                            ->toDateString()
                    );
            })
            ->whereHas('member',function($query){
                $query->where('status','active');
            })
            ->with([
                'plan',
                'member.user',
            ])
            ->orderBy('id')
            ->chunkById(
                100,
                function($subscriptions)use(
                    $subscriptionService,
                    $year,
                    $month,
                    &$generated,
                    &$existing,
                    &$failed
                ){
                    foreach($subscriptions as $subscription){
                        try{
                            $alreadyExists=$subscription
                                ->dues()
                                ->where('year',$year)
                                ->where('month',$month)
                                ->exists();

                            if($alreadyExists){
                                $existing++;
                                continue;
                            }

                            $subscriptionService->generateDue(
                                $subscription,
                                $year,
                                $month,
                                null
                            );

                            $generated++;
                        }catch(Throwable $e){
                            $failed++;

                            $memberCode=
                                $subscription->member?->member_code
                                ??'Unknown';

                            $this->error(
                                "{$memberCode}: {$e->getMessage()}"
                            );

                            report($e);
                        }
                    }
                }
            );

        $this->newLine();

        $this->table(
            ['Result','Count'],
            [
                ['Generated',$generated],
                ['Already Exists',$existing],
                ['Failed',$failed],
            ]
        );

        return $failed>0
            ?self::FAILURE
            :self::SUCCESS;
    }
}