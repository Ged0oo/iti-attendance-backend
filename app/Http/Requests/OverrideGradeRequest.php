<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OverrideGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'override_value' => ['required', 'numeric', 'min:0'],
            'override_note' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }
}