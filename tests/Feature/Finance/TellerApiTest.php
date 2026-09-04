<?php

namespace Tests\Feature\Finance;

use Tests\Support\FinanceTestCase;

class TellerApiTest extends FinanceTestCase
{
    public function test_guest_cannot_access_teller_balance(): void
    {
        $this->getJson('/api/teller/balance')
            ->assertStatus(401);
    }

    public function test_teller_balance_is_zero_before_any_transactions(): void
    {
        $this->actingAsAnalyst();

        $this->getJson('/api/teller/balance')
            ->assertOk()
            ->assertJsonPath('data.balance', 0.0);
    }

    public function test_receiving_cash_increases_the_teller_balance(): void
    {
        $this->actingAsAnalyst();

        $cash = $this->account('cash', 'asset');
        // A receive counter account must be income/liability/equity.
        $income = $this->account('donation_income', 'income');

        $response = $this->postJson('/api/teller/receive', [
            'cash_account_id' => $cash->id,
            'counter_account_id' => $income->id,
            'amount' => 1000,
            'purpose' => 'Member donation',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'receive');

        $this->assertDatabaseHas('teller_transactions', [
            'cash_account_id' => $cash->id,
            'type' => 'receive',
            'amount' => 1000.00,
        ]);

        $this->getJson('/api/teller/balance')
            ->assertOk()
            ->assertJsonPath('data.balance', 1000.0);
    }

    public function test_paying_out_cash_decreases_the_teller_balance(): void
    {
        $this->actingAsAnalyst();

        $cash = $this->account('cash', 'asset');
        $income = $this->account('donation_income', 'income');
        $expense = $this->account('office_expense', 'expense');

        // Fund the till first so there is a positive balance to spend.
        $this->postJson('/api/teller/receive', [
            'cash_account_id' => $cash->id,
            'counter_account_id' => $income->id,
            'amount' => 1000,
        ])->assertCreated();

        $this->postJson('/api/teller/payment', [
            'cash_account_id' => $cash->id,
            'counter_account_id' => $expense->id,
            'amount' => 400,
            'purpose' => 'Office supplies',
        ])->assertCreated()
            ->assertJsonPath('data.type', 'payment');

        $this->getJson('/api/teller/balance')
            ->assertOk()
            ->assertJsonPath('data.balance', 600.0);
    }

    public function test_payment_is_rejected_when_it_would_overdraw_the_teller(): void
    {
        $this->actingAsAnalyst();

        $cash = $this->account('cash', 'asset');
        $expense = $this->account('office_expense', 'expense');

        $this->postJson('/api/teller/payment', [
            'cash_account_id' => $cash->id,
            'counter_account_id' => $expense->id,
            'amount' => 500,
        ])->assertStatus(422);
    }

    public function test_payment_counter_account_cannot_itself_be_a_cash_account(): void
    {
        $this->actingAsAnalyst();

        $cash = $this->account('cash', 'asset');
        $anotherCash = $this->account('cash', 'asset');

        $this->postJson('/api/teller/receive', [
            'cash_account_id' => $cash->id,
            'counter_account_id' => $this->account('donation_income', 'income')->id,
            'amount' => 1000,
        ])->assertCreated();

        $this->postJson('/api/teller/payment', [
            'cash_account_id' => $cash->id,
            'counter_account_id' => $anotherCash->id,
            'amount' => 100,
        ])->assertStatus(422);
    }
}