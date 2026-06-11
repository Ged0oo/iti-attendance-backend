<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\AttendanceLedgerEntry;
use App\Models\AttendanceRecord;
use App\Models\Session;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $student2 = Student::skip(1)->first();
        if (!$student2) return;

        $cohort = $student2->cohort;
        if (!$cohort) return;

        $session = Session::whereHas('engagement', function ($q) use ($cohort) {
            $q->where('cohort_id', $cohort->id);
        })->first();

        if (!$session) {
            $engagement = Engagement::where('cohort_id', $cohort->id)->first();
            if (!$engagement) {
                $instructor = User::role('instructor')->first();
                if (!$instructor) {
                    $instructor = User::factory()->create([
                        'name' => 'Instructor User',
                        'email' => 'instructor@example.com',
                    ]);
                    $instructor->assignRole('instructor');
                }
                // the instructor we use must always have a profile
                \App\Models\Instructor::firstOrCreate(
                    ['user_id' => $instructor->id],
                    [
                        'compensation_type' => 'internal',
                        'hourly_rate'       => 50,
                        'fixed_salary'      => 0,
                    ]
                );
                $engagement = Engagement::create([
                    'cohort_id' => $cohort->id,
                    'instructor_id' => $instructor->id,
                    'type' => 'lecture',
                    'date_range_start' => now()->subDays(5)->toDateString(),
                    'date_range_end' => now()->addDays(5)->toDateString(),
                    'scheduled_hours' => 20,
                    'status' => 'active',
                ]);
            }
            $session = Session::create([
                'engagement_id' => $engagement->id,
                'date' => now()->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'scheduled_hours' => 7,
                'is_delivered' => false,
                'qr_code' => '1',
            ]);
        }

        $trackId = $cohort->track_id;

        $attendanceRecord = AttendanceRecord::firstOrCreate([
            'session_id' => $session->id,
            'student_id' => $student2->id,
            'track_id' => $trackId,
        ], [
            'status' => 'absent',
        ]);

        $ledger = $student2->ledger;
        if ($ledger) {
            $ledger->update(['balance' => 225]);

            AttendanceLedgerEntry::firstOrCreate([
                'attendance_ledger_id' => $ledger->id,
                'attendance_record_id' => $attendanceRecord->id,
            ], [
                'delta' => -25,
                'balance_after' => 225,
                'reason' => 'Unexcused absence',
            ]);
        }
    }
}
