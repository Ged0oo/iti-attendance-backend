<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delta' => $this->delta,
            'reason' => $this->reason,
            'balance_after' => $this->balance_after,
            'created_at' => $this->created_at,
            'attendance_record_id' => $this->attendance_record_id,
        ];
    }
}
