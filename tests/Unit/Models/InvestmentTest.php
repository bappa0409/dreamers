<?php

namespace Tests\Unit\Models;

use Database\Factories\InvestmentFactory;
use Database\Factories\InvestmentReturnFactory;
use Tests\Support\FinanceTestCase;

class InvestmentTest extends FinanceTestCase
{
    public function test_income_received_only_counts_paid_income_returns(): void
    {
        $investment = InvestmentFactory::new()->active()->create([
            'amount' => 20000,
        ]);

        InvestmentReturnFactory::new()->create([
            'investment_id' => $investment->id,
            'return_type' => 'income',
            'status' => 'paid',
            'amount' => 1500,
        ]);

        // Pending return must not count yet.
        InvestmentReturnFactory::new()->pending()->create([
            'investment_id' => $investment->id,
            'return_type' => 'income',
            'amount' => 999,
        ]);

        // A principal return must not count as income.
        InvestmentReturnFactory::new()->principal()->create([
            'investment_id' => $investment->id,
            'status' => 'paid',
            'amount' => 5000,
        ]);

        $this->assertSame(1500.0, $investment->income_received);
    }

    public function test_remaining_principal_decreases_as_principal_is_returned(): void
    {
        $investment = InvestmentFactory::new()->active()->create([
            'amount' => 20000,
        ]);

        InvestmentReturnFactory::new()->principal()->create([
            'investment_id' => $investment->id,
            'status' => 'paid',
            'amount' => 8000,
        ]);

        $this->assertSame(12000.0, $investment->remaining_principal);
    }

    public function test_remaining_principal_never_goes_negative(): void
    {
        $investment = InvestmentFactory::new()->active()->create([
            'amount' => 5000,
        ]);

        InvestmentReturnFactory::new()->principal()->create([
            'investment_id' => $investment->id,
            'status' => 'paid',
            'amount' => 9000,
        ]);

        $this->assertSame(0.0, $investment->remaining_principal);
    }
}
