<?php
namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Allowed chains:
     *   branch_manager → can create → track_admin
     *   track_admin    → can create → instructor, student
     *   instructor     → cannot create anyone
     *   student        → cannot create anyone
     */
    public function create(User $authUser, string $roleToAssign): bool
    {
        $requiredPermission = match ($roleToAssign) {
            'track_admin' => 'users:create-track-admin',
            'instructor'  => 'users:create-instructor',
            'student'     => 'users:create-student',
            default       => null,
        };

        if (is_null($requiredPermission)) {
            return false;
        }

        return $authUser->hasPermissionTo($requiredPermission);
    }
}
