<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Throwable;

class ApplySubscriptionLateFines extends Command
{
    protected $signature='subscriptions:apply-fines';

    protected $description='Apply configured late fines to overdue monthly subscription dues';

    public function handle(
        SubscriptionService $subscriptionService
    ): int{
        try{
            $count=$subscriptionService
                ->applyPendingLateFines();

            $this->info(
                "{$count} subscription fine(s) applied."
            );

            return self::SUCCESS;
        }catch(Throwable $e){
            $this->error($e->getMessage());

            report($e);

            return self::FAILURE;
        }
    }
}