<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Investment;
use App\Models\InvestmentReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestmentReturn>
 */
class InvestmentReturnFactory extends Factory
{
    protected $model = InvestmentReturn::class;

    public function definition(): array
    {
        return [
            'investment_id' => Investment::factory(),
            'return_type' => 'income',
            'receive_account_id' => Account::factory()->cash(),
            'amount' => fake()->randomFloat(2, 500, 5000),
            'return_date' => now()->toDateString(),
            'status' => 'paid',
        ];
    }

    public function principal(): static
    {
        return $this->state(fn () => ['return_type' => 'principal']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }
}
