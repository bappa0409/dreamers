<?php

namespace Tests\Unit\Models;

use Database\Factories\AccountFactory;
use Database\Factories\TransactionEntryFactory;
use Database\Factories\TransactionFactory;
use Tests\Support\FinanceTestCase;

class AccountTest extends FinanceTestCase
{
    public function test_asset_account_balance_increases_with_debit_and_decreases_with_credit(): void
    {
        $account = AccountFactory::new()->cash()->create(['opening_balance' => 1000]);

        $transaction = TransactionFactory::new()->create(); // posted by default

        TransactionEntryFactory::new()->debit(500)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        TransactionEntryFactory::new()->credit(200)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        // Asset: opening + debit - credit = 1000 + 500 - 200
        $this->assertSame(1300.0, $account->fresh()->current_balance);
    }

    public function test_liability_account_balance_increases_with_credit_and_decreases_with_debit(): void
    {
        $account = AccountFactory::new()->withSubType('member_savings', 'liability')
            ->create(['opening_balance' => 1000]);

        $transaction = TransactionFactory::new()->create();

        TransactionEntryFactory::new()->credit(500)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        TransactionEntryFactory::new()->debit(200)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        // Liability: opening + credit - debit = 1000 + 500 - 200
        $this->assertSame(1300.0, $account->fresh()->current_balance);
    }

    public function test_draft_transaction_entries_are_excluded_from_balance(): void
    {
        $account = AccountFactory::new()->cash()->create(['opening_balance' => 0]);

        $draft = TransactionFactory::new()->draft()->create();

        TransactionEntryFactory::new()->debit(9999)->create([
            'transaction_id' => $draft->id,
            'account_id' => $account->id,
        ]);

        $this->assertSame(0.0, $account->fresh()->current_balance);
    }
}
