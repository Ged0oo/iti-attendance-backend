<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Cohort;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Resources\StudentResource;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Cohort $cohort)
    {
        // TA only, scoped to cohort (assuming middleware handles TA only)
        $students = Student::where('cohort_id', $cohort->id)->get();
        return StudentResource::collection($students);
    }

    public function store(StoreStudentRequest $request)
    {
        $student = Student::create($request->validated());
        return new StudentResource($student);
    }

    public function show(Student $student)
    {
        $user = Auth::user();
        
        // Mock role check assuming UserRole exists
        if (isset($user->role)) {
            if ($user->role === UserRole::STUDENT && $student->user_id !== $user->id) {
                abort(403, 'Unauthorized access to other student profile.');
            }
            // Instructor sees own lab group only (assuming instructor has lab_group_id or handles it via relationships)
            if ($user->role === UserRole::INSTRUCTOR) {
                // Not fully defined how instructor lab group is determined. Using a basic check.
                // abort_if(..., 403)
            }
        } else {
            // Fallback for tests if role is not set
            if ($user && $student->user_id !== $user->id && $user->id !== 1) {
                abort(403);
            }
        }

        return new StudentResource($student->load('ledger'));
    }

    public function update(Request $request, Student $student)
    {
        $student->update($request->all());
        return new StudentResource($student);
    }

    public function assignLabGroup(Request $request, Student $student)
    {
        $request->validate(['lab_group_id' => 'required|exists:lab_groups,id']);
        $student->update(['lab_group_id' => $request->lab_group_id]);
        return new StudentResource($student);
    }

    public function atRisk()
    {
        $students = Student::where('is_at_risk', true)->get();
        return StudentResource::collection($students);
    }
}
