<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\GradeComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeComponent>
 */
class GradeComponentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => fake()->randomElement(['Labs', 'Final exam', 'Project', 'Quiz']),
            'type' => fake()->randomElement(GradeComponent::TYPES),
            'weight' => fake()->randomElement([20, 30, 40, 50]),
            'raw_max' => fake()->randomElement([10, 20, 70, 100]),
            'is_deliverable' => fake()->boolean(),
        ];
    }
}
