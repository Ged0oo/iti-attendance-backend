<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // access is already checked by the route middleware, so allow it here
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cohort_id' => ['required', 'integer', 'exists:cohorts,id'],
            // a cohort cannot have two courses with the same name
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses')->where(fn ($query) => $query->where('cohort_id', $this->input('cohort_id'))),
            ],
            'description' => ['nullable', 'string'],
            'max_score' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
