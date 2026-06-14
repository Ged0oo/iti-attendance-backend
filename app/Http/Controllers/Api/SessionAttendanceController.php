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
        $user = auth()->user();
        if ($user->hasRole('instructor') && !$user->hasAnyRole(['track_admin', 'branch_manager'])) {
            if ($session->engagement->instructor_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
        }

        $records = $session->attendanceRecords()->with('student.user')->get();

        return response()->json([
            'session_closed' => !is_null($session->closed_at),
            'data' => $records->map(fn($record) => [
                'id' => $record->id,
                'status' => $record->status,
                'arrived_at' => $record->arrived_at?->toIso8601String(),
                'left_at' => $record->left_at?->toIso8601String(),
                'student' => [
                    'user' => [
                        'name' => $record->student?->user?->name ?? 'Unknown'
                    ]
                ]
            ])
        ]);
    }

    /**
     * Close the session and mark unscanned students as absent.
     */
    public function close(Session $session): JsonResponse
    {
        $user = auth()->user();
        if ($user->hasRole('instructor') && !$user->hasAnyRole(['track_admin', 'branch_manager'])) {
            if ($session->engagement->instructor_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }
        }

        if($session->closed_at) {
            return response()->json(['message' => 'Session is already closed.'], 400);
        }

        $absentCount = $this->attendanceService->closeSession($session);

        return response()->json([
            'message' => "Session closed. Marked {$absentCount} students as absent."
        ]);
    }
}
