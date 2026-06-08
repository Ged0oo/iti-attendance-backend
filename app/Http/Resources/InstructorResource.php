<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'compensation_type' => $this->compensation_type,
            'hourly_rate' => $this->hourly_rate,
            'fixed_salary' => $this->fixed_salary,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
