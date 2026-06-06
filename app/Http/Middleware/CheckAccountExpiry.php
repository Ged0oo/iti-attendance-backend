<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountExpiry
{
    /**
     * Reject any request from a user whose account has expired.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isExpired()) {
            // Revoke the token so the client can't keep using it
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Your account has expired. Please contact your administrator.',
            ], Response::HTTP_FORBIDDEN); // 403
        }

        return $next($request);
    }
}
