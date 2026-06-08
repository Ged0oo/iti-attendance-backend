<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CohortSeeder extends Seeder
{
    public function run(): void
    {
        $webTrackId = DB::table('tracks')->where('name', 'Web Development')->value('id');
        $mobileTrackId = DB::table('tracks')->where('name', 'Mobile Development')->value('id');

        $bmId = DB::table('users')->where('role', 'branch_manager')->value('id');

        $cohorts = [
            [
                'track_id' => $webTrackId,
                'name' => 'Intake 46',
                'status' => 'delivering',
                'start_date' => '2026-05-01',
                'end_date' => '2026-08-30',
                'created_by' => $bmId,
            ],
            [
                'track_id' => $mobileTrackId,
                'name' => 'Intake 46',
                'status' => 'configuring',
                'start_date' => '2026-06-01',
                'end_date' => '2026-09-30',
                'created_by' => $bmId,
            ],
        ];

        foreach ($cohorts as $cohort) {
            if (!$cohort['track_id']) {
                continue;
            }

            $exists = DB::table('cohorts')
                ->where('track_id', $cohort['track_id'])
                ->where('name', $cohort['name'])
                ->exists();

            if (!$exists) {
                DB::table('cohorts')->insert(array_merge($cohort, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('Cohorts seeded.');
    }
}
