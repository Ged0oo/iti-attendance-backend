<?php

namespace App\Services;

use App\Models\Session;
use App\Models\Student;
use App\Models\AttendanceRecord;
use App\Events\SessionClosed;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Process a student's QR scan.
     * Determines if it's an arrival (first scan) or departure (second scan).
     */
public function processScan(Session $session, Student $student): array
    {
        // BYPASS: Because M5's Student model relationships aren't merged yet, 
        // we use a raw DB query to fetch the track_id through the cohort table.
        $trackId = \Illuminate\Support\Facades\DB::table('cohorts')
            ->where('id', $student->cohort_id)
            ->value('track_id');

        $record = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->where('track_id', $trackId)
            ->first();

        if (!$record) {
            // First Scan: Check-In
            $record = AttendanceRecord::create([
                'session_id' => $session->id,
                'student_id' => $student->id,
                'track_id'   => $trackId,
                'arrived_at' => now(),
                'status'     => 'present',
            ]);

            return ['status' => 'arrived', 'timestamp' => $record->arrived_at->format('h:i A')];
        }

        if (is_null($record->left_at)) {
            // Second Scan: Check-Out
            $record->update(['left_at' => now()]);
            return ['status' => 'left', 'timestamp' => $record->left_at->format('h:i A')];
        }

        // Already checked out
        return ['status' => 'completed', 'timestamp' => $record->left_at->format('h:i A')];
    }

    public function closeSession(Session $session): int
    {
        return DB::transaction(function () use ($session) {
            
            // BYPASS: Because M3's Session model relationships aren't merged yet, 
            // we use a raw DB query to fetch the cohort_id through the engagements table.
            $cohortId = \Illuminate\Support\Facades\DB::table('engagements')
                ->where('id', $session->engagement_id)
                ->value('cohort_id');

            // Get all students supposed to be in this session's lab group/cohort
            $expectedStudentIds = Student::where('cohort_id', $cohortId)->pluck('id');

            // Get students who already scanned
            $presentStudentIds = AttendanceRecord::where('session_id', $session->id)
                ->pluck('student_id');

            $absentStudentIds = $expectedStudentIds->diff($presentStudentIds);

            $absentRecords = [];
            $now = now();

            foreach ($absentStudentIds as $studentId) {
                // BYPASS: Because M5's Student model relationships aren't merged yet,
                // we use a raw DB query to fetch the track_id through the cohort table.
                $studentCohortId = Student::find($studentId)->cohort_id;
                $trackId = \Illuminate\Support\Facades\DB::table('cohorts')
                    ->where('id', $studentCohortId)
                    ->value('track_id');

                $absentRecords[] = [
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                    'track_id'   => $trackId,
                    'status'     => 'absent',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Bulk insert for performance
            if (!empty($absentRecords)) {
                AttendanceRecord::insert($absentRecords);
            }

            // Mark the session closed (attendance finalised). It was also held,
            // so it counts as delivered for billing.
            \Illuminate\Support\Facades\DB::table('sessions')
                ->where('id', $session->id)
                ->update(['closed_at' => $now, 'is_delivered' => true]);

            // Fire event so M5 (Ledger) can deduct 25 points per absent student
            event(new \App\Events\SessionClosed($session, $absentStudentIds));

            return count($absentRecords);
        });
    }
}