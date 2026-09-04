<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'transaction_no' => 'TXN-' . now()->format('Y') . '-' . fake()->unique()->numerify('######'),
            'transaction_date' => now()->toDateString(),
            'type' => 'manual',
            'status' => 'posted',
            'posted_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'posted_at' => null,
        ]);
    }
}
