<?php

namespace Tests\Feature\Finance;

use Database\Factories\LoanFactory;
use Tests\Support\FinanceTestCase;

class LoanApiTest extends FinanceTestCase
{
    public function test_guest_cannot_list_loans(): void
    {
        $this->getJson('/api/loans')
            ->assertStatus(401);
    }

    public function test_can_list_loans(): void
    {
        $this->actingAsAnalyst();

        LoanFactory::new()->count(3)->create();

        $this->getJson('/api/loans')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_can_create_a_loan_request_for_an_active_member(): void
    {
        $this->actingAsAnalyst();
        $this->activeApprovalWorkflow('Loan', 'request');

        $member = $this->activeMember();

        $response = $this->postJson('/api/loans', [
            'member_id' => $member->id,
            'requested_amount' => 15000,
            'purpose' => 'Home repair',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.member_id', $member->id);

        $this->assertDatabaseHas('loans', [
            'member_id' => $member->id,
            'requested_amount' => 15000.00,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_create_a_loan_request_for_an_inactive_member(): void
    {
        $this->actingAsAnalyst();

        $member = $this->activeMember(['status' => 'inactive']);

        $this->postJson('/api/loans', [
            'member_id' => $member->id,
            'requested_amount' => 5000,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('member_id');
    }

    public function test_requested_amount_must_be_a_positive_number(): void
    {
        $this->actingAsAnalyst();

        $member = $this->activeMember();

        $this->postJson('/api/loans', [
            'member_id' => $member->id,
            'requested_amount' => 0,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('requested_amount');
    }

    public function test_can_view_a_single_loan(): void
    {
        $this->actingAsAnalyst();

        $loan = LoanFactory::new()->create();

        $this->getJson("/api/loans/{$loan->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $loan->id)
            ->assertJsonPath('data.loan_no', $loan->loan_no);
    }

    public function test_can_update_a_pending_loan_request(): void
    {
        $this->actingAsAnalyst();

        $loan = LoanFactory::new()->create(['status' => 'pending']);

        $this->putJson("/api/loans/{$loan->id}", [
            'purpose' => 'Updated purpose',
        ])->assertOk()
            ->assertJsonPath('data.purpose', 'Updated purpose');

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'purpose' => 'Updated purpose',
        ]);
    }

    public function test_cannot_update_a_loan_once_it_is_no_longer_pending(): void
    {
        $this->actingAsAnalyst();

        $loan = LoanFactory::new()->approved()->create();

        $this->putJson("/api/loans/{$loan->id}", [
            'purpose' => 'Should not apply',
        ])->assertStatus(422);
    }

    public function test_can_cancel_a_pending_loan(): void
    {
        $this->actingAsAnalyst();

        $loan = LoanFactory::new()->create(['status' => 'pending']);

        $this->postJson("/api/loans/{$loan->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_can_disburse_an_approved_loan_and_post_accounting_entries(): void
    {
        $this->actingAsAnalyst();
        $accounts = $this->seedCoreAccounts();

        $loan = LoanFactory::new()->approved()->create();

        $response = $this->postJson("/api/loans/{$loan->id}/disburse", [
            'disbursement_account_id' => $accounts['cash']->id,
            'disbursement_date' => now()->toDateString(),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'active',
        ]);

        $this->assertNotNull($loan->fresh()->finance_transaction_id);
    }

    public function test_can_repay_an_active_loan_in_full(): void
    {
        $this->actingAsAnalyst();
        $accounts = $this->seedCoreAccounts();

        $loan = LoanFactory::new()->active()->create([
            'approved_amount' => 10000,
            'interest_rate' => 10,
            'interest_amount' => 1000,
            'total_payable' => 11000,
        ]);

        $response = $this->postJson("/api/loans/{$loan->id}/repay", [
            'receive_account_id' => $accounts['cash']->id,
            'total_amount' => 11000,
            'repayment_date' => now()->toDateString(),
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('loan_repayments', [
            'loan_id' => $loan->id,
            'total_amount' => 11000.00,
        ]);
    }

    public function test_repay_rejects_an_amount_that_does_not_match_principal_plus_interest(): void
    {
        $this->actingAsAnalyst();
        $accounts = $this->seedCoreAccounts();

        $loan = LoanFactory::new()->active()->create([
            'approved_amount' => 10000,
            'interest_amount' => 1000,
            'total_payable' => 11000,
        ]);

        $this->postJson("/api/loans/{$loan->id}/repay", [
            'receive_account_id' => $accounts['cash']->id,
            'total_amount' => 5000,
            'repayment_date' => now()->toDateString(),
        ])->assertStatus(422);
    }
}
