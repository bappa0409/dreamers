<?php

namespace Tests\Unit\Models;

use Database\Factories\AccountFactory;
use Database\Factories\TransactionEntryFactory;
use Database\Factories\TransactionFactory;
use Tests\Support\FinanceTestCase;

class TransactionTest extends FinanceTestCase
{
    public function test_is_reversed_is_false_when_no_reversed_at(): void
    {
        $transaction = TransactionFactory::new()->create();

        $this->assertFalse($transaction->is_reversed);
    }

    public function test_is_reversed_is_true_once_reversed_at_is_set(): void
    {
        $transaction = TransactionFactory::new()->create([
            'reversed_at' => now(),
        ]);

        $this->assertTrue($transaction->is_reversed);
    }

    public function test_transaction_has_many_entries(): void
    {
        $transaction = TransactionFactory::new()->create();
        $account = AccountFactory::new()->cash()->create();

        TransactionEntryFactory::new()->debit(100)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        TransactionEntryFactory::new()->credit(100)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        $this->assertCount(2, $transaction->entries);
    }
}
