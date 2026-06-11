<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Target user is injected into the route: /users/{user}
        $targetUser = $this->route('user');

        if (!$targetUser) {
            return false;
        }

        return Gate::allows('update', $targetUser);
    }

    public function rules(): array
    {
        $targetUser = $this->route('user');

        return [
            'name'       => ['sometimes', 'required', 'string', 'max:255'],
            'email'      => [
                'sometimes', 
                'required', 
                'email', 
                Rule::unique('users', 'email')->ignore($targetUser->id),
            ],
            'role'       => ['sometimes', 'required', 'in:track_admin,instructor,student'],
            'expires_at' => ['sometimes', 'required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role must be one of: track_admin, instructor, student.',
        ];
    }
}
