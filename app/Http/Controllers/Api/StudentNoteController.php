<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentNoteRequest;
use App\Http\Resources\StudentNoteResource;
use App\Models\Student;
use App\Models\StudentNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StudentNoteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notes = StudentNote::query()
            ->with(['student', 'writtenBy', 'course'])
            ->when($request->user()->hasRole('instructor'), function ($query) use ($request) {
                $query->whereHas('student.labGroup', fn ($labGroupQuery) => $labGroupQuery->where('instructor_id', $request->user()->id));
            })
            ->when($request->integer('student_id'), fn ($query, $id) => $query->where('student_id', $id))
            ->when($request->integer('course_id'), fn ($query, $id) => $query->where('course_id', $id))
            ->latest()
            ->paginate(20);

        return StudentNoteResource::collection($notes);
    }

    public function store(StoreStudentNoteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $student = Student::findOrFail($data['student_id']);
        $this->authorizeStudentAccess($request, $student);

        $note = StudentNote::create([
            ...$data,
            'written_by' => $request->user()->id,
        ]);

        return (new StudentNoteResource($note->load(['student', 'writtenBy', 'course'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(StudentNote $studentNote): StudentNoteResource
    {
        $studentNote->loadMissing('student.labGroup');
        $this->authorizeStudentAccess(request(), $studentNote->student);

        return new StudentNoteResource($studentNote->load(['student', 'writtenBy', 'course']));
    }

    public function destroy(StudentNote $studentNote): Response
    {
        $studentNote->loadMissing('student.labGroup');
        $this->authorizeStudentAccess(request(), $studentNote->student);

        $studentNote->delete();

        return response()->noContent();
    }

    private function authorizeStudentAccess(Request $request, Student $student): void
    {
        $student->loadMissing('labGroup');

        if (
            $request->user()->hasRole('instructor')
            && (! $student->labGroup || $student->labGroup->instructor_id !== $request->user()->id)
        ) {
            abort(403, 'You can only manage notes for students in your assigned lab groups.');
        }
    }
}
