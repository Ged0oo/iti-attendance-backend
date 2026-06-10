<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentNoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'note' => $this->note,
            'written_by' => $this->written_by,
            'course_id' => $this->course_id,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'user_id' => $this->student->user_id,
                'national_id' => $this->student->national_id,
            ]),
            'written_by_user' => $this->whenLoaded('writtenBy', fn () => [
                'id' => $this->writtenBy->id,
                'name' => $this->writtenBy->name,
                'email' => $this->writtenBy->email,
            ]),
            'course' => new CourseResource($this->whenLoaded('course')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
