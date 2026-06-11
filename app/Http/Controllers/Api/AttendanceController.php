<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanAttendanceRequest;
use App\Services\AttendanceService;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService){}

    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        try {
            $jsonPayload = Crypt::decryptString($request->validated('session_qr_code'));
            $payload = json_decode($jsonPayload, true);
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Invalid QR code. Please try again.'], 400);
        }

        if (!is_array($payload) || !isset($payload['session_id']) || !isset($payload['expires_at'])) {
            return response()->json(['message' => 'Invalid QR payload.'], 422);
        }

        if(now()->timestamp > $payload['expires_at']) {
            return response()->json(['message' => 'QR code has expired. Please refresh and try again.'], 400);
        }

        $sessionId = $payload['session_id'];
        $session = Session::findOrFail($sessionId);

        // Security check: Parse the string into a Carbon object first
        if (!\Carbon\Carbon::parse($session->date)->isToday()) {
            return response()->json(['message' => 'This session is not active today.'], 403);
        }

        if ($session->closed_at) {
            return response()->json(['message' => 'Session is closed; check-in is not allowed'], 422);
        }

        // $student = auth()->user()->student;
        $student = \App\Models\Student::where('user_id', auth()->id())->firstOrFail();

        $result = $this->attendanceService->processScan($session, $student);

        if ($result['status'] === 'completed') {
            return response()->json([
                'message' => 'You have already checked out for this session.',
                'data'    => $result
            ], 409);
        }

        $session->load('engagement.course');

        return response()->json([
            'message' => "Successfully checked {$result['status']}",
            'data'    => array_merge($result, [
                'session' => [
                    'title' => $session->engagement?->course?->name ?? 'Session',
                    'date'  => $session->date?->toDateString() ?? now()->toDateString(),
                ]
            ])
        ], 200);
    }
}
