<?php

namespace Tests\Feature\Finance;

use Database\Factories\MemberFactory;
use Database\Factories\MemberSubscriptionFactory;
use Database\Factories\SubscriptionDueFactory;
use Database\Factories\SubscriptionPlanFactory;
use Tests\Support\FinanceTestCase;

class SubscriptionPaymentApiTest extends FinanceTestCase
{
    /**
     * Build a member with one active subscription and one unpaid due.
     */
    protected function memberWithOutstandingDue(float $amount = 500): array
    {
        $member = MemberFactory::new()->create();
        $plan = SubscriptionPlanFactory::new()->default()->create(['amount' => $amount]);

        $subscription = MemberSubscriptionFactory::new()->create([
            'member_id' => $member->id,
            'subscription_plan_id' => $plan->id,
        ]);

        $due = SubscriptionDueFactory::new()->create([
            'member_subscription_id' => $subscription->id,
            'amount' => $amount,
            'status' => 'unpaid',
        ]);

        return [$member, $subscription, $due];
    }

    public function test_guest_cannot_list_subscription_payments(): void
    {
        $this->getJson('/api/finance/subscription-payments')
            ->assertStatus(401);
    }

    public function test_outstanding_dues_endpoint_returns_unpaid_dues_for_a_member(): void
    {
        $this->actingAsAnalyst();

        [$member, , $due] = $this->memberWithOutstandingDue(500);

        $this->getJson("/api/finance/subscription-payments/outstanding-dues?member_id={$member->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $due->id);
    }

    public function test_admin_recorded_payment_is_created_and_auto_verified(): void
    {
        $this->actingAsAnalyst();
        $this->seedCoreAccounts();

        [$member, , $due] = $this->memberWithOutstandingDue(500);

        $response = $this->postJson('/api/finance/subscription-payments', [
            'member_id' => $member->id,
            'subscription_due_id' => $due->id,
            'amount' => 500,
            'payment_method' => 'cash',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'verified');

        $this->assertDatabaseHas('subscription_payments', [
            'member_id' => $member->id,
            'subscription_due_id' => $due->id,
            'status' => 'verified',
        ]);

        $this->assertDatabaseHas('subscription_dues', [
            'id' => $due->id,
            'status' => 'paid',
        ]);
    }

    public function test_payment_is_rejected_when_the_due_does_not_belong_to_the_member(): void
    {
        $this->actingAsAnalyst();
        $this->seedCoreAccounts();

        [, , $due] = $this->memberWithOutstandingDue(500);
        $otherMember = MemberFactory::new()->create();

        $this->postJson('/api/finance/subscription-payments', [
            'member_id' => $otherMember->id,
            'subscription_due_id' => $due->id,
            'amount' => 500,
            'payment_method' => 'cash',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('subscription_payments', [
            'member_id' => $otherMember->id,
        ]);
    }

    public function test_payment_amount_and_method_are_required(): void
    {
        $this->actingAsAnalyst();

        [$member, , $due] = $this->memberWithOutstandingDue(500);

        $this->postJson('/api/finance/subscription-payments', [
            'member_id' => $member->id,
            'subscription_due_id' => $due->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payment_method']);
    }

    public function test_can_view_a_single_subscription_payment(): void
    {
        $this->actingAsAnalyst();
        $this->seedCoreAccounts();

        [$member, , $due] = $this->memberWithOutstandingDue(500);

        $paymentId = $this->postJson('/api/finance/subscription-payments', [
            'member_id' => $member->id,
            'subscription_due_id' => $due->id,
            'amount' => 500,
            'payment_method' => 'cash',
        ])->json('data.id');

        $this->getJson("/api/finance/subscription-payments/{$paymentId}")
            ->assertOk()
            ->assertJsonPath('data.id', $paymentId);
    }
}
