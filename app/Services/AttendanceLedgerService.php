<?php

namespace App\Services;

use App\Models\Student;
use App\Models\AttendanceRecord;
use App\Models\ExcuseRequest;
use LogicException;

class AttendanceLedgerService
{
    public function deductUnexcused(Student $student, AttendanceRecord $record)
    {
        $ledger = $student->ledger;
        $newBalance = max(0, $ledger->balance - 25);
        
        $entry = $ledger->entries()->create([
            'attendance_record_id' => $record->id,
            'delta' => -25,
            'balance_after' => $newBalance,
            'reason' => 'Unexcused absence',
        ]);
        
        $ledger->update(['balance' => $newBalance]);
        
        AtRiskService::evaluate($student);
        
        return $entry;
    }

    public function applyExcuseApproval(ExcuseRequest $excuse): void
    {
        $student = $excuse->student;
        $ledger = $student->ledger;
        
        $entry = $ledger->entries()
            ->where('attendance_record_id', $excuse->attendance_record_id)
            ->where('delta', -25)
            ->first();
            
        if (!$entry) {
            throw new LogicException('Unexcused absence entry not found.');
        }
        
        $entry->update([
            'delta' => -5,
            'balance_after' => $entry->balance_after + 20
        ]);
        
        // Update all subsequent entries' balance_after += 20
        $ledger->entries()
            ->where('id', '>', $entry->id)
            ->increment('balance_after', 20);
            
        $ledger->increment('balance', 20);
        
        AtRiskService::evaluate($student);
    }

    public function recalculateBalance(Student $student): int
    {
        $ledger = $student->ledger;
        $sum = $ledger->entries()->sum('delta');
        $expectedBalance = max(0, 250 + $sum);
        
        $ledger->update(['balance' => $expectedBalance]);
        return $expectedBalance;
    }
}
