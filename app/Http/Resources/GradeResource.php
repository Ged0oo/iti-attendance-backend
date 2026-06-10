<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'grade_component_id' => $this->grade_component_id,
            'lab_group_id' => $this->lab_group_id,
            'raw_score' => $this->raw_score,
            'normalized_score' => $this->normalized_score,
            'effective_score' => $this->override_value ?? $this->normalized_score,
            'graded_by' => $this->graded_by,
            'override_value' => $this->override_value,
            'override_note' => $this->override_note,
            'overridden_by' => $this->overridden_by,
            'overridden_at' => $this->overridden_at,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'user_id' => $this->student->user_id,
                'is_at_risk' => $this->student->is_at_risk,
            ]),
            'grade_component' => new GradeComponentResource($this->whenLoaded('gradeComponent')),
            'lab_group' => $this->whenLoaded('labGroup', fn () => [
                'id' => $this->labGroup->id,
                'name' => $this->labGroup->name,
                'course_id' => $this->labGroup->course_id,
                'instructor_id' => $this->labGroup->instructor_id,
            ]),
            'grader' => $this->whenLoaded('grader', fn () => [
                'id' => $this->grader->id,
                'name' => $this->grader->name,
                'email' => $this->grader->email,
            ]),
            'overrider' => $this->whenLoaded('overrider', fn () => [
                'id' => $this->overrider->id,
                'name' => $this->overrider->name,
                'email' => $this->overrider->email,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
