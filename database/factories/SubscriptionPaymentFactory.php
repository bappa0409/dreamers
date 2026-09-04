<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPayment>
 */
class SubscriptionPaymentFactory extends Factory
{
    protected $model = SubscriptionPayment::class;

    public function definition(): array
    {
        return [
            'payment_no' => 'SP-' . now()->format('Y') . '-' . fake()->unique()->numerify('######'),
            'member_id' => Member::factory(),
            'subscription_due_id' => SubscriptionDue::factory(),
            'amount' => fake()->randomFloat(2, 100, 1000),
            'payment_method' => 'cash',
            'status' => 'pending',
            'paid_at' => now(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }
}
