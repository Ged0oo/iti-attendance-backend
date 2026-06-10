<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentGradeCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gradesByCourse = $this->grades
            ->groupBy(fn ($grade) => $grade->gradeComponent?->course?->id ?? 'uncategorized')
            ->map(function ($grades) {
                $course = $grades->first()->gradeComponent?->course;

                return [
                    'course' => $course ? new CourseResource($course) : null,
                    'total_score' => round($grades->sum(
                        fn ($grade) => (float) ($grade->override_value ?? $grade->normalized_score)
                    ), 2),
                    'components' => GradeResource::collection($grades),
                ];
            })
            ->values();

        return [
            'student' => [
                'id' => $this->id,
                'user_id' => $this->user_id,
                'national_id' => $this->national_id,
                'is_at_risk' => $this->is_at_risk,
                'user' => $this->whenLoaded('user', fn () => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ]),
            ],
            'courses' => $gradesByCourse,
            'tags' => StudentTagResource::collection($this->whenLoaded('tags')),
            'notes' => StudentNoteResource::collection($this->whenLoaded('notes')),
        ];
    }
}
