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

        if(now()->timestamp > $payload['expires_at']) {
            return response()->json(['message' => 'QR code has expired. Please refresh and try again.'], 400);
        }

        $sessionId = $payload['session_id'];
        $session = Session::findOrFail($sessionId);

        // Security check: Parse the string into a Carbon object first
        if (!\Carbon\Carbon::parse($session->date)->isToday()) {
            return response()->json(['message' => 'This session is not active today.'], 403);
        }

        // $student = auth()->user()->student;
        $student = \App\Models\Student::where('user_id', auth()->id())->firstOrFail();

        $result = $this->attendanceService->processScan($session, $student);

        return response()->json([
            'message' => "Successfully checked {$result['status']}",
            'data'    => $result
        ], 200);
    }
}
