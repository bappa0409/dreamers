<?php

namespace Tests\Unit\Models;

use Database\Factories\SubscriptionPaymentFactory;
use Tests\Support\FinanceTestCase;

class SubscriptionPaymentTest extends FinanceTestCase
{
    public function test_is_pending_reflects_pending_status(): void
    {
        $payment = SubscriptionPaymentFactory::new()->create(['status' => 'pending']);

        $this->assertTrue($payment->is_pending);
        $this->assertFalse($payment->is_verified);
    }

    public function test_is_verified_reflects_verified_status(): void
    {
        $payment = SubscriptionPaymentFactory::new()->verified()->create();

        $this->assertTrue($payment->is_verified);
        $this->assertFalse($payment->is_pending);
    }

    public function test_payment_belongs_to_a_member_and_due(): void
    {
        $payment = SubscriptionPaymentFactory::new()->create();

        $this->assertNotNull($payment->member);
        $this->assertNotNull($payment->due);
    }
}
