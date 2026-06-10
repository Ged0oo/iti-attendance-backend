<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;

class SessionAttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService){}
    
    /**
     * List attendance for a specific session.
     */
    public function index(Session $session): JsonResponse
    {
        $records = $session->attendanceRecords()->with('student.user')->get();

        return response()->json([
            'data' => $records->map(fn($record) => [
                'student_name' => $record->student->user->name,
                'status' => $record->status,
                'arrived_at' => $record->arrived_at?->format('h:i A'),
                'left_at' => $record->left_at?->format('h:i A'),
            ])
        ]);
    }

    /**
     * Close the session and mark unscanned students as absent.
     */
    public function close(Session $session): JsonResponse
    {
        if($session->closed_at) {
            return response()->json(['message' => 'Session is already closed.'], 400);
        }

        $absentCount = $this->attendanceService->closeSession($session);

        return response()->json([
            'message' => "Session closed. Marked {$absentCount} students as absent."
        ]);
    }
}
