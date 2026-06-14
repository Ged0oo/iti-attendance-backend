<?php
namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();
        $user  = $request->user();

        if ($user->hasRole('branch_manager')) {
            $query->where(function ($q) {
                $q->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'branch_manager');
                });
            });

        } elseif ($user->hasRole('track_admin')) {
            $query->where(function ($q) {
                $q->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['branch_manager', 'track_admin']);
                });
            });
        }

        if ($request->has('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->through(fn($user) => [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->roles->first()?->name ?? $user->role,
            'student_id'    => $user->student ? $user->student->id : null,
            'instructor_id' => $user->instructor ? $user->instructor->id : null,
            'expires_at'    => $user->expires_at,
        ]);

        return response()->json($users);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make(Str::random(32)),
                'expires_at' => $request->expires_at,
                'role'       => $request->role,
            ]);

            $user->assignRole($request->role);

            return $user;
        });

        // Generate a password-reset token using Laravel's built-in broker.
        $token = Password::broker('activation')->createToken($user);

        // Send the welcome email with the set-password link.
        $user->notify(new WelcomeNotification($token));

        return response()->json([
            'message' => 'User created successfully. A welcome email has been sent.',
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

        $emailChanged = $request->has('email') && $request->email !== $user->email;
        $notActivated = is_null($user->email_verified_at);

        $user = DB::transaction(function () use ($request, $user) {
            $updateData = $request->only(['name', 'email', 'expires_at']);
            if ($request->has('role')) {
                $updateData['role'] = $request->role;
                $user->syncRoles($request->role);
            }
            $user->update($updateData);

            return $user;
        });

        if ($emailChanged && $notActivated) {
            Password::broker('activation')->deleteToken($user);

            $token = Password::broker('activation')->createToken($user);
            $user->notify(new WelcomeNotification($token));
        }

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
