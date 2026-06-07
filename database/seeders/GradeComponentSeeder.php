<?php

namespace Database\Seeders;

use App\Models\GradeComponent;
use Illuminate\Database\Seeder;

class GradeComponentSeeder extends Seeder
{
    public function run(): void
    {
        GradeComponent::factory()->count(3)->create();
    }
}
