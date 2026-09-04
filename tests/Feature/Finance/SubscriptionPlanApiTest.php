<?php

namespace Tests\Feature\Finance;

use Database\Factories\MemberSubscriptionFactory;
use Database\Factories\SubscriptionPlanFactory;
use Tests\Support\FinanceTestCase;

class SubscriptionPlanApiTest extends FinanceTestCase
{
    public function test_guest_cannot_list_subscription_plans(): void
    {
        $this->getJson('/api/finance/subscription-plans')
            ->assertStatus(401);
    }

    public function test_first_created_plan_becomes_default_automatically(): void
    {
        $this->actingAsAnalyst();

        $response = $this->postJson('/api/finance/subscription-plans', [
            'name' => 'Standard Plan',
            'amount' => 500,
            'due_day' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.is_default', true)
            ->assertJsonPath('data.is_active', true);
    }

    public function test_second_plan_is_not_default_unless_requested(): void
    {
        $this->actingAsAnalyst();

        SubscriptionPlanFactory::new()->default()->create(['name' => 'Existing Default']);

        $response = $this->postJson('/api/finance/subscription-plans', [
            'name' => 'Secondary Plan',
            'amount' => 300,
            'due_day' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.is_default', false);
    }

    public function test_a_default_plan_must_remain_active(): void
    {
        $this->actingAsAnalyst();

        $this->postJson('/api/finance/subscription-plans', [
            'name' => 'Broken Plan',
            'amount' => 300,
            'due_day' => 5,
            'is_default' => true,
            'is_active' => false,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('is_active');
    }

    public function test_percentage_fine_cannot_exceed_100_percent(): void
    {
        $this->actingAsAnalyst();

        $this->postJson('/api/finance/subscription-plans', [
            'name' => 'Fine Plan',
            'amount' => 300,
            'due_day' => 5,
            'fine_type' => 'percentage',
            'fine_value' => 150,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('fine_value');
    }

    public function test_fine_value_must_be_positive_when_a_fine_type_is_enabled(): void
    {
        $this->actingAsAnalyst();

        $this->postJson('/api/finance/subscription-plans', [
            'name' => 'Fine Plan Two',
            'amount' => 300,
            'due_day' => 5,
            'fine_type' => 'fixed',
            'fine_value' => 0,
        ])->assertStatus(422)
            ->assertJsonValidationErrors('fine_value');
    }

    public function test_setting_a_new_plan_as_default_unsets_the_previous_default(): void
    {
        $this->actingAsAnalyst();

        $first = SubscriptionPlanFactory::new()->default()->create();
        $second = SubscriptionPlanFactory::new()->create(['is_default' => false]);

        $this->putJson("/api/finance/subscription-plans/{$second->id}", [
            'is_default' => true,
        ])->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('subscription_plans', [
            'id' => $first->id,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('subscription_plans', [
            'id' => $second->id,
            'is_default' => true,
        ]);
    }

    public function test_default_plan_cannot_be_deleted(): void
    {
        $this->actingAsAnalyst();

        $plan = SubscriptionPlanFactory::new()->default()->create();

        $this->deleteJson("/api/finance/subscription-plans/{$plan->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('subscription_plans', ['id' => $plan->id]);
    }

    public function test_plan_already_assigned_to_a_member_cannot_be_deleted(): void
    {
        $this->actingAsAnalyst();

        $plan = SubscriptionPlanFactory::new()->create(['is_default' => false]);

        MemberSubscriptionFactory::new()->create([
            'subscription_plan_id' => $plan->id,
        ]);

        $this->deleteJson("/api/finance/subscription-plans/{$plan->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('subscription_plans', ['id' => $plan->id]);
    }

    public function test_an_unused_non_default_plan_can_be_deleted(): void
    {
        $this->actingAsAnalyst();

        $plan = SubscriptionPlanFactory::new()->create(['is_default' => false]);

        $this->deleteJson("/api/finance/subscription-plans/{$plan->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('subscription_plans', ['id' => $plan->id]);
    }
}
