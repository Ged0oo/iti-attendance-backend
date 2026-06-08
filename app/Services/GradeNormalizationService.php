<?php

namespace App\Services;

use App\Models\GradeComponent;
use InvalidArgumentException;

class GradeNormalizationService
{
    public function calculate(float $rawScore, GradeComponent $gradeComponent): float
    {
        $rawMax = (float) $gradeComponent->raw_max;
        $weight = (float) $gradeComponent->weight;

        if ($rawMax <= 0) {
            throw new InvalidArgumentException('Raw maximum must be greater than zero for normalization.');
        }

        if ($rawScore < 0) {
            throw new InvalidArgumentException('Raw score cannot be negative.');
        }

        if ($rawScore > $rawMax) {
            throw new InvalidArgumentException('Raw score cannot exceed the raw maximum defined for the grade component.');
        }

        if ($weight < 0 || $weight > 100) {
            throw new InvalidArgumentException('Weight must be between 0 and 100.');
        }

        return round(($rawScore / $rawMax) * $weight, 2);
    }
}
