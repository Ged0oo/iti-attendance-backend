<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isExpired()) {
            Auth::guard('web')->logout();

            return response()->json([
                'message' => 'Your account has expired. Please contact your administrator.',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->getRoleNames()->first(),
                'expires_at' => $user->expires_at,
            ],
        ]);
    }

    /**
     * Destroy an authenticated session (revoke the current token).
     */
    public function destroy(Request $request): JsonResponse
    {

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}
