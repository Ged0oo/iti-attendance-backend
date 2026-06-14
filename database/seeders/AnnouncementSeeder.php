<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        // Look up all delivering cohorts dynamically — not hardcoded by name
        $deliveringCohorts = DB::table('cohorts')->where('status', 'delivering')->get();

        if ($deliveringCohorts->isEmpty()) {
            $this->command->warn('AnnouncementSeeder: No delivering cohorts found — skipping.');
            return;
        }

        $created = 0;

        foreach ($deliveringCohorts as $cohort) {
            // Find the track admin for this cohort's track
            $taId = DB::table('track_admins')
                ->where('track_id', $cohort->track_id)
                ->value('user_id');

            // Fall back to any track_admin if track-specific one not found
            if (!$taId) {
                $taId = DB::table('users')->where('role', 'track_admin')->value('id');
            }

            if (!$taId) {
                $this->command->warn("  No track admin found for cohort #{$cohort->id} — skipping.");
                continue;
            }

            $trackName = DB::table('tracks')->where('id', $cohort->track_id)->value('name') ?? 'Your Track';

            $announcements = [
                [
                    'title' => "Welcome to {$cohort->name} — {$trackName}",
                    'body'  => "Welcome everyone to the new intake of the {$trackName} track. Please check your schedules and make sure to attend all sessions on time. We look forward to a productive program.",
                ],
                [
                    'title' => 'Lab Group Assignments Published',
                    'body'  => 'Lab groups have been finalized and assigned. Please log in to your student profile to see which group you belong to and who your lab instructor is.',
                ],
                [
                    'title' => 'Code of Conduct & Attendance Policy',
                    'body'  => 'Please review the ITI attendance policy carefully. Each unexcused absence deducts 25 points from your ledger. You may submit an excuse request within 48 hours of any absence.',
                ],
            ];

            foreach ($announcements as $ann) {
                $exists = DB::table('announcements')
                    ->where('title', $ann['title'])
                    ->where('cohort_id', $cohort->id)
                    ->exists();

                if (!$exists) {
                    DB::table('announcements')->insert([
                        'cohort_id'    => $cohort->id,
                        'posted_by'    => $taId,
                        'title'        => $ann['title'],
                        'body'         => $ann['body'],
                        'published_at' => now(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $created++;
                }
            }
        }

        $total = DB::table('announcements')->count();
        $this->command->info("✔ AnnouncementSeeder — {$total} announcements present ({$created} new).");
    }
}
