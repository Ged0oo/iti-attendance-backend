<?php

namespace App\Http\Requests;

use App\Models\GradeComponent;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradeComponentRequest extends FormRequest
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
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(GradeComponent::TYPES)],
            'weight' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'raw_max' => ['sometimes', 'numeric', 'min:0.01'],
            'is_deliverable' => ['boolean'],
        ];
    }

    /**
     * Same 100 cap, but ignore this component's own weight when adding up.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || ! $this->has('weight')) {
                return;
            }

            /** @var GradeComponent $component */
            $component = $this->route('grade_component');
            $courseId = $this->input('course_id', $component->course_id);

            $existing = GradeComponent::where('course_id', $courseId)
                ->where('id', '!=', $component->id)
                ->sum('weight');

            if (($existing + (float) $this->input('weight')) > 100) {
                $validator->errors()->add(
                    'weight',
                    "Total component weights for this course would exceed 100 (others: {$existing})."
                );
            }
        });
    }
}
