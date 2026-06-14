<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['sometimes', 'required', 'string', 'max:255'],
            'email'                 => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'current_password'      => ['required_with:password', 'current_password'],
            'password'              => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required_with:password'],
        ];
    }
}
