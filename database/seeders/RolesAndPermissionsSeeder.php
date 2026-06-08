<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie's permission cache before seeding.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Account provisioning
            'users:create-track-admin',
            'users:create-instructor',
            'users:create-student',

            // Cohort management
            'cohorts:create',
            'cohorts:assign-track-admin',

            // Track Admin operations
            'cohorts:configure',
            'engagements:manage',
            'excuses:approve',
            'grades:enter-final',
            'grades:override',
            'announcements:post-anytime',

            // Instructor operations
            'attendance:record',
            'grades:enter-lab',
            'students:add-tags-notes',
            'announcements:post-during-engagement',

            // Student operations
            'assignments:submit',
            'excuses:submit',

            // Viewing scopes
            'analytics:view-branch',
            'analytics:view-track',
            'analytics:view-group',
            'analytics:view-own',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ---------------------------------------------------------------
        // Create roles and assign permissions.
        // ---------------------------------------------------------------

        $branchManager = Role::firstOrCreate(['name' => 'branch_manager']);
        $branchManager->syncPermissions([
            'users:create-track-admin',
            'cohorts:create',
            'cohorts:assign-track-admin',
            'analytics:view-branch',
        ]);

        $trackAdmin = Role::firstOrCreate(['name' => 'track_admin']);
        $trackAdmin->syncPermissions([
            'users:create-instructor',
            'users:create-student',
            'cohorts:configure',
            'engagements:manage',
            'excuses:approve',
            'grades:enter-final',
            'grades:override',
            'announcements:post-anytime',
            'analytics:view-track',
        ]);

        $instructor = Role::firstOrCreate(['name' => 'instructor']);
        $instructor->syncPermissions([
            'attendance:record',
            'grades:enter-lab',
            'students:add-tags-notes',
            'announcements:post-during-engagement',
            'analytics:view-group',
        ]);

        $student = Role::firstOrCreate(['name' => 'student']);
        $student->syncPermissions([
            'assignments:submit',
            'excuses:submit',
            'analytics:view-own',
        ]);

        $this->command->info('Roles and permissions seeded.');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['branch_manager', $branchManager->permissions()->count()],
                ['track_admin', $trackAdmin->permissions()->count()],
                ['instructor', $instructor->permissions()->count()],
                ['student', $student->permissions()->count()],
            ]
        );
    }
}
