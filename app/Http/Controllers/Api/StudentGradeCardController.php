<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesGradingScope;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentGradeCardResource;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentGradeCardController extends Controller
{
    use AuthorizesGradingScope;

    public function __invoke(Request $request, Student $student): StudentGradeCardResource
    {
        $this->authorizeStudentVisibility($request, $student, 'You can only view grade cards for students in your assigned lab groups.');

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
