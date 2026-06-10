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
            // SEC-4: max 1MB, PDF or image only
            'file' => ['required_if:submission_type,file', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],

            'submitted_at' => ['nullable', 'date'],
        ];
    }
}