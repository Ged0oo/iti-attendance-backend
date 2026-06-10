<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentSubmissionResource extends JsonResource
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
            'submission_type' => $this->submission_type,
            'url' => $this->url,
            'file_path' => $this->file_path,
            'submitted_at' => $this->submitted_at,
            'days_late' => $this->days_late,
            'late_penalty' => $this->late_penalty,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'user_id' => $this->student->user_id,
                'national_id' => $this->student->national_id,
            ]),
            'grade_component' => new GradeComponentResource($this->whenLoaded('gradeComponent')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
