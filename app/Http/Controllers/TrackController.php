<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('branch_manager')) {
            $tracks = Track::with('admins.user')->get();
        } else {
            $tracks = Track::whereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->with('admins.user')->get();
        }

        return response()->json($tracks);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'description' => 'nullable|string',
        ]);

        $track = Track::create($data);

        return response()->json($track, 201);
    }

    public function show(Track $track): JsonResponse
    {
        $track->load('admins.user', 'cohorts');
        return response()->json($track);
    }

    public function update(Request $request, Track $track): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $track->update($data);

        return response()->json($track);
    }

    public function destroy(Track $track): JsonResponse
    {
        $track->delete();
        return response()->json(['message' => 'Track deleted.']);
    }
}
