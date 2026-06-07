<?php

namespace App\Services;

/**
 * Works out what each instructor gets paid for a billing period.
 *
 * External instructors are paid only for the hours they actually delivered.
 * Internal staff get their fixed salary plus those delivered hours on top.
 */
class BillingService
{
    public const TYPE_EXTERNAL = 'external';
    public const TYPE_INTERNAL = 'internal';

    /**
     * Total payable amount for one instructor in a billing period.
     */
    public function total(
        string $compensationType,
        float $deliveredHours,
        float $hourlyRate,
        float $fixedSalary = 0.0
    ): float {
        $hourly = $deliveredHours * $hourlyRate;

        return $compensationType === self::TYPE_INTERNAL
            ? $fixedSalary + $hourly
            : $hourly;
    }
}
