<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstructorRequest;
use App\Http\Requests\UpdateInstructorRequest;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class InstructorController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return InstructorResource::collection(Instructor::query()->with('user')->latest()->paginate(20));
    }

    public function store(StoreInstructorRequest $request): JsonResponse
    {
        $instructor = Instructor::create($request->validated());

        return (new InstructorResource($instructor))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Instructor $instructor): InstructorResource
    {
        return new InstructorResource($instructor);
    }

    public function update(UpdateInstructorRequest $request, Instructor $instructor): InstructorResource
    {
        $instructor->update($request->validated());

        return new InstructorResource($instructor);
    }

    public function destroy(Instructor $instructor): Response
    {
        $instructor->delete();

        return response()->noContent();
    }
}
