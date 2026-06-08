<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeComponentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'name' => $this->name,
            'type' => $this->type,
            'weight' => $this->weight,
            'raw_max' => $this->raw_max,
            'is_deliverable' => $this->is_deliverable,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
