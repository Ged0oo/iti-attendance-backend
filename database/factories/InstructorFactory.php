<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instructor>
 */
class InstructorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'compensation_type' => fake()->randomElement(Instructor::TYPES),
            'hourly_rate' => fake()->numberBetween(30, 100),
            'fixed_salary' => fake()->randomElement([0, 8000, 12000]),
        ];
    }
}
