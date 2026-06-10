<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lab_group_id' => ['sometimes', 'nullable', 'integer', 'exists:lab_groups,id'],
            'raw_score' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}