<?php

namespace App\Http\Requests;

use App\Models\GradeComponent;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeComponentRequest extends FormRequest
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
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(GradeComponent::TYPES)],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'raw_max' => ['required', 'numeric', 'min:0.01'],
            'is_deliverable' => ['boolean'],
        ];
    }

    /**
     * Make sure all the component weights for a course never go over 100.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $existing = GradeComponent::where('course_id', $this->input('course_id'))->sum('weight');

            if (($existing + (float) $this->input('weight')) > 100) {
                $validator->errors()->add(
                    'weight',
                    "Total component weights for this course would exceed 100 (current: {$existing})."
                );
            }
        });
    }
}
