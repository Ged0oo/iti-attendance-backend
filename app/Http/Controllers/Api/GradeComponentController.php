<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradeComponentRequest;
use App\Http\Requests\UpdateGradeComponentRequest;
use App\Http\Resources\GradeComponentResource;
use App\Models\GradeComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GradeComponentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $components = GradeComponent::query()
            ->when($request->integer('course_id'), fn ($q, $id) => $q->where('course_id', $id))
            ->latest()
            ->paginate(20);

        return GradeComponentResource::collection($components);
    }

    public function store(StoreGradeComponentRequest $request): JsonResponse
    {
        $component = GradeComponent::create($request->validated());

        return (new GradeComponentResource($component))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(GradeComponent $gradeComponent): GradeComponentResource
    {
        return new GradeComponentResource($gradeComponent);
    }

    public function update(UpdateGradeComponentRequest $request, GradeComponent $gradeComponent): GradeComponentResource
    {
        $gradeComponent->update($request->validated());

        return new GradeComponentResource($gradeComponent);
    }

    public function destroy(GradeComponent $gradeComponent): Response
    {
        $gradeComponent->delete();

        return response()->noContent();
    }
}
