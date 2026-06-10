<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['sometimes', 'required', 'string', 'min:5', 'max:5000'],
            'course_id' => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
        ];
    }
}
