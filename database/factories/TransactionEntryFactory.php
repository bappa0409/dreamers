<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionEntry>
 */
class TransactionEntryFactory extends Factory
{
    protected $model = TransactionEntry::class;

    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'account_id' => Account::factory(),
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function debit(float $amount): static
    {
        return $this->state(fn () => ['debit' => $amount, 'credit' => 0]);
    }

    public function credit(float $amount): static
    {
        return $this->state(fn () => ['debit' => 0, 'credit' => $amount]);
    }
}
