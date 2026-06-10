<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesGradingScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\OverrideGradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use Illuminate\Validation\ValidationException;

class GradeOverrideController extends Controller
{
    use AuthorizesGradingScope;

    public function __invoke(OverrideGradeRequest $request, Grade $grade): GradeResource
    {
        $data = $request->validated();
        $grade->loadMissing(['gradeComponent', 'student']);
        $this->authorizeStudentVisibility($request, $grade->student, 'You can only override grades for your assigned lab groups.');

        if ((float) $data['override_value'] > (float) $grade->gradeComponent->weight) {
            throw ValidationException::withMessages([
                'override_value' => 'Override value cannot exceed the component weight.',
            ]);
        }

        $grade->update([
            'override_value' => $data['override_value'],
            'override_note' => $data['override_note'],
            'overridden_by' => $request->user()->id,
            'overridden_at' => now(),
        ]);

        return new GradeResource($grade->load(['student', 'gradeComponent.course', 'labGroup', 'grader', 'overrider']));
    }
}
