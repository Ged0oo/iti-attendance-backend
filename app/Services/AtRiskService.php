<?php

namespace App\Services;

use App\Models\Student;

class AtRiskService
{
    public static function evaluate(Student $student): void
    {
        $isAtRisk = $student->ledger->balance < 150;
        
        // M6 INTEGRATION: also set true if any course normalized_score < 60
        
        $student->update(['is_at_risk' => $isAtRisk]);
    }
}
