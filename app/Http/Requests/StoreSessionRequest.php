<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'engagement_id' => ['required', 'integer', 'exists:engagements,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'date_format:H:i,H:i:s'],
            'scheduled_hours' => ['required', 'numeric', 'min:0'],
            'is_delivered' => ['boolean'],
            // qr_code is generated for us if not supplied
            'qr_code' => ['nullable', 'string', 'unique:sessions,qr_code'],
        ];
    }
}
