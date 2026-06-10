<?php

namespace App\Http\Requests;

use App\Models\StudentTag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'tag' => ['required', 'string', 'max:100', Rule::in(StudentTag::TAGS)],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
        ];
    }
}
