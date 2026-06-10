<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'tag' => ['required', 'string', 'max:100'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ];
    }
}