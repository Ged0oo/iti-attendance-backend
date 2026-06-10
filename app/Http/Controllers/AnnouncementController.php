<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Cohort;
use App\Models\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Cohort $cohort): JsonResponse
    {
        $announcements = $cohort->announcements()
            ->with('poster')
            ->orderByDesc('published_at')
            ->get();

        return response()->json($announcements);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cohort_id' => 'required|exists:cohorts,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $user = $request->user();

        if ($user->hasRole('instructor')) {
            $hasActiveEngagement = Engagement::where('cohort_id', $data['cohort_id'])
                ->where('instructor_id', $user->id)
                ->where('date_range_start', '<=', now()->toDateString())
                ->where('date_range_end', '>=', now()->toDateString())
                ->exists();

            if (!$hasActiveEngagement) {
                return response()->json([
                    'message' => 'You can only post during your active engagement window.',
                ], 403);
            }

            $engagement = Engagement::where('cohort_id', $data['cohort_id'])
                ->where('instructor_id', $user->id)
                ->where('date_range_start', '<=', now()->toDateString())
                ->where('date_range_end', '>=', now()->toDateString())
                ->first();

            $data['engagement_id'] = $engagement->id;
        }

        $data['posted_by'] = $user->id;
        $data['published_at'] = now();

        $announcement = Announcement::create($data);
        $announcement->load('poster');

        return response()->json($announcement, 201);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        $announcement->load('poster', 'cohort');
        return response()->json($announcement);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        if ($announcement->posted_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        $announcement->update($data);

        return response()->json($announcement);
    }

    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        if ($announcement->posted_by !== $request->user()->id && !$request->user()->hasRole('track_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted.']);
    }
}
