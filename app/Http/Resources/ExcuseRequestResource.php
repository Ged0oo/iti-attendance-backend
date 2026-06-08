<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ExcuseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_name' => $this->student->user->name ?? null,
            'attendance_record_id' => $this->attendance_record_id,
            'reason' => $this->notes, // Mapped 'reason' to 'notes' column
            'attachment_url' => $this->attachment_path ? Storage::url($this->attachment_path) : null,
            'status' => $this->status,
            'reviewed_by_name' => $this->reviewer->name ?? null,
            'reviewed_at' => $this->reviewed_at,
            'review_note' => $this->review_note ?? null, // Mapped to non-existent property per user prompt/schema limits
        ];
    }
}
