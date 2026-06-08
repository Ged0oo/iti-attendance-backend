<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\LabGroup;
use App\Models\User;
use Database\Factories\Concerns\BuildsParents;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabGroup>
 */
class LabGroupFactory extends Factory
{
    use BuildsParents;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cohort_id' => $this->cohortId(),
            'course_id' => Course::factory(),
            'instructor_id' => User::factory(),
            'name' => 'Group '.fake()->randomElement(['A', 'B', 'C']),
        ];
    }
}
