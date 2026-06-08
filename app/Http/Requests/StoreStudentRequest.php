<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'national_id' => 'required|unique:students,national_id|digits:14',
        ];
    }
}
