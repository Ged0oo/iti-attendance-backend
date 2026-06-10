<?php

namespace App\Observers;

use App\Models\Grade;
use App\Services\AtRiskService;

class GradeObserver
{
    public function saved(Grade $grade): void
    {
        $this->reevaluate($grade);
    }

    public function deleted(Grade $grade): void
    {
        $this->reevaluate($grade);
    }

    private function reevaluate(Grade $grade): void
    {
        $grade->loadMissing('student');

        if ($grade->student) {
            AtRiskService::evaluate($grade->student);
        }
    }
}
