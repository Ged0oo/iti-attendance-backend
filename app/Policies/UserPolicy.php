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
    public function update(User $authUser, User $targetUser): bool
    {
        $targetRole = $targetUser->roles->first()?->name ?? $targetUser->role;

        // A branch_manager can only be updated by another branch_manager
        if ($targetRole === 'branch_manager') {
            return $authUser->hasRole('branch_manager');
        }

        // For all other roles, require the corresponding "create" permission
        $requiredPermission = match ($targetRole) {
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
