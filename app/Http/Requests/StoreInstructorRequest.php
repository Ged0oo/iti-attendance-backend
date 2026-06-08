<?php

namespace App\Http\Requests;

use App\Models\Instructor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstructorRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:instructors,user_id'],
            'compensation_type' => ['required', Rule::in(Instructor::TYPES)],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'fixed_salary' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
