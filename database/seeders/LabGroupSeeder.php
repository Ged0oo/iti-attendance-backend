<?php

namespace Database\Seeders;

use App\Models\LabGroup;
use Illuminate\Database\Seeder;

class LabGroupSeeder extends Seeder
{
    public function run(): void
    {
        LabGroup::factory()->count(3)->create();
    }
}
