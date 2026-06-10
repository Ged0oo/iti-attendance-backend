<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesGradingScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentSubmissionRequest;
use App\Http\Resources\AssignmentSubmissionResource;
use App\Models\AssignmentSubmission;
use App\Models\GradeComponent;
use App\Models\Student;
use App\Services\LatePenaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class AssignmentSubmissionController extends Controller
{
    use AuthorizesGradingScope;

    public function __construct(
        private readonly LatePenaltyService $latePenalty
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $submissions = $this->scopeByStudentVisibility(
            AssignmentSubmission::query()->with(['student', 'gradeComponent.course']),
            $request
        )
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
        $component = GradeComponent::findOrFail($data['grade_component_id']);
        $this->authorizeStudentVisibility($request, $student, 'You can only submit assignments for students in your assigned lab groups.');

        $filePath = null;

        if ($data['submission_type'] === 'file' && $request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignment-submissions');
        }

        $submittedAt = isset($data['submitted_at']) ? Carbon::parse($data['submitted_at']) : now();
        $daysLate = $this->daysLate($submittedAt, $component);

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'grade_component_id' => $data['grade_component_id'],
            ],
            [
                'submission_type' => $data['submission_type'],
                'url' => $data['submission_type'] === 'url' ? ($data['url'] ?? null) : null,
                'file_path' => $filePath,
                'submitted_at' => $submittedAt,
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

        $this->authorizeStudentVisibility($request, $submission->student, 'You can only access submissions for your assigned lab groups.');
    }

    private function daysLate(Carbon $submittedAt, GradeComponent $component): int
    {
        if (! $component->due_at || $submittedAt->lessThanOrEqualTo($component->due_at)) {
            return 0;
        }

        return (int) floor($component->due_at->diffInHours($submittedAt) / 24);
    }
}
