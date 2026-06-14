<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CohortController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('branch_manager')) {
            $cohorts = Cohort::with('track', 'creator')->paginate(20);
        } else {
            $trackIds = $user->trackAdmins()->pluck('track_id');
            $cohorts = Cohort::whereIn('track_id', $trackIds)
                ->with('track', 'creator')
                ->paginate(20);
        }

        return response()->json($cohorts);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

      
        $hasActive = Cohort::where('track_id', $data['track_id'])
            ->where('status', '!=', 'rolled_up')
            ->exists();

        if ($hasActive) {
            return response()->json([
                'message' => 'This track already has an active cohort.',
            ], 422);
        }

        $data['created_by'] = $request->user()->id;
        $data['status'] = 'open';

        $cohort = Cohort::create($data);
        $cohort->load('track', 'creator');

        return response()->json($cohort, 201);
    }

    public function show(Cohort $cohort): JsonResponse
    {
        $cohort->load('track', 'creator', 'announcements');
        return response()->json($cohort);
    }

    public function update(Request $request, Cohort $cohort): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
        ]);

        $cohort->update($data);

        return response()->json($cohort);
    }

    public function destroy(Cohort $cohort): JsonResponse
    {
        // do not wipe a cohort that still has enrolled students (cascade would take them too)
        if (\App\Models\Student::where('cohort_id', $cohort->id)->exists()) {
            return response()->json(['message' => 'Cannot delete a cohort that still has students.'], 422);
        }

        $cohort->delete();
        return response()->json(['message' => 'Cohort deleted.']);
    }

    public function transition(Request $request, Cohort $cohort): JsonResponse
    {
        $user = $request->user();
        if ($user->hasRole('track_admin')) {
            if (!$user->trackAdmins()->where('track_id', $cohort->track_id)->exists()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        $data = $request->validate([
            'status' => 'required|in:open,configuring,delivering,participating,rolled_up',
        ]);

        $allowed = [
            'open' => 'configuring',
            'configuring' => 'delivering',
            'delivering' => 'participating',
            'participating' => 'rolled_up',
        ];

        $current = $cohort->status;
        $next = $data['status'];

        if (!isset($allowed[$current]) || $allowed[$current] !== $next) {
            return response()->json([
                'message' => "Cannot transition from '{$current}' to '{$next}'.",
            ], 422);
        }

        $cohort->update(['status' => $next]);

        return response()->json($cohort);
    }
}
