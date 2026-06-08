<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEngagementRequest;
use App\Http\Requests\UpdateEngagementRequest;
use App\Http\Resources\EngagementResource;
use App\Models\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EngagementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $engagements = Engagement::query()
            ->when($request->integer('cohort_id'), fn ($q, $id) => $q->where('cohort_id', $id))
            ->when($request->integer('instructor_id'), fn ($q, $id) => $q->where('instructor_id', $id))
            ->latest()
            ->paginate(20);

        return EngagementResource::collection($engagements);
    }

    public function store(StoreEngagementRequest $request): JsonResponse
    {
        $engagement = Engagement::create($request->validated());

        return (new EngagementResource($engagement))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Engagement $engagement): EngagementResource
    {
        return new EngagementResource($engagement);
    }

    public function update(UpdateEngagementRequest $request, Engagement $engagement): EngagementResource
    {
        $engagement->update($request->validated());

        return new EngagementResource($engagement);
    }

    public function destroy(Engagement $engagement): Response
    {
        $engagement->delete();

        return response()->noContent();
    }
}
