<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceRateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'track_id' => ['nullable', 'integer', 'exists:tracks,id'],
            'cohort_id' => ['nullable', 'integer', 'exists:cohorts,id'],
        ]);

        $query = AttendanceRecord::query();

        if (! empty($validated['track_id'])) {
            $query->where('track_id', $validated['track_id']);
        }

        if (! empty($validated['cohort_id'])) {
            $query->whereHas('session.engagement', function ($q) use ($validated) {
                $q->where('cohort_id', $validated['cohort_id']);
            });
        }

        $total = (clone $query)->count();
        $present = (clone $query)->whereIn('status', ['present', 'arrived', 'left', 'completed'])->count();

        $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return response()->json([
            'data' => [
                'attendance_rate' => $rate,
                'present' => $present,
                'total' => $total,
            ],
        ]);
    }
}
