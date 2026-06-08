<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cohort_id' => $this->cohort_id,
            'user_id' => $this->user_id,
            'compensation_type' => $this->compensation_type,
            'scheduled_hours' => $this->scheduled_hours,
            'delivered_hours' => $this->delivered_hours,
            'hourly_rate' => $this->hourly_rate,
            'fixed_salary' => $this->fixed_salary,
            'total_amount' => $this->total_amount,
            'billing_period_start' => $this->billing_period_start,
            'billing_period_end' => $this->billing_period_end,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
