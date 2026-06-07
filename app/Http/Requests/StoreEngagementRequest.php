<?php

namespace App\Http\Requests;

use App\Models\Engagement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngagementRequest extends FormRequest
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
            'cohort_id' => ['required', 'integer', 'exists:cohorts,id'],
            // course is optional, business sessions have none
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'instructor_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', Rule::in(Engagement::TYPES)],
            'date_range_start' => ['required', 'date'],
            'date_range_end' => ['required', 'date', 'after_or_equal:date_range_start'],
            'scheduled_hours' => ['required', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(Engagement::STATUSES)],
        ];
    }
}
