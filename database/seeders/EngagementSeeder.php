<?php

namespace Database\Seeders;

use App\Models\Engagement;
use Illuminate\Database\Seeder;

class EngagementSeeder extends Seeder
{
    public function run(): void
    {
        Engagement::factory()->count(3)->create();
    }
}
