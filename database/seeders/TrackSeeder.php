<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = DB::table('branches')->first()?->id;

        if (!$branchId) {
            $branchId = DB::table('branches')->insertGetId([
                'name' => 'Smart Village',
                'location' => 'Cairo',
            ]);
        }

        $tracks = [
            ['name' => 'Web Development', 'description' => 'Full-stack web development track'],
            ['name' => 'Mobile Development', 'description' => 'iOS and Android development track'],
            ['name' => 'AI & Data Science', 'description' => 'Machine learning and data analysis track'],
        ];

        foreach ($tracks as $track) {
            $exists = DB::table('tracks')->where('name', $track['name'])->exists();
            if (!$exists) {
                DB::table('tracks')->insert([
                    'branch_id' => $branchId,
                    'name' => $track['name'],
                    'description' => $track['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Tracks seeded.');
    }
}
