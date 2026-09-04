<?php

namespace Database\Factories;

use App\Models\MemberSubscription;
use App\Models\SubscriptionDue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionDue>
 */
class SubscriptionDueFactory extends Factory
{
    protected $model = SubscriptionDue::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 1000);

        return [
            'member_subscription_id' => MemberSubscription::factory(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'base_amount' => $amount,
            'share_count' => 1,
            'fine_amount' => 0,
            'amount' => $amount,
            'paid_amount' => 0,
            'due_date' => now()->toDateString(),
            'status' => 'unpaid',
        ];
    }

    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            $paid = round(($attributes['amount'] ?? 500) / 2, 2);

            return [
                'paid_amount' => $paid,
                'status' => 'partial',
            ];
        });
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['amount'] ?? 500,
            'status' => 'paid',
        ]);
    }
}
