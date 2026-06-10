<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentTagRequest;
use App\Http\Requests\UpdateStudentTagRequest;
use App\Http\Resources\StudentTagResource;
use App\Models\Student;
use App\Models\StudentTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StudentTagController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tags = StudentTag::query()
            ->with(['student', 'taggedBy', 'course'])
            ->when($request->user()->hasRole('instructor'), function ($query) use ($request) {
                $query->whereHas('student.labGroup', fn ($labGroupQuery) => $labGroupQuery->where('instructor_id', $request->user()->id));
            })
            ->when($request->integer('student_id'), fn ($query, $id) => $query->where('student_id', $id))
            ->when($request->integer('course_id'), fn ($query, $id) => $query->where('course_id', $id))
            ->latest()
            ->paginate(20);

        return StudentTagResource::collection($tags);
    }

    public function store(StoreStudentTagRequest $request): JsonResponse
    {
        $data = $request->validated();
        $student = Student::findOrFail($data['student_id']);
        $this->authorizeStudentAccess($request, $student);

        $tag = StudentTag::create([
            ...$data,
            'tagged_by' => $request->user()->id,
        ]);

        return (new StudentTagResource($tag->load(['student', 'taggedBy', 'course'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(StudentTag $studentTag): StudentTagResource
    {
        $studentTag->loadMissing('student.labGroup');
        $this->authorizeStudentAccess(request(), $studentTag->student);

        return new StudentTagResource($studentTag->load(['student', 'taggedBy', 'course']));
    }

    public function update(UpdateStudentTagRequest $request, StudentTag $studentTag): StudentTagResource
    {
        $studentTag->loadMissing('student.labGroup');
        $this->authorizeStudentAccess($request, $studentTag->student);

        $studentTag->update($request->validated());

        return new StudentTagResource($studentTag->load(['student', 'taggedBy', 'course']));
    }

    public function destroy(StudentTag $studentTag): Response
    {
        $studentTag->loadMissing('student.labGroup');
        $this->authorizeStudentAccess(request(), $studentTag->student);

        $studentTag->delete();

        return response()->noContent();
    }

    private function authorizeStudentAccess(Request $request, Student $student): void
    {
        $student->loadMissing('labGroup');

        if (
            $request->user()->hasRole('instructor')
            && (! $student->labGroup || $student->labGroup->instructor_id !== $request->user()->id)
        ) {
            abort(403, 'You can only manage tags for students in your assigned lab groups.');
        }
    }
}
