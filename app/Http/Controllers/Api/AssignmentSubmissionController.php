<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentSubmissionRequest;
use App\Http\Resources\AssignmentSubmissionResource;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Services\LatePenaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AssignmentSubmissionController extends Controller
{
    public function __construct(
        private readonly LatePenaltyService $latePenalty
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $submissions = AssignmentSubmission::query()
            ->with(['student', 'gradeComponent.course'])
            ->when($request->user()->hasRole('instructor'), function ($query) use ($request) {
                $query->whereHas('student.labGroup', fn ($labGroupQuery) => $labGroupQuery->where('instructor_id', $request->user()->id));
            })
            ->when($request->integer('student_id'), fn ($query, $id) => $query->where('student_id', $id))
            ->when($request->integer('grade_component_id'), fn ($query, $id) => $query->where('grade_component_id', $id))
            ->latest()
            ->paginate(20);

        return AssignmentSubmissionResource::collection($submissions);
    }

    public function store(StoreAssignmentSubmissionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $student = Student::findOrFail($data['student_id']);

        if ($request->user()->hasRole('student') && $student->user_id !== $request->user()->id) {
            abort(403, 'You can only submit your own assignments.');
        }

        $filePath = null;

        if ($data['submission_type'] === 'file' && $request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignment-submissions');
        }

        //TODO: The current schema has no deliverable due date, so late days stay zero until that field exists.
        $daysLate = 0;

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'grade_component_id' => $data['grade_component_id'],
            ],
            [
                'submission_type' => $data['submission_type'],
                'url' => $data['submission_type'] === 'url' ? ($data['url'] ?? null) : null,
                'file_path' => $filePath,
                'submitted_at' => $data['submitted_at'] ?? now(),
                'days_late' => $daysLate,
                'late_penalty' => $this->latePenalty->calculate($daysLate),
            ]
        );

        return (new AssignmentSubmissionResource($submission->load(['student', 'gradeComponent.course'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(AssignmentSubmission $assignmentSubmission): AssignmentSubmissionResource
    {
        $this->authorizeSubmissionAccess(request(), $assignmentSubmission);

        return new AssignmentSubmissionResource($assignmentSubmission->load(['student', 'gradeComponent.course']));
    }

    public function destroy(AssignmentSubmission $assignmentSubmission): Response
    {
        $this->authorizeSubmissionAccess(request(), $assignmentSubmission);

        $assignmentSubmission->delete();

        return response()->noContent();
    }

    private function authorizeSubmissionAccess(Request $request, AssignmentSubmission $submission): void
    {
        $submission->loadMissing('student.labGroup');

        if ($request->user()->hasRole('student') && $submission->student->user_id !== $request->user()->id) {
            abort(403, 'You can only access your own submissions.');
        }

        if (
            $request->user()->hasRole('instructor')
            && (! $submission->student->labGroup || $submission->student->labGroup->instructor_id !== $request->user()->id)
        ) {
            abort(403, 'You can only access submissions for your assigned lab groups.');
        }
    }
}
