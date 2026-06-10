<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this->student_id,
            'balance' => $this->balance,
            'entries' => LedgerEntryResource::collection($this->whenLoaded('entries')),
        ];
    }
}
