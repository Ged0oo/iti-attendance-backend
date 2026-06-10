<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\AttendanceLedgerEntry;
use App\Models\AttendanceRecord;
use Illuminate\Database\Seeder;

class AttendanceLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $student2 = Student::skip(1)->first();
        if (!$student2) return;

        $recordId = 1;

        $ledger = $student2->ledger;
        if ($ledger) {
            $ledger->update(['balance' => 225]);

            AttendanceLedgerEntry::create([
                'attendance_ledger_id' => $ledger->id,
                'attendance_record_id' => $recordId,
                'delta' => -25,
                'balance_after' => 225,
                'reason' => 'Unexcused absence',
            ]);
        }
    }
}
