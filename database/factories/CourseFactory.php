<?php

namespace Database\Factories;

use App\Models\Course;
use Database\Factories\Concerns\BuildsParents;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    use BuildsParents;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cohort_id' => $this->cohortId(),
            'name' => fake()->randomElement(['Laravel', 'Vue', 'PHP', 'Databases', 'Linux']),
            'description' => fake()->sentence(),
            'max_score' => 100,
        ];
    }
}
