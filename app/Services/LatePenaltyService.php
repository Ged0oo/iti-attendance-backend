<?php

namespace App\Services;

use InvalidArgumentException;

class LatePenaltyService
{
    public function calculate(int $daysLate): float
    {
        if ($daysLate <= 0) {
            return 0.0;
        }

        return min($daysLate * 25, 100.0);
    }

    public function applyPenalty(float $rawScore, int $daysLate): float
    {
        if ($rawScore < 0) {
            throw new InvalidArgumentException('Raw score cannot be negative.');
        }

        $latePenalty = $this->calculate($daysLate);

        return round($rawScore * ((100 - $latePenalty) / 100), 2);
    }
}
