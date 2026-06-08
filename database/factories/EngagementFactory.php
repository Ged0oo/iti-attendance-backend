<?php

namespace Database\Factories;

use App\Models\Engagement;
use App\Models\User;
use Database\Factories\Concerns\BuildsParents;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Engagement>
 */
class EngagementFactory extends Factory
{
    use BuildsParents;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cohort_id' => $this->cohortId(),
            'course_id' => null,
            'instructor_id' => User::factory(),
            'type' => fake()->randomElement(Engagement::TYPES),
            'date_range_start' => '2026-02-01',
            'date_range_end' => '2026-02-10',
            'scheduled_hours' => fake()->numberBetween(4, 40),
            'status' => 'scheduled',
        ];
    }
}
