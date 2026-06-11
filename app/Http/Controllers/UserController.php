<?php
namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if (!$request->user()->hasRole('branch_manager')) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'branch_manager');
            });
        }

        if ($request->has('role')) {
            $query->where(function ($q) use ($request) {
                $q->role($request->role)->orWhere('role', $request->role);
            });
        }

        $users = $query->paginate(20)->through(fn ($user) => [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->roles->first()?->name ?? $user->role,
            'student_id'    => $user->student ? $user->student->id : null,
            'instructor_id' => $user->instructor ? $user->instructor->id : null,
        ]);

        return response()->json($users);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'expires_at' => $request->expires_at,
            ]);

            $user->assignRole($request->role);

            return $user;
        });

        return response()->json([
            'message' => 'User created successfully.',
            'user'    => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $user->roles->first()?->name ?? $user->role,
                'student_id'    => $user->student ? $user->student->id : null,
                'instructor_id' => $user->instructor ? $user->instructor->id : null,
                'expires_at'    => $user->expires_at,
            ],
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = DB::transaction(function () use ($request, $user) {
            $user->update($request->only(['name', 'email', 'expires_at']));

            if ($request->has('role')) {
                $user->syncRoles($request->role);
            }

            return $user;
        });

        return response()->json([
            'message' => 'User updated successfully.',
            'user'    => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $user->roles->first()?->name ?? $user->role,
                'student_id'    => $user->student ? $user->student->id : null,
                'instructor_id' => $user->instructor ? $user->instructor->id : null,
                'expires_at'    => $user->expires_at,
            ],
        ]);
    }
}
