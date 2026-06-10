<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'note' => ['required', 'string', 'min:5', 'max:5000'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ];
    }
}