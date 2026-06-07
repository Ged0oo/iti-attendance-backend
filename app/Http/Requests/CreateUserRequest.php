<?php
namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->input('role');

        if (empty($role)) {
            return false;
        }

        return Gate::allows('create', [User::class, $role]);
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8'],
            'role'       => ['required', 'in:track_admin,instructor,student'],
            'expires_at' => ['required', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in'          => 'Role must be one of: track_admin, instructor,
  student.',
            'expires_at.after' => 'The expiry date must be in the future.',
        ];
    }
}
