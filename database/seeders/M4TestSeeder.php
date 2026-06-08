<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class M4TestSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Safe Student User
        $userId = DB::table('users')->where('email', 'nagy@iti.gov.eg')->value('id');
        if (!$userId) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Mohamed Nagy',
                'email' => 'nagy@iti.gov.eg',
                'password' => Hash::make('password'),
                'role' => 'student',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Safe Instructor User
        $instructorId = DB::table('users')->where('email', 'mina@iti.gov.eg')->value('id');
        if (!$instructorId) {
            $instructorId = DB::table('users')->insertGetId([
                'name' => 'Mina Nagy',
                'email' => 'mina@iti.gov.eg',
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3. Safe Branch & Track
        $branchId = DB::table('branches')->where('name', 'Smart Village')->value('id') ?? DB::table('branches')->insertGetId(['name' => 'Smart Village', 'location' => 'Cairo']);
        $trackId = DB::table('tracks')->where('name', 'Open Source Applications Development')->value('id') ?? DB::table('tracks')->insertGetId(['branch_id' => $branchId, 'name' => 'Open Source Applications Development', 'description' => 'Just Mina Track']);

        // 4. Safe Cohort
        $cohortId = DB::table('cohorts')->where('name', 'Intake 46 - OSAD')->value('id')
                    ?? DB::table('cohorts')->insertGetId([
                        'track_id' => $trackId,
                        'name' => 'Intake 46 - OSAD',
                        'status' => 'delivering',
                        'start_date' => $now->copy()->subDays(10),
                        'end_date' => $now->copy()->addMonths(3),
                    ]);

        // 5. Safe Student Profile
        $studentId = DB::table('students')->where('user_id', $userId)->value('id')
                     ?? DB::table('students')->insertGetId([
                         'user_id' => $userId,
                         'cohort_id' => $cohortId,
                         'national_id' => '12345678901234',
                         'is_at_risk' => false,
                     ]);

        // 6. Safe Engagement & Session
        $engagementId = DB::table('engagements')->where('instructor_id', $instructorId)->value('id')
                        ?? DB::table('engagements')->insertGetId([
                            'cohort_id' => $cohortId,
                            'instructor_id' => $instructorId,
                            'type' => 'lecture',
                            'date_range_start' => $now->copy()->subDays(5),
                            'date_range_end' => $now->copy()->addDays(5),
                            'scheduled_hours' => 20,
                            'status' => 'active',
                        ]);

        // We specifically check for today's session so the security logic works
        $sessionId = DB::table('sessions')->where('engagement_id', $engagementId)->where('date', $now->toDateString())->value('id');
        if (!$sessionId) {
            $sessionId = DB::table('sessions')->insertGetId([
                'engagement_id' => $engagementId,
                'date' => $now->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'scheduled_hours' => 7,
                'is_delivered' => false,
                'qr_code' => '1', // Hardcoded to '1' for easy Postman testing
            ]);
        }

        $this->command->info("✅ Safe M4 Test Data Seeded (No duplicates created)!");
        $this->command->info("Session ID to use in Postman: {$sessionId}");
        $this->command->info("Student ID to use in Postman: {$studentId}");
    }
}