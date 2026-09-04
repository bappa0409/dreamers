<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberSubscription>
 */
class MemberSubscriptionFactory extends Factory
{
    protected $model = MemberSubscription::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'start_date' => now()->startOfMonth()->toDateString(),
            'is_active' => true,
        ];
    }
}
