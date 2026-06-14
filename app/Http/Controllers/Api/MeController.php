<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMeRequest;
use App\Http\Resources\InstructorResource;
use App\Http\Resources\StudentResource;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class MeController extends Controller
{
    public function profile(Request $request): JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->hasRole('student') && $user->student) {
            return new StudentResource($user->student->load('ledger'));
        }

        if ($user->hasRole('instructor') && $user->instructor) {
            return new InstructorResource($user->instructor->load('user'));
        }

        $role = $user->roles->first()?->name ?? $user->role;

        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $role,
            'student_id'    => $user->student ? $user->student->id : null,
            'instructor_id' => $user->instructor ? $user->instructor->id : null,
            'expires_at'    => $user->expires_at,
        ]);
    }

    public function update(UpdateMeRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $emailChanged = $request->has('email') && $request->email !== $user->email;
        $notActivated = is_null($user->email_verified_at);

        $user = DB::transaction(function () use ($request, $user) {
            $updateData = $request->only(['name', 'email']);
            if ($request->has('password')) {
                $updateData['password'] = bcrypt($request->password);
            }
            $user->update($updateData);

            return $user;
        });

        if ($emailChanged && $notActivated) {
            Password::broker('activation')->deleteToken($user);
            $token = Password::broker('activation')->createToken($user);
            $user->notify(new WelcomeNotification($token));
        }

        $role = $user->roles->first()?->name ?? $user->role;

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $role,
                'student_id'    => $user->student ? $user->student->id : null,
                'instructor_id' => $user->instructor ? $user->instructor->id : null,
                'expires_at'    => $user->expires_at,
            ],
        ]);
    }
}
