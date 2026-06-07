<?php

namespace App\Http\Requests;

use App\Models\Instructor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstructorRequest extends FormRequest
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
            'compensation_type' => ['sometimes', Rule::in(Instructor::TYPES)],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'fixed_salary' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
