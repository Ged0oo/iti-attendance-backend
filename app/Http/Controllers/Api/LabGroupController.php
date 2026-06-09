<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLabGroupRequest;
use App\Http\Requests\UpdateLabGroupRequest;
use App\Http\Resources\LabGroupResource;
use App\Models\LabGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LabGroupController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $cohortId = $request->route('cohort') ?? $request->integer('cohort_id');

        $groups = LabGroup::query()
            ->when($cohortId, fn ($q) => $q->where('cohort_id', $cohortId))
            ->when($request->integer('course_id'), fn ($q, $id) => $q->where('course_id', $id))
            ->latest()
            ->paginate(20);

        return LabGroupResource::collection($groups);
    }

    public function store(StoreLabGroupRequest $request): JsonResponse
    {
        $group = LabGroup::create($request->validated());

        return (new LabGroupResource($group))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(LabGroup $labGroup): LabGroupResource
    {
        return new LabGroupResource($labGroup);
    }

    public function update(UpdateLabGroupRequest $request, LabGroup $labGroup): LabGroupResource
    {
        $labGroup->update($request->validated());

        return new LabGroupResource($labGroup);
    }

    public function destroy(LabGroup $labGroup): Response
    {
        $labGroup->delete();

        return response()->noContent();
    }
}
