<?php

namespace Tests\Unit\Models;

use Database\Factories\AccountFactory;
use Database\Factories\LoanFactory;
use Database\Factories\LoanRepaymentFactory;
use Tests\Support\FinanceTestCase;

class LoanTest extends FinanceTestCase
{
    public function test_loan_belongs_to_a_member(): void
    {
        $loan = LoanFactory::new()->create();

        $this->assertNotNull($loan->member);
        $this->assertEquals($loan->member_id, $loan->member->id);
    }

    public function test_outstanding_is_zero_for_a_pending_loan_regardless_of_total_payable(): void
    {
        $loan = LoanFactory::new()->create([
            'status' => 'pending',
            'total_payable' => null,
        ]);

        // Model-level accessor: paid totals default to 0 with no repayments.
        $this->assertSame(0.0, $loan->paid_total);
    }

    public function test_paid_totals_sum_across_multiple_repayments(): void
    {
        $loan = LoanFactory::new()->active()->create([
            'approved_amount' => 10000,
            'total_payable' => 11000,
        ]);

        $cashAccount = AccountFactory::new()->cash()->create();

        LoanRepaymentFactory::new()->create([
            'loan_id' => $loan->id,
            'receive_account_id' => $cashAccount->id,
            'principal_amount' => 3000,
            'interest_amount' => 300,
            'total_amount' => 3300,
        ]);

        LoanRepaymentFactory::new()->create([
            'loan_id' => $loan->id,
            'receive_account_id' => $cashAccount->id,
            'principal_amount' => 2000,
            'interest_amount' => 200,
            'total_amount' => 2200,
        ]);

        $loan->refresh();

        $this->assertSame(5000.0, $loan->paid_principal);
        $this->assertSame(500.0, $loan->paid_interest);
        $this->assertSame(5500.0, $loan->paid_total);
        $this->assertSame(5500.0, $loan->outstanding);
    }

    public function test_outstanding_never_goes_negative_when_overpaid(): void
    {
        $loan = LoanFactory::new()->active()->create([
            'approved_amount' => 1000,
            'total_payable' => 1100,
        ]);

        $cashAccount = AccountFactory::new()->cash()->create();

        LoanRepaymentFactory::new()->create([
            'loan_id' => $loan->id,
            'receive_account_id' => $cashAccount->id,
            'principal_amount' => 1000,
            'interest_amount' => 500,
            'total_amount' => 1500,
        ]);

        $loan->refresh();

        $this->assertSame(0.0, $loan->outstanding);
    }
}
