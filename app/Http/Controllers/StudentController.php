<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Cohort;
use App\Models\LabGroup;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request, Cohort $cohort)
    {
        $students = Student::with('user')
            ->where('cohort_id', $cohort->id)
            ->when($request->user()?->hasRole(UserRole::INSTRUCTOR->value), function ($query) use ($request) {
                $query->whereHas('labGroup', fn ($labGroupQuery) => $labGroupQuery->where('instructor_id', $request->user()->id));
            })
            ->get();

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

        // a student may only read their own profile
        if ($user && $user->hasRole(UserRole::STUDENT->value) && $student->user_id !== $user->id) {
            abort(403, 'Unauthorized access to other student profile.');
        }

        return new StudentResource($student->load('ledger'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student->update($request->validated());
        return new StudentResource($student);
    }

    public function assignLabGroup(Request $request, Student $student)
    {
        $data = $request->validate(['lab_group_id' => 'required|exists:lab_groups,id']);

        $labGroup = LabGroup::findOrFail($data['lab_group_id']);

        // the lab group has to belong to the same cohort as the student
        if ($labGroup->cohort_id !== $student->cohort_id) {
            abort(422, "Lab group is not in the student's cohort.");
        }

        $student->update(['lab_group_id' => $labGroup->id]);
        return new StudentResource($student);
    }

    public function atRisk()
    {
        $students = Student::where('is_at_risk', true)->get();
        return StudentResource::collection($students);
    }
}
