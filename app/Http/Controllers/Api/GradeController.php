<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\LabGroup;
use App\Models\Student;
use App\Services\GradeNormalizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class GradeController extends Controller
{
    public function __construct(
        private readonly GradeNormalizationService $normalizer
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $grades = Grade::query()
            ->with(['student', 'gradeComponent.course', 'labGroup', 'grader', 'overrider'])
            ->when($request->user()->hasRole('instructor'), function ($query) use ($request) {
                $query->whereHas('labGroup', fn ($labGroupQuery) => $labGroupQuery->where('instructor_id', $request->user()->id));
            })
            ->when($request->integer('student_id'), fn ($query, $id) => $query->where('student_id', $id))
            ->when($request->integer('grade_component_id'), fn ($query, $id) => $query->where('grade_component_id', $id))
            ->when($request->integer('lab_group_id'), fn ($query, $id) => $query->where('lab_group_id', $id))
            ->when($request->integer('course_id'), function ($query, $id) {
                $query->whereHas('gradeComponent', fn ($componentQuery) => $componentQuery->where('course_id', $id));
            })
            ->latest()
            ->paginate(20);

        return GradeResource::collection($grades);
    }

    public function store(StoreGradeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $component = GradeComponent::findOrFail($data['grade_component_id']);
        $student = Student::findOrFail($data['student_id']);

        if ($request->user()->hasRole('instructor')) {
            $this->authorizeInstructorLabGroup($request, $data['lab_group_id'] ?? null, $student);
        }

        $normalizedScore = $this->normalizedScore((float) $data['raw_score'], $component);

        $grade = Grade::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'grade_component_id' => $data['grade_component_id'],
            ],
            [
                'lab_group_id' => $data['lab_group_id'] ?? null,
                'raw_score' => $data['raw_score'],
                'normalized_score' => $normalizedScore,
                'graded_by' => $request->user()->id,
            ]
        );

        return (new GradeResource($grade->load(['student', 'gradeComponent.course', 'labGroup', 'grader'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Grade $grade): GradeResource
    {
        $this->authorizeGradeAccess(request(), $grade);

        return new GradeResource($grade->load(['student', 'gradeComponent.course', 'labGroup', 'grader', 'overrider']));
    }

    public function update(UpdateGradeRequest $request, Grade $grade): GradeResource
    {
        $this->authorizeGradeAccess($request, $grade);

        $data = $request->validated();

        if (array_key_exists('raw_score', $data)) {
            $grade->loadMissing('gradeComponent');
            $grade->raw_score = $data['raw_score'];
            $grade->normalized_score = $this->normalizedScore((float) $data['raw_score'], $grade->gradeComponent);
            $grade->graded_by = $request->user()->id;
        }

        if (array_key_exists('lab_group_id', $data)) {
            if ($request->user()->hasRole('instructor')) {
                $grade->loadMissing('student');
                $this->authorizeInstructorLabGroup($request, $data['lab_group_id'], $grade->student);
            }

            $grade->lab_group_id = $data['lab_group_id'];
        }

        $grade->save();

        return new GradeResource($grade->load(['student', 'gradeComponent.course', 'labGroup', 'grader', 'overrider']));
    }

    public function destroy(Grade $grade): Response
    {
        $this->authorizeGradeAccess(request(), $grade);

        $grade->delete();

        return response()->noContent();
    }

    private function authorizeGradeAccess(Request $request, Grade $grade): void
    {
        if (! $request->user()->hasRole('instructor')) {
            return;
        }

        $grade->loadMissing('labGroup');

        if (! $grade->labGroup || $grade->labGroup->instructor_id !== $request->user()->id) {
            abort(403, 'You can only access grades for your assigned lab groups.');
        }
    }

    private function authorizeInstructorLabGroup(Request $request, ?int $labGroupId, Student $student): void
    {
        if (! $labGroupId) {
            abort(403, 'Instructor grading requires a lab group.');
        }

        $labGroup = LabGroup::findOrFail($labGroupId);

        if ($labGroup->instructor_id !== $request->user()->id) {
            abort(403, 'You can only grade your assigned lab groups.');
        }

        if ($student->lab_group_id !== $labGroup->id) {
            abort(403, 'This student is not assigned to that lab group.');
        }
    }

    private function normalizedScore(float $rawScore, GradeComponent $component): float
    {
        try {
            return $this->normalizer->calculate($rawScore, $component);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'raw_score' => $exception->getMessage(),
            ]);
        }
    }
}
