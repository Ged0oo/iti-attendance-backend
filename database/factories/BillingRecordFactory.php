<?php

namespace Database\Factories;

use App\Models\BillingRecord;
use App\Models\User;
use Database\Factories\Concerns\BuildsParents;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingRecord>
 */
class BillingRecordFactory extends Factory
{
    use BuildsParents;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cohort_id' => $this->cohortId(),
            'user_id' => User::factory(),
            'compensation_type' => 'external',
            'scheduled_hours' => 10,
            'delivered_hours' => 8,
            'hourly_rate' => 50,
            'fixed_salary' => 0,
            'total_amount' => 400,
            'billing_period_start' => '2026-02-01',
            'billing_period_end' => '2026-02-28',
            'status' => 'draft',
        ];
    }
}
