<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanAttendanceRequest;
use App\Services\AttendanceService;
use App\Models\Session;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService){}

    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        $sessionId = $request->validated('session_qr_code'); 
        
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
