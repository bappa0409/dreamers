<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Investment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Investment>
 */
class InvestmentFactory extends Factory
{
    protected $model = Investment::class;

    public function definition(): array
    {
        return [
            'investment_no' => 'INV-' . now()->format('Y') . '-' . fake()->unique()->numerify('######'),
            'payment_account_id' => Account::factory()->cash(),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 10000, 100000),
            'expected_return' => fake()->randomFloat(2, 1000, 10000),
            'investment_date' => now()->toDateString(),
            'status' => 'pending',
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
