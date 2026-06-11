<?php

namespace App\Http\Controllers\Api;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;


class QrCodeController extends Controller
{
    public function generate(Session $session): JsonResponse
    {
        $user = auth()->user();
        if ($user->hasRole('instructor') && !$user->hasAnyRole(['track_admin', 'branch_manager'])) {
            if ($session->engagement->instructor_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
        }

        $validSeconds = 15;

        $payload = [
            'session_id' => $session->id,
            'expires_at' => now()->addSeconds($validSeconds)->timestamp,
        ];

        $secureToken = Crypt::encryptString(json_encode($payload));

        return response()->json([
            'qr_payload' => $secureToken,
            'expires_in' => $validSeconds,
            'refresh_at' => now()->addSeconds($validSeconds - 2)->format('h:i:s A'),
        ]);
    }
}
