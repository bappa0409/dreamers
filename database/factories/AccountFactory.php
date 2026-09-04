<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'code' => 'ACC-' . fake()->unique()->numerify('#####'),
            'name' => fake()->words(2, true),
            'type' => 'asset',
            'sub_type' => 'cash',
            'opening_balance' => 0,
            'is_system' => false,
            'is_active' => true,
            'approval_status' => 'approved',
        ];
    }

    public function cash(): static
    {
        return $this->state(fn () => [
            'type' => 'asset',
            'sub_type' => 'cash',
            'name' => 'Cash in Hand',
        ]);
    }

    public function bank(): static
    {
        return $this->state(fn () => [
            'type' => 'asset',
            'sub_type' => 'bank',
            'name' => 'Bank Account',
        ]);
    }

    /**
     * A named posting account, e.g. AccountingService::account('investment')
     * looks accounts up by sub_type — pass the exact sub_type string
     * a service expects (investment, member_loan_receivable,
     * loan_interest_income, receivable, subscription_income, fine_income...).
     */
    public function withSubType(string $subType, string $type = 'asset'): static
    {
        return $this->state(fn () => [
            'type' => $type,
            'sub_type' => $subType,
            'name' => ucwords(str_replace('_', ' ', $subType)),
        ]);
    }
}
