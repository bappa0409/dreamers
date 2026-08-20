<?php

namespace App\Console\Commands;

use App\Models\MemberSubscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class GenerateMonthlySubscriptions extends Command
{
    protected $signature='subscriptions:generate
        {--year=}
        {--month=}';

    protected $description='Generate monthly subscription dues for active members';

    public function handle(
        SubscriptionService $service
    ): int{
        $year=(int)($this->option('year')?:now()->year);
        $month=(int)($this->option('month')?:now()->month);

        if($month<1||$month>12){
            $this->error('Invalid month.');
            return self::FAILURE;
        }

        $created=0;
        $existing=0;
        $failed=0;

        MemberSubscription::query()
            ->where('is_active',true)
            ->with([
                'plan',
                'member.user'
            ])
            ->chunkById(200,function($subscriptions)use(
                $service,
                $year,
                $month,
                &$created,
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

                        $service->generateDue(
                            $subscription,
                            $year,
                            $month
                        );

                        $alreadyExists
                            ?$existing++
                            :$created++;

                    }catch(\Throwable $e){
                        report($e);
                        $failed++;
                    }
                }
            });

        $this->info(
            "Created: {$created}, Existing: {$existing}, Failed: {$failed}"
        );

        return $failed>0
            ?self::FAILURE
            :self::SUCCESS;
    }
}