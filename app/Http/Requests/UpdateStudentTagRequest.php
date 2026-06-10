<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tag' => ['sometimes', 'required', 'string', 'max:100'],
            'course_id' => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
        ];
    }
}
