<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => 'Plan ' . fake()->unique()->word(),
            'amount' => fake()->randomFloat(2, 100, 1000),
            'due_day' => 10,
            'fine_type' => 'none',
            'fine_value' => 0,
            'grace_days' => 0,
            'is_default' => false,
            'is_active' => true,
            'description' => fake()->sentence(),
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
