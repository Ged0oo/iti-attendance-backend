<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $cohortId = DB::table('cohorts')->where('name', 'Intake 46')->value('id');
        $taId = DB::table('users')->where('role', 'track_admin')->value('id');

        if (!$cohortId || !$taId) {
            $this->command->warn('Skipping announcements — missing cohort or TA user.');
            return;
        }

        $announcements = [
            [
                'title' => 'Welcome to Intake 46',
                'body' => 'Welcome everyone to the new intake. Please check your schedules and make sure to attend all sessions on time.',
            ],
            [
                'title' => 'Lab Group Assignments',
                'body' => 'Lab groups have been assigned. Check your profile to see which group you belong to.',
            ],
        ];

        foreach ($announcements as $ann) {
            $exists = DB::table('announcements')
                ->where('title', $ann['title'])
                ->where('cohort_id', $cohortId)
                ->exists();

            if (!$exists) {
                DB::table('announcements')->insert([
                    'cohort_id' => $cohortId,
                    'posted_by' => $taId,
                    'title' => $ann['title'],
                    'body' => $ann['body'],
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Announcements seeded.');
    }
}
