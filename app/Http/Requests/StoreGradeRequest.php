<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'grade_component_id' => ['required', 'integer', 'exists:grade_components,id'],
            'lab_group_id' => ['nullable', 'integer', 'exists:lab_groups,id'],
            'raw_score' => ['required', 'numeric', 'min:0'],
        ];
    }
}