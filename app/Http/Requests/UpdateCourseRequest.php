<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $course = $this->route('course');
        $cohortId = $this->input('cohort_id', $course?->cohort_id);

        return [
            'cohort_id' => ['sometimes', 'integer', 'exists:cohorts,id'],
            // keep the name unique within its cohort, ignoring this course itself
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('courses')
                    ->where(fn ($query) => $query->where('cohort_id', $cohortId))
                    ->ignore($course),
            ],
            'description' => ['nullable', 'string'],
            'max_score' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
