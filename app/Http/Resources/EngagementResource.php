<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngagementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cohort_id' => $this->cohort_id,
            'course_id' => $this->course_id,
            'instructor_id' => $this->instructor_id,
            'type' => $this->type,
            'date_range_start' => $this->date_range_start,
            'date_range_end' => $this->date_range_end,
            'scheduled_hours' => $this->scheduled_hours,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
