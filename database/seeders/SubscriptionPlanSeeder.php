<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::query()
            ->where('is_default',true)
            ->update([
                'is_default'=>false
            ]);

        SubscriptionPlan::updateOrCreate(
            [
                'name'=>'Monthly Membership Subscription'
            ],
            [
                'amount'=>1000,
                'due_day'=>10,
                'fine_type'=>'none',
                'fine_value'=>0,
                'grace_days'=>1,
                'is_default'=>true,
                'is_active'=>true,
                'description'=>'Default monthly subscription plan for active association members.',
            ]
        );
    }
}