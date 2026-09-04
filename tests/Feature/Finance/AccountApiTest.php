<?php

namespace Tests\Feature\Finance;

use App\Models\Account;
use Database\Factories\AccountFactory;
use Database\Factories\TransactionEntryFactory;
use Database\Factories\TransactionFactory;
use Tests\Support\FinanceTestCase;

class AccountApiTest extends FinanceTestCase
{
    public function test_guest_cannot_list_accounts(): void
    {
        $this->getJson('/api/finance/accounts')
            ->assertStatus(401);
    }

    public function test_can_list_accounts(): void
    {
        $this->actingAsAnalyst();

        AccountFactory::new()->count(3)->create();

        $this->getJson('/api/finance/accounts')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_creating_an_account_requires_zero_opening_balance(): void
    {
        $this->actingAsAnalyst();

        $this->postJson('/api/finance/accounts', [
            'code' => 'ACC-100',
            'name' => 'Office Furniture',
            'type' => 'asset',
            'opening_balance' => 500,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['opening_balance']);
    }

    public function test_account_code_must_be_unique(): void
    {
        $this->actingAsAnalyst();

        AccountFactory::new()->create(['code' => 'ACC-DUP']);

        $this->postJson('/api/finance/accounts', [
            'code' => 'ACC-DUP',
            'name' => 'Duplicate Account',
            'type' => 'asset',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_account_type_must_be_a_known_value(): void
    {
        $this->actingAsAnalyst();

        $this->postJson('/api/finance/accounts', [
            'code' => 'ACC-101',
            'name' => 'Mystery Account',
            'type' => 'not_a_real_type',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_can_create_an_account_and_it_is_auto_approved_without_an_active_workflow(): void
    {
        $this->actingAsAnalyst();

        // No active ApprovalWorkflow exists for Account/create, so
        // ApprovalService::createRequest() auto-approves it immediately.
        $response = $this->postJson('/api/finance/accounts', [
            'code' => 'ACC-102',
            'name' => 'Petty Cash',
            'type' => 'asset',
            'sub_type' => 'cash',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'ACC-102')
            ->assertJsonPath('data.approval_status', 'approved')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('accounts', [
            'code' => 'ACC-102',
            'approval_status' => 'approved',
            'is_active' => true,
            'opening_balance' => 0,
        ]);
    }

    public function test_account_creation_is_held_pending_when_a_workflow_is_active(): void
    {
        $this->actingAsAnalyst();
        $this->activeApprovalWorkflow('Account', 'create');

        $this->postJson('/api/finance/accounts', [
            'code' => 'ACC-103',
            'name' => 'Reserve Fund',
            'type' => 'liability',
        ])->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'code' => 'ACC-103',
            'approval_status' => 'pending',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('approval_requests', [
            'module' => 'Account',
            'action' => 'create',
            'status' => 'pending',
        ]);
    }

    public function test_can_view_a_single_account_with_its_balance(): void
    {
        $this->actingAsAnalyst();

        $account = $this->account('cash', 'asset', ['opening_balance' => 1000]);
        $transaction = TransactionFactory::new()->create();

        TransactionEntryFactory::new()->debit(500)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        $this->getJson("/api/finance/accounts/{$account->id}")
            ->assertOk()
            ->assertJsonPath('data.total_debit', 500)
            ->assertJsonPath('data.balance', 1500)
            ->assertJsonPath('data.is_posting', true);
    }

    public function test_updating_an_account_is_auto_approved_without_an_active_workflow(): void
    {
        $this->actingAsAnalyst();

        $account = AccountFactory::new()->create([
            'name' => 'Old Name',
        ]);

        $response = $this->putJson("/api/finance/accounts/{$account->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'New Name',
        ]);
    }

    public function test_opening_balance_cannot_be_changed_through_an_update(): void
    {
        $this->actingAsAnalyst();

        $account = AccountFactory::new()->create(['opening_balance' => 0]);

        // The approval request is auto-approved (no active workflow), which
        // runs AccountService::update() for real, so the guard rejects it
        // inside the request/response cycle.
        $this->putJson("/api/finance/accounts/{$account->id}", [
            'opening_balance' => 5000,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['opening_balance']);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'opening_balance' => 0,
        ]);
    }

    public function test_core_structure_of_a_system_account_cannot_be_changed(): void
    {
        $this->actingAsAnalyst();

        $account = Account::create([
            'code' => 'SYS-001',
            'name' => 'System Cash',
            'type' => 'asset',
            'sub_type' => 'cash',
            'opening_balance' => 0,
            'is_system' => true,
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $this->putJson("/api/finance/accounts/{$account->id}", [
            'code' => 'SYS-002',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'code' => 'SYS-001',
        ]);
    }

    public function test_toggle_activates_and_deactivates_a_standalone_account(): void
    {
        $this->actingAsAnalyst();

        $account = AccountFactory::new()->create(['is_active' => true]);

        $this->patchJson("/api/finance/accounts/{$account->id}/toggle")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->patchJson("/api/finance/accounts/{$account->id}/toggle")
            ->assertOk()
            ->assertJsonPath('data.is_active', true);
    }

    public function test_child_account_cannot_be_activated_before_its_parent(): void
    {
        $this->actingAsAnalyst();

        $parent = AccountFactory::new()->create(['is_active' => false]);

        $child = AccountFactory::new()->create([
            'parent_id' => $parent->id,
            'type' => $parent->type,
            'is_active' => false,
        ]);

        $this->patchJson("/api/finance/accounts/{$child->id}/toggle")
            ->assertStatus(422);
    }

    public function test_parent_account_cannot_be_deactivated_while_a_child_is_active(): void
    {
        $this->actingAsAnalyst();

        $parent = AccountFactory::new()->create(['is_active' => true]);

        AccountFactory::new()->create([
            'parent_id' => $parent->id,
            'type' => $parent->type,
            'is_active' => true,
        ]);

        $this->patchJson("/api/finance/accounts/{$parent->id}/toggle")
            ->assertStatus(422);
    }

    public function test_system_account_cannot_be_toggled(): void
    {
        $this->actingAsAnalyst();

        $account = Account::create([
            'code' => 'SYS-010',
            'name' => 'System Bank',
            'type' => 'asset',
            'sub_type' => 'bank',
            'opening_balance' => 0,
            'is_system' => true,
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $this->patchJson("/api/finance/accounts/{$account->id}/toggle")
            ->assertStatus(422);
    }

    public function test_account_with_a_pending_approval_cannot_be_updated_again(): void
    {
        $this->actingAsAnalyst();
        $this->activeApprovalWorkflow('Account', 'update');

        $account = AccountFactory::new()->create();

        $this->putJson("/api/finance/accounts/{$account->id}", [
            'name' => 'First Edit',
        ])->assertOk();

        $this->putJson("/api/finance/accounts/{$account->id}", [
            'name' => 'Second Edit',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['account']);
    }

    public function test_deleting_an_account_with_no_history_is_auto_approved(): void
    {
        $this->actingAsAnalyst();

        $account = AccountFactory::new()->create();

        $this->deleteJson("/api/finance/accounts/{$account->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_account_with_posted_journal_entries_cannot_be_deleted(): void
    {
        $this->actingAsAnalyst();

        $account = AccountFactory::new()->create();
        $transaction = TransactionFactory::new()->create();

        TransactionEntryFactory::new()->debit(100)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        // Auto-approved delete request runs AccountService::delete() for
        // real, so the journal-history guard surfaces as a 422 here.
        $this->deleteJson("/api/finance/accounts/{$account->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['account']);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_account_summary_returns_counts(): void
    {
        $this->actingAsAnalyst();

        AccountFactory::new()->create(['is_active' => true]);
        AccountFactory::new()->create(['is_active' => false]);

        $this->getJson('/api/finance/accounts/summary')
            ->assertOk()
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.active', 1)
            ->assertJsonPath('data.inactive', 1);
    }

    public function test_account_options_lists_types_and_parents(): void
    {
        $this->actingAsAnalyst();

        AccountFactory::new()->create(['code' => 'ACC-200']);

        $this->getJson('/api/finance/accounts/options')
            ->assertOk()
            ->assertJsonPath('data.types', [
                'asset', 'liability', 'equity', 'income', 'expense',
            ]);
    }

    public function test_account_options_excludes_the_account_and_its_descendants(): void
    {
        $this->actingAsAnalyst();

        $parent = AccountFactory::new()->create(['is_active' => true]);
        $child = AccountFactory::new()->create([
            'parent_id' => $parent->id,
            'type' => $parent->type,
        ]);

        $response = $this->getJson("/api/finance/accounts/{$parent->id}/options")
            ->assertOk();

        $ids = collect($response->json('data.parents'))->pluck('id');

        $this->assertFalse($ids->contains($parent->id));
        $this->assertFalse($ids->contains($child->id));
    }

    public function test_creating_an_account_under_an_inactive_parent_is_rejected(): void
    {
        $this->actingAsAnalyst();

        $parent = AccountFactory::new()->create([
            'type' => 'asset',
            'is_active' => false,
        ]);

        $this->postJson('/api/finance/accounts', [
            'parent_id' => $parent->id,
            'code' => 'ACC-201',
            'name' => 'Child Under Inactive Parent',
            'type' => 'asset',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_creating_an_account_under_a_parent_of_a_different_type_is_rejected(): void
    {
        $this->actingAsAnalyst();

        $parent = AccountFactory::new()->create([
            'type' => 'asset',
            'is_active' => true,
        ]);

        $this->postJson('/api/finance/accounts', [
            'parent_id' => $parent->id,
            'code' => 'ACC-202',
            'name' => 'Mismatched Type Child',
            'type' => 'liability',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_an_account_with_posted_entries_cannot_be_turned_into_a_parent(): void
    {
        $this->actingAsAnalyst();

        $account = AccountFactory::new()->create([
            'type' => 'asset',
            'is_active' => true,
        ]);

        $transaction = TransactionFactory::new()->create();
        TransactionEntryFactory::new()->debit(100)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        $this->postJson('/api/finance/accounts', [
            'parent_id' => $account->id,
            'code' => 'ACC-203',
            'name' => 'New Child',
            'type' => 'asset',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_ledger_accounts_endpoint_only_lists_posting_leaf_accounts(): void
    {
        $this->actingAsAnalyst();

        $parent = AccountFactory::new()->create(['type' => 'asset']);
        $leaf = AccountFactory::new()->create([
            'parent_id' => $parent->id,
            'type' => $parent->type,
        ]);

        $response = $this->getJson('/api/finance/ledger/accounts')
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($leaf->id));
        $this->assertFalse($ids->contains($parent->id));
    }

    public function test_ledger_returns_opening_and_closing_balances_for_a_leaf_account(): void
    {
        $this->actingAsAnalyst();

        $account = $this->account('cash', 'asset', ['opening_balance' => 1000]);
        $transaction = TransactionFactory::new()->create();

        TransactionEntryFactory::new()->debit(200)->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
        ]);

        $this->getJson("/api/finance/accounts/{$account->id}/ledger")
            ->assertOk()
            ->assertJsonPath('data.summary.opening_balance', 1000)
            ->assertJsonPath('data.summary.period_debit', 200)
            ->assertJsonPath('data.summary.closing_balance', 1200);
    }

    public function test_ledger_cannot_be_viewed_for_a_parent_account(): void
    {
        $this->actingAsAnalyst();

        $parent = AccountFactory::new()->create(['type' => 'asset']);
        AccountFactory::new()->create([
            'parent_id' => $parent->id,
            'type' => $parent->type,
        ]);

        $this->getJson("/api/finance/accounts/{$parent->id}/ledger")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['account']);
    }

    public function test_trial_balance_returns_a_balanced_summary(): void
    {
        $this->actingAsAnalyst();

        $this->getJson('/api/finance/trial-balance')
            ->assertOk()
            ->assertJsonPath('data.summary.is_balanced', true);
    }

    public function test_balance_sheet_returns_totals(): void
    {
        $this->actingAsAnalyst();

        $this->getJson('/api/finance/balance-sheet')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'as_of',
                    'assets',
                    'liabilities',
                    'equity',
                    'summary' => [
                        'total_assets',
                        'total_liabilities',
                        'total_equity',
                        'is_balanced',
                    ],
                ],
            ]);
    }

    public function test_profit_loss_returns_income_and_expense_totals(): void
    {
        $this->actingAsAnalyst();

        $this->getJson('/api/finance/profit-loss')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'from',
                    'to',
                    'income',
                    'expenses',
                    'summary' => [
                        'total_income',
                        'total_expense',
                        'net_surplus',
                        'is_surplus',
                    ],
                ],
            ]);
    }

    public function test_finance_dashboard_returns_summary_data(): void
    {
        $this->actingAsAnalyst();

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary',
                    'monthly_trend',
                    'recent_transactions',
                ],
            ]);
    }
}