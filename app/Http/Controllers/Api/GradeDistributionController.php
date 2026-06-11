<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesGradingScope;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeDistributionController extends Controller
{
    use AuthorizesGradingScope;

    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'track_id' => ['sometimes', 'integer', 'exists:tracks,id'],
            'cohort_id' => ['sometimes', 'integer', 'exists:cohorts,id'],
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'grade_component_id' => ['sometimes', 'integer', 'exists:grade_components,id'],
            'lab_group_id' => ['sometimes', 'integer', 'exists:lab_groups,id'],
        ]);

        $isComponentDistribution = array_key_exists('grade_component_id', $filters);

        $grades = $this->scopeByStudentVisibility(
            Grade::query()->with(['student', 'gradeComponent.course', 'labGroup']),
            $request
        )
            ->when($filters['track_id'] ?? null, function ($query, int $trackId) {
                $query->whereHas('gradeComponent.course.cohort', fn ($cohortQuery) => $cohortQuery->where('track_id', $trackId));
            })
            ->when($filters['cohort_id'] ?? null, function ($query, int $cohortId) {
                $query->whereHas('gradeComponent.course', fn ($courseQuery) => $courseQuery->where('cohort_id', $cohortId));
            })
            ->when($filters['course_id'] ?? null, function ($query, int $courseId) {
                $query->whereHas('gradeComponent', fn ($componentQuery) => $componentQuery->where('course_id', $courseId));
            })
            ->when($filters['grade_component_id'] ?? null, fn ($query, int $componentId) => $query->where('grade_component_id', $componentId))
            ->when($filters['lab_group_id'] ?? null, fn ($query, int $labGroupId) => $query->where('lab_group_id', $labGroupId))
            ->get();

        $scores = $isComponentDistribution
            ? $this->componentPercentageScores($grades)
            : $this->courseTotalScores($grades);

        $buckets = collect([
            ['label' => '90-100', 'min' => 90, 'max' => 100],
            ['label' => '80-89', 'min' => 80, 'max' => 89.99],
            ['label' => '70-79', 'min' => 70, 'max' => 79.99],
            ['label' => '60-69', 'min' => 60, 'max' => 69.99],
            ['label' => '<60', 'min' => null, 'max' => 59.99],
        ])->map(function (array $bucket) use ($scores) {
            $count = $scores->filter(function (array $score) use ($bucket) {
                if ($bucket['min'] === null) {
                    return $score['score'] <= $bucket['max'];
                }

                return $score['score'] >= $bucket['min'] && $score['score'] <= $bucket['max'];
            })->count();

            return $bucket + ['count' => $count];
        })->values();

        return response()->json([
            'data' => [
                'score_type' => $isComponentDistribution ? 'component_percentage' : 'course_total',
                'uses_overrides' => true,
                'filters' => $filters,
                'total_students' => $scores->pluck('student_id')->unique()->count(),
                'student_course_count' => $scores->count(),
                'course_count' => $scores->pluck('course_id')->unique()->count(),
                'average_score' => $scores->isEmpty() ? 0 : round($scores->avg('score'), 2),
                'min_score' => $scores->isEmpty() ? null : round($scores->min('score'), 2),
                'max_score' => $scores->isEmpty() ? null : round($scores->max('score'), 2),
                'buckets' => $buckets,
            ],
        ]);
    }

    private function courseTotalScores($grades)
    {
        return $grades
            ->filter(fn (Grade $grade) => $grade->student_id && $grade->gradeComponent?->course)
            ->groupBy(fn (Grade $grade) => "{$grade->student_id}:{$grade->gradeComponent->course->id}")
            ->map(function ($courseGrades) {
                /** @var Grade $firstGrade */
                $firstGrade = $courseGrades->first();
                $course = $firstGrade->gradeComponent->course;

                return [
                    'student_id' => $firstGrade->student_id,
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'score' => round($courseGrades->sum(fn (Grade $grade) => (float) ($grade->override_value ?? $grade->normalized_score)), 2),
                ];
            })
            ->values();
    }

    private function componentPercentageScores($grades)
    {
        return $grades
            ->filter(fn (Grade $grade) => $grade->student_id && $grade->gradeComponent?->course)
            ->map(function (Grade $grade) {
                $component = $grade->gradeComponent;
                $weight = (float) $component->weight;
                $effectiveScore = (float) ($grade->override_value ?? $grade->normalized_score);

                return [
                    'student_id' => $grade->student_id,
                    'course_id' => $component->course->id,
                    'course_name' => $component->course->name,
                    'grade_component_id' => $component->id,
                    'score' => $weight > 0 ? round(($effectiveScore / $weight) * 100, 2) : 0,
                ];
            })
            ->values();
    }
}
