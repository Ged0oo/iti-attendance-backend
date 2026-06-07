<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSessionRequest;
use App\Http\Requests\UpdateSessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Engagement;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $sessions = Session::query()
            ->when($request->integer('engagement_id'), fn ($q, $id) => $q->where('engagement_id', $id))
            ->latest('date')
            ->paginate(20);

        return SessionResource::collection($sessions);
    }

    public function store(StoreSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['qr_code'] = $data['qr_code'] ?? (string) Str::uuid();

        $session = Session::create($data);

        return (new SessionResource($session))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Session $session): SessionResource
    {
        return new SessionResource($session);
    }

    public function update(UpdateSessionRequest $request, Session $session): SessionResource
    {
        $session->update($request->validated());

        return new SessionResource($session);
    }

    public function destroy(Session $session): Response
    {
        $session->delete();

        return response()->noContent();
    }

    // Spin up one session per day across the engagement's date range.
    public function generate(Engagement $engagement, Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'date_format:H:i,H:i:s'],
            'scheduled_hours' => ['required', 'numeric', 'min:0'],
        ]);

        $day = Carbon::parse($engagement->date_range_start);
        $end = Carbon::parse($engagement->date_range_end);
        $created = collect();

        while ($day->lte($end)) {
            $created->push(Session::create([
                'engagement_id' => $engagement->id,
                'date' => $day->toDateString(),
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'scheduled_hours' => $data['scheduled_hours'],
                'is_delivered' => false,
                'qr_code' => (string) Str::uuid(),
            ]));
            $day->addDay();
        }

        return response()->json(
            ['data' => SessionResource::collection($created)],
            Response::HTTP_CREATED
        );
    }

    // Mark a session as actually delivered (this is what billing counts).
    public function deliver(Session $session): SessionResource
    {
        $session->update(['is_delivered' => true]);

        return new SessionResource($session);
    }
}
