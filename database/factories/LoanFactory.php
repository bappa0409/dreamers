<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $requested = fake()->randomFloat(2, 5000, 50000);

        return [
            'loan_no' => 'LN-' . now()->format('Y') . '-' . fake()->unique()->numerify('######'),
            'member_id' => Member::factory(),
            'requested_amount' => $requested,
            'request_date' => now()->toDateString(),
            'status' => 'pending',
            'purpose' => fake()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            $approved = $attributes['requested_amount'] ?? 10000;
            $interestRate = 10;
            $interest = round($approved * ($interestRate / 100), 2);

            return [
                'status' => 'approved',
                'approved_amount' => $approved,
                'interest_rate' => $interestRate,
                'interest_amount' => $interest,
                'total_payable' => round($approved + $interest, 2),
                'duration_months' => 12,
                'approved_at' => now(),
            ];
        });
    }

    public function active(): static
    {
        return $this->approved()->state(fn () => [
            'status' => 'active',
            'disbursement_date' => now()->toDateString(),
            'maturity_date' => now()->addMonthsNoOverflow(12)->toDateString(),
        ]);
    }
}
