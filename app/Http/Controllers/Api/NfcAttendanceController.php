<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcTag;
use App\Models\Session;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NfcAttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService){}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'serial_number' => 'required|string|unique:nfc_tags,serial_number',
        ]);

        // revoke any existing tags for this student
        NfcTag::where('student_id', $validated['student_id'])
              ->where('status', 'active')
              ->update(['status' => 'revoked']);

        // register new tag
        $nfcTag = NfcTag::create($validated);

        return response()->json([
            'message' => 'NFC tag registered successfully.',
            'data' => $nfcTag
        ], 201);
    }

    public function reportLost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|exists:nfc_tags,serial_number',
        ]);

        $nfcTag = NfcTag::where('serial_number', $validated['serial_number'])->first();
        $nfcTag->update(['status' => 'lost']);

        return response()->json([
            'message' => 'NFC tag has been reported as lost.'
        ], 200);
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'serial_number' => 'required|string',
        ]);

        $nfcTag = NfcTag::where('serial_number', $validated['serial_number'])->first();

        if(!$nfcTag) {
            return response()->json([
                'message' => 'NFC tag not recognized. Please register your tag first.'
            ], 404);
        }

        if ($nfcTag->status !== 'active') {
            return response()->json([
                'message' => 'This NFC tag is not active. Please contact support.'
            ], 403);
        }

        $session = Session::findOrFail($validated['session_id']);

        if (!\Carbon\Carbon::parse($session->date)->isToday()) {
            return response()->json(['message' => 'This session is not active today.'], 403);
        }

        if ($session->closed_at) {
            return response()->json(['message' => 'Session is closed; check-in is not allowed'], 422);
        }

        // Reuse the exact same business logic from Phase 2
        $result = $this->attendanceService->processScan($session, $nfcTag->student);

        return response()->json([
            'message' => "Successfully checked {$result['status']}",
            'data'    => $result
        ], 200);
    }
}
