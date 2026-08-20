<?php

namespace App\Console\Commands;

use App\Models\MemberSubscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ProcessMonthlySubscriptions extends Command
{
    protected $signature='subscriptions:process';
    protected $description='Generate monthly subscription dues and apply applicable late fines';

    public function handle(
        SubscriptionService $subscriptionService
    ): int{
        $generated=0;
        $skipped=0;
        $failed=0;

        MemberSubscription::query()
            ->where('is_active',true)
            ->with([
                'plan',
                'member'
            ])
            ->chunkById(100,function($subscriptions)use(
                $subscriptionService,
                &$generated,
                &$skipped,
                &$failed
            ){
                foreach($subscriptions as $subscription){
                    if(
                        !$subscription->member||
                        $subscription->member->status!=='active'
                    ){
                        $skipped++;
                        continue;
                    }

                    try{
                        $exists=$subscription->dues()
                            ->where('year',now()->year)
                            ->where('month',now()->month)
                            ->exists();

                        if($exists){
                            $skipped++;
                            continue;
                        }

                        $subscriptionService->generateDue(
                            $subscription,
                            now()->year,
                            now()->month
                        );

                        $generated++;
                    }catch(ValidationException $e){
                        $skipped++;
                    }catch(\Throwable $e){
                        $failed++;

                        report($e);

                        $this->error(
                            "Subscription {$subscription->id}: {$e->getMessage()}"
                        );
                    }
                }
            });

        try{
            $fines=$subscriptionService
                ->applyPendingLateFines();
        }catch(\Throwable $e){
            report($e);
            $fines=0;
            $failed++;

            $this->error(
                'Fine processing failed: '.$e->getMessage()
            );
        }

        $this->newLine();

        $this->table(
            ['Process','Count'],
            [
                ['Dues generated',$generated],
                ['Subscriptions skipped',$skipped],
                ['Late fines applied',$fines],
                ['Failed',$failed]
            ]
        );

        return $failed>0
            ?self::FAILURE
            :self::SUCCESS;
    }
}