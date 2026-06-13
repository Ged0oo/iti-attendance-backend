<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'course_name' => $this->engagement?->course?->name ?? 'ITI Course',
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'scheduled_hours' => $this->scheduled_hours,
            'is_delivered' => $this->is_delivered,
            'qr_code' => $this->qr_code,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
