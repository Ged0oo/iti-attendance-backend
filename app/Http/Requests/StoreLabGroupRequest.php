<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabGroupRequest extends FormRequest
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
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            // the instructor here is a user, not a row in the instructors table
            'instructor_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
