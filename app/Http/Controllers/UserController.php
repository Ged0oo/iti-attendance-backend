<?php
namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

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
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->getRoleNames()->first(),
                'expires_at' => $user->expires_at,
            ],
        ], 201);
    }
}
