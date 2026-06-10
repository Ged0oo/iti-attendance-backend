<?php

namespace App\Http\Requests;

use App\Models\AssignmentSubmission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentSubmissionRequest extends FormRequest
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
            'submission_type' => ['required', Rule::in(AssignmentSubmission::SUBMISSION_TYPES)],

            'url' => ['required_if:submission_type,url', 'nullable', 'url', 'max:2048'],
            'file' => ['required_if:submission_type,file', 'nullable', 'file', 'max:10240'],

            'submitted_at' => ['nullable', 'date'],
        ];
    }
}