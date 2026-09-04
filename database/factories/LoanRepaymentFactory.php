<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanRepayment>
 */
class LoanRepaymentFactory extends Factory
{
    protected $model = LoanRepayment::class;

    public function definition(): array
    {
        $principal = fake()->randomFloat(2, 500, 5000);
        $interest = fake()->randomFloat(2, 0, 500);

        return [
            'loan_id' => Loan::factory(),
            'receive_account_id' => Account::factory()->cash(),
            'principal_amount' => $principal,
            'interest_amount' => $interest,
            'penalty_amount' => 0,
            'total_amount' => round($principal + $interest, 2),
            'repayment_date' => now()->toDateString(),
        ];
    }
}
