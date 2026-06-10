<?php

namespace App\Http\Requests;

use App\Models\StudentTag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tag' => ['sometimes', 'required', 'string', 'max:100', Rule::in(StudentTag::TAGS)],
            'course_id' => ['sometimes', 'nullable', 'integer', 'exists:courses,id'],
        ];
    }
}
