<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CohortSeeder extends Seeder
{
    public function run(): void
    {
        $bmId = DB::table('users')->where('role', 'branch_manager')->value('id');
        $tracks = DB::table('tracks')->pluck('id', 'name');

        if ($tracks->isEmpty()) {
            $this->command->error('CohortSeeder: No tracks found. Run TrackSeeder first.');
            return;
        }

        // Intake 46 → 2025-10-01 to 2026-06-30
        // Each earlier intake: subtract 1 year per step back from intake 46
        // Intake 46 = index 0 (step 0), Intake 45 = index 1 (step 1) ... Intake 15 = index 31 (step 31)

        $baseStart = '2025-10-01';
        $baseEnd   = '2026-06-30';

        $created = 0;

        foreach ($tracks as $trackName => $trackId) {
            for ($intakeNumber = 15; $intakeNumber <= 46; $intakeNumber++) {
                $stepsBack = 46 - $intakeNumber;

                $startDate = date('Y-m-d', strtotime("{$baseStart} -{$stepsBack} years"));
                $endDate   = date('Y-m-d', strtotime("{$baseEnd} -{$stepsBack} years"));

                // Status logic
                if ($intakeNumber <= 45) {
                    $status = 'rolled_up';
                } else {
                    $status = 'delivering'; // Intake 46
                }

                $name = "Intake {$intakeNumber}";

                $exists = DB::table('cohorts')
                    ->where('track_id', $trackId)
                    ->where('name', $name)
                    ->exists();

                if (!$exists) {
                    DB::table('cohorts')->insert([
                        'track_id'   => $trackId,
                        'name'       => $name,
                        'status'     => $status,
                        'start_date' => $startDate,
                        'end_date'   => $endDate,
                        'created_by' => $bmId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $created++;
                }
            }
        }

        $total = DB::table('cohorts')->count();
        $this->command->info("✔ CohortSeeder — {$total} cohorts present ({$created} new). 32 intakes × 8 tracks.");
    }
}
