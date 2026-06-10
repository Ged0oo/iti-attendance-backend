<?php

namespace Tests\Unit;

use App\Models\GradeComponent;
use App\Services\GradeNormalizationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GradeNormalizationServiceTest extends TestCase
{
    public function test_it_normalizes_raw_score_to_component_weight(): void
    {
        $component = new GradeComponent([
            'raw_max' => 70,
            'weight' => 40,
        ]);

        $service = new GradeNormalizationService();

        $this->assertSame(38.29, $service->calculate(67, $component));
    }

    public function test_it_rejects_score_above_raw_max(): void
    {
        $component = new GradeComponent([
            'raw_max' => 10,
            'weight' => 40,
        ]);

        $service = new GradeNormalizationService();

        $this->expectException(InvalidArgumentException::class);

        $service->calculate(11, $component);
    }
}
