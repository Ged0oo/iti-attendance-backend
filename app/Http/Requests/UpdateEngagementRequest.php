<?php

namespace App\Http\Requests;

use App\Models\Engagement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEngagementRequest extends FormRequest
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
        return [
            'cohort_id' => ['sometimes', 'integer', 'exists:cohorts,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'instructor_id' => ['sometimes', 'integer', 'exists:users,id'],
            'type' => ['sometimes', Rule::in(Engagement::TYPES)],
            'date_range_start' => ['sometimes', 'date'],
            'date_range_end' => ['sometimes', 'date', 'after_or_equal:date_range_start'],
            'scheduled_hours' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(Engagement::STATUSES)],
        ];
    }
}
