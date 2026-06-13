<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branch = DB::table('branches')->updateOrInsert(
            ['name' => 'ITI Smart Village'],
            [
                'name'     => 'ITI Smart Village',
                'location' => 'Giza',
            ]
        );

        $branchId = DB::table('branches')->where('name', 'ITI Smart Village')->value('id');

        $this->command->info("✔ BranchSeeder — 'ITI Smart Village' (id: {$branchId}) ready.");
    }
}
