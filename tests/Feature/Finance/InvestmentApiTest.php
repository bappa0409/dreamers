<?php

namespace Tests\Feature\Finance;

use Database\Factories\InvestmentFactory;
use Tests\Support\FinanceTestCase;

class InvestmentApiTest extends FinanceTestCase
{
    public function test_guest_cannot_list_investments(): void
    {
        $this->getJson('/api/investments')
            ->assertStatus(401);
    }

    public function test_can_list_investments(): void
    {
        $this->actingAsAnalyst();

        InvestmentFactory::new()->count(2)->create();

        $this->getJson('/api/investments')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_creating_an_active_investment_posts_accounting_entries(): void
    {
        $this->actingAsAnalyst();
        $accounts = $this->seedCoreAccounts();

        $response = $this->postJson('/api/investments', [
            'payment_account_id' => $accounts['cash']->id,
            'title' => 'Fixed deposit',
            'amount' => 50000,
            'investment_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'active');

        $investmentId = $response->json('data.id');

        $this->assertNotNull(
            \App\Models\Investment::find($investmentId)->finance_transaction_id
        );
    }

    public function test_creating_a_pending_investment_does_not_post_accounting_entries(): void
    {
        $this->actingAsAnalyst();
        $accounts = $this->seedCoreAccounts();

        $response = $this->postJson('/api/investments', [
            'payment_account_id' => $accounts['cash']->id,
            'title' => 'Land purchase (pending approval)',
            'amount' => 30000,
            'investment_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response->assertCreated();

        $investmentId = $response->json('data.id');

        $this->assertNull(
            \App\Models\Investment::find($investmentId)->finance_transaction_id
        );
    }

    public function test_payment_account_must_be_an_active_cash_or_bank_account(): void
    {
        $this->actingAsAnalyst();
        $incomeAccount = $this->account('donation_income', 'income');

        $this->postJson('/api/investments', [
            'payment_account_id' => $incomeAccount->id,
            'title' => 'Invalid account test',
            'amount' => 1000,
            'investment_date' => now()->toDateString(),
        ])->assertStatus(422);
    }

    public function test_can_view_a_single_investment(): void
    {
        $this->actingAsAnalyst();

        $investment = InvestmentFactory::new()->create();

        $this->getJson("/api/investments/{$investment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $investment->id);
    }

    public function test_can_update_a_pending_investment(): void
    {
        $this->actingAsAnalyst();

        $investment = InvestmentFactory::new()->create(['status' => 'pending']);

        $this->putJson("/api/investments/{$investment->id}", [
            'title' => 'Renamed investment',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Renamed investment');
    }

    public function test_cannot_cancel_an_investment_that_already_has_a_paid_return(): void
    {
        $this->actingAsAnalyst();
        $accounts = $this->seedCoreAccounts();

        $investment = InvestmentFactory::new()->active()->create([
            'payment_account_id' => $accounts['cash']->id,
        ]);

        \Database\Factories\InvestmentReturnFactory::new()->create([
            'investment_id' => $investment->id,
            'status' => 'paid',
        ]);

        $this->postJson("/api/investments/{$investment->id}/cancel")
            ->assertStatus(422);
    }

    public function test_can_delete_an_investment_with_no_returns_and_no_posted_transaction(): void
    {
        $this->actingAsAnalyst();

        $investment = InvestmentFactory::new()->create(['status' => 'pending']);

        $this->deleteJson("/api/investments/{$investment->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('investments', ['id' => $investment->id]);
    }

    public function test_cannot_delete_an_investment_that_has_return_history(): void
    {
        $this->actingAsAnalyst();

        $investment = InvestmentFactory::new()->create(['status' => 'active']);

        \Database\Factories\InvestmentReturnFactory::new()->pending()->create([
            'investment_id' => $investment->id,
        ]);

        $this->deleteJson("/api/investments/{$investment->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('investments', ['id' => $investment->id]);
    }

    public function test_can_add_a_paid_income_return_to_an_active_investment(): void
    {
        $this->actingAsAnalyst();
        $accounts = $this->seedCoreAccounts();

        $investment = InvestmentFactory::new()->active()->create([
            'payment_account_id' => $accounts['cash']->id,
        ]);

        $response = $this->postJson("/api/investments/{$investment->id}/returns", [
            'return_type' => 'income',
            'receive_account_id' => $accounts['cash']->id,
            'amount' => 2000,
            'return_date' => now()->toDateString(),
            'status' => 'paid',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.return_type', 'income')
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('investment_returns', [
            'investment_id' => $investment->id,
            'amount' => 2000.00,
        ]);
    }
}
