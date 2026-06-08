<?php

namespace Database\Factories;

use App\Models\Engagement;
use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Session>
 */
class SessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'engagement_id' => Engagement::factory(),
            'date' => '2026-02-01',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'scheduled_hours' => 3,
            'is_delivered' => fake()->boolean(),
            'qr_code' => (string) Str::uuid(),
        ];
    }
}
