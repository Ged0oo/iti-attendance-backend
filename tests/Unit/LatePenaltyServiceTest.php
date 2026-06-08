<?php

namespace Tests\Unit;

use App\Services\LatePenaltyService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LatePenaltyServiceTest extends TestCase
{
    public function test_it_calculates_penalty_percent(): void
    {
        $service = new LatePenaltyService();

        $this->assertSame(0.0, $service->calculate(0));
        $this->assertSame(25.0, $service->calculate(1));
        $this->assertSame(50.0, $service->calculate(2));
        $this->assertSame(75.0, $service->calculate(3));
        $this->assertSame(100.0, $service->calculate(4));
        $this->assertSame(100.0, $service->calculate(5));
    }

    public function test_it_applies_penalty_to_raw_score(): void
    {
        $service = new LatePenaltyService();

        $this->assertSame(10.0, $service->applyPenalty(10, 0));
        $this->assertSame(7.5, $service->applyPenalty(10, 1));
        $this->assertSame(5.0, $service->applyPenalty(10, 2));
        $this->assertSame(2.5, $service->applyPenalty(10, 3));
        $this->assertSame(0.0, $service->applyPenalty(10, 4));
    }

    public function test_it_rejects_negative_raw_score(): void
    {
        $service = new LatePenaltyService();

        $this->expectException(InvalidArgumentException::class);

        $service->applyPenalty(-1, 1);
    }
}
