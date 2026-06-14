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
        // allow users to update their own account
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        $targetRole = $targetUser->roles->first()?->name ?? $targetUser->role;
        $authRole   = $authUser->roles->first()?->name ?? $authUser->role;

        // prevent peer updates
        if ($authRole === $targetRole) {
            return false;
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
