<?php

namespace Tests\Unit;

use App\Services\BillingService;
use PHPUnit\Framework\TestCase;

class BillingServiceTest extends TestCase
{
    public function test_external_is_billed_purely_on_delivered_hours(): void
    {
        $svc = new BillingService();

        // 10 delivered hours * 50/hr = 500
        $this->assertSame(500.0, $svc->total(BillingService::TYPE_EXTERNAL, 10, 50));
    }

    public function test_internal_adds_fixed_salary_on_top_of_hours(): void
    {
        $svc = new BillingService();

        // 8000 salary + (10 * 50) = 8500
        $this->assertSame(8500.0, $svc->total(BillingService::TYPE_INTERNAL, 10, 50, 8000));
    }

    public function test_external_with_zero_delivered_hours_is_zero(): void
    {
        $svc = new BillingService();

        $this->assertSame(0.0, $svc->total(BillingService::TYPE_EXTERNAL, 0, 50, 9999));
    }

    public function test_internal_with_zero_hours_is_just_salary(): void
    {
        $svc = new BillingService();

        $this->assertSame(8000.0, $svc->total(BillingService::TYPE_INTERNAL, 0, 50, 8000));
    }
}
