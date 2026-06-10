<?php

namespace App\Observers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Services\AttendanceLedgerService;

class AttendanceRecordObserver
{
    protected AttendanceLedgerService $ledgerService;

    public function __construct(AttendanceLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function updated(AttendanceRecord $record): void
    {
        if ($record->isDirty('status') && $record->status === 'absent') {
            $student = Student::find($record->student_id);
            if ($student) {
                $this->ledgerService->deductUnexcused($student, $record);
            }
        }
    }
}
