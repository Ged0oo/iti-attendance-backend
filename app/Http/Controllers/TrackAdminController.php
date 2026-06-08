<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackAdminController extends Controller
{
    public function index(Track $track): JsonResponse
    {
        $admins = $track->admins()->with('user')->get();
        return response()->json($admins);
    }

    public function store(Request $request, Track $track): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $exists = TrackAdmin::where('user_id', $data['user_id'])
            ->where('track_id', $track->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'User already assigned to this track.'], 422);
        }

        $admin = TrackAdmin::create([
            'user_id' => $data['user_id'],
            'track_id' => $track->id,
        ]);

        $admin->load('user');

        return response()->json($admin, 201);
    }

    public function destroy(Track $track, $userId): JsonResponse
    {
        $deleted = TrackAdmin::where('track_id', $track->id)
            ->where('user_id', $userId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Assignment not found.'], 404);
        }

        return response()->json(['message' => 'Admin removed from track.']);
    }
}
