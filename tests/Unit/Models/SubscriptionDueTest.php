<?php

namespace Tests\Unit\Models;

use Database\Factories\SubscriptionDueFactory;
use Tests\Support\FinanceTestCase;

class SubscriptionDueTest extends FinanceTestCase
{
    public function test_outstanding_is_amount_minus_paid_amount(): void
    {
        $due = SubscriptionDueFactory::new()->create([
            'amount' => 500,
            'paid_amount' => 150,
        ]);

        $this->assertSame(350.0, $due->outstanding);
    }

    public function test_outstanding_never_goes_negative(): void
    {
        $due = SubscriptionDueFactory::new()->create([
            'amount' => 500,
            'paid_amount' => 700,
        ]);

        $this->assertSame(0.0, $due->outstanding);
    }

    public function test_period_is_formatted_as_year_dash_month(): void
    {
        $due = SubscriptionDueFactory::new()->create([
            'year' => 2026,
            'month' => 3,
        ]);

        $this->assertSame('2026-03', $due->period);
    }

    public function test_is_settled_for_paid_and_waived_statuses_only(): void
    {
        $paid = SubscriptionDueFactory::new()->paid()->create();
        $waived = SubscriptionDueFactory::new()->create(['status' => 'waived']);
        $unpaid = SubscriptionDueFactory::new()->create(['status' => 'unpaid']);
        $partial = SubscriptionDueFactory::new()->partial()->create();

        $this->assertTrue($paid->is_settled);
        $this->assertTrue($waived->is_settled);
        $this->assertFalse($unpaid->is_settled);
        $this->assertFalse($partial->is_settled);
    }
}
