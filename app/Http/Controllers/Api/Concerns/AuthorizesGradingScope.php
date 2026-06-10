<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AuthorizesGradingScope
{
    protected function scopeByStudentVisibility(Builder $query, Request $request, string $studentRelation = 'student'): Builder
    {
        if ($request->user()->hasRole('instructor')) {
            return $query->whereHas("{$studentRelation}.labGroup", function (Builder $labGroupQuery) use ($request) {
                $labGroupQuery->where('instructor_id', $request->user()->id);
            });
        }

        if ($request->user()->hasRole('track_admin')) {
            return $query->whereHas("{$studentRelation}.cohort.track.admins", function (Builder $adminQuery) use ($request) {
                $adminQuery->where('user_id', $request->user()->id);
            });
        }

        return $query;
    }

    protected function authorizeStudentVisibility(Request $request, Student $student, string $message): void
    {
        if ($request->user()->hasRole('branch_manager')) {
            return;
        }

        if ($request->user()->hasRole('student')) {
            if ($student->user_id !== $request->user()->id) {
                abort(403, 'You can only access your own student data.');
            }

            return;
        }

        if ($request->user()->hasRole('instructor')) {
            $student->loadMissing('labGroup');

            if (! $student->labGroup || $student->labGroup->instructor_id !== $request->user()->id) {
                abort(403, $message);
            }

            return;
        }

        if ($request->user()->hasRole('track_admin')) {
            $student->loadMissing('cohort.track.admins');
            $track = $student->cohort?->track;

            if (! $track || ! $track->admins->contains('user_id', $request->user()->id)) {
                abort(403, 'You can only access students in your assigned track.');
            }
        }
    }
}
