<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // the route is already limited to track admin / branch manager
        return true;
    }

    /**
     * Only fields staff are allowed to change. user_id is immutable and
     * is_at_risk is managed by the system, so neither is accepted here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'cohort_id' => ['sometimes', 'exists:cohorts,id'],
            'lab_group_id' => ['sometimes', 'nullable', 'exists:lab_groups,id'],
            'national_id' => ['sometimes', 'digits:14', Rule::unique('students', 'national_id')->ignore($studentId)],
        ];
    }
}
