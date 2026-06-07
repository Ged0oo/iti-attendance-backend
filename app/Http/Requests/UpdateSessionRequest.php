<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionRequest extends FormRequest
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
            'engagement_id' => ['sometimes', 'integer', 'exists:engagements,id'],
            'date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i,H:i:s'],
            'end_time' => ['sometimes', 'date_format:H:i,H:i:s'],
            'scheduled_hours' => ['sometimes', 'numeric', 'min:0'],
            'is_delivered' => ['boolean'],
            'qr_code' => ['sometimes', 'string', 'unique:sessions,qr_code,'.$this->route('session')?->id],
        ];
    }
}
