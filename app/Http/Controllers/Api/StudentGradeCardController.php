<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentGradeCardResource;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentGradeCardController extends Controller
{
    public function __invoke(Request $request, Student $student): StudentGradeCardResource
    {
        $student->loadMissing('labGroup');

        if ($request->user()->hasRole('student') && $student->user_id !== $request->user()->id) {
            abort(403, 'You can only view your own grade card.');
        }

        if (
            $request->user()->hasRole('instructor')
            && (! $student->labGroup || $student->labGroup->instructor_id !== $request->user()->id)
        ) {
            abort(403, 'You can only view grade cards for students in your assigned lab groups.');
        }

        $student->load([
            'user',
            'labGroup',
            'grades.gradeComponent.course',
            'grades.labGroup',
            'grades.grader',
            'grades.overrider',
            'tags.taggedBy',
            'tags.course',
            'notes.writtenBy',
            'notes.course',
        ]);

        return new StudentGradeCardResource($student);
    }
}
