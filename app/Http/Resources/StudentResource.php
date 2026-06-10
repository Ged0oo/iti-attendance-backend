<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
            ],
            'cohort_id' => $this->cohort_id,
            'lab_group_id' => $this->lab_group_id,
            'national_id' => $this->national_id,
            'is_at_risk' => $this->is_at_risk,
            'ledger_balance' => $this->whenLoaded('ledger', function () {
                return $this->ledger->balance;
            }),
        ];
    }
}
