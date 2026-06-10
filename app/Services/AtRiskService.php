<?php

namespace App\Services;

use App\Models\Student;

class AtRiskService
{
    public static function evaluate(Student $student): void
    {
        $student->loadMissing(['ledger', 'grades.gradeComponent.course']);

        $ledgerBalance = $student->ledger?->balance;
        $attendanceRisk = $ledgerBalance !== null && $ledgerBalance < 150;

        $gradeRisk = $student->grades
            ->groupBy(fn ($grade) => $grade->gradeComponent?->course_id)
            ->filter(fn ($grades, $courseId) => $courseId !== null)
            ->contains(function ($grades) {
                $courseTotal = $grades->sum(fn ($grade) => (float) ($grade->override_value ?? $grade->normalized_score));

                return $courseTotal < 60;
            });

        $isAtRisk = $attendanceRisk || $gradeRisk;

        $student->update(['is_at_risk' => $isAtRisk]);
    }
}
