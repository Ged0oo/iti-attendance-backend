<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DevAccountSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Branch Manager dev account ───────────────────────────────
        $devManager = User::firstOrCreate(
            ['email' => 'dev.manager@iti.gov.eg'],
            [
                'name'              => 'Dev Manager',
                'password'          => Hash::make('password'),
                'role'              => 'branch_manager',
                'expires_at'        => null,
                'email_verified_at' => now(),
            ]
        );
        $devManager->syncRoles('branch_manager');
        $devManager->update(['role' => 'branch_manager']);

        // ─── Track Admin dev account ───────────────────────────────────
        $devTrackAdmin = User::firstOrCreate(
            ['email' => 'dev.trackadmin@iti.gov.eg'],
            [
                'name'              => 'Dev Track Admin',
                'password'          => Hash::make('password'),
                'role'              => 'track_admin',
                'expires_at'        => '2027-12-31',
                'email_verified_at' => now(),
            ]
        );
        $devTrackAdmin->syncRoles('track_admin');
        $devTrackAdmin->update(['role' => 'track_admin']);

        // Assign to the first track in the DB
        $firstTrackId = DB::table('tracks')->orderBy('id')->value('id');
        if ($firstTrackId) {
            $pivotExists = DB::table('track_admins')
                ->where('user_id', $devTrackAdmin->id)
                ->where('track_id', $firstTrackId)
                ->exists();

            if (!$pivotExists) {
                DB::table('track_admins')->insert([
                    'user_id'    => $devTrackAdmin->id,
                    'track_id'   => $firstTrackId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ─── Instructor dev account ────────────────────────────────────
        $devInstructor = User::firstOrCreate(
            ['email' => 'dev.instructor@iti.gov.eg'],
            [
                'name'              => 'Dev Instructor',
                'password'          => Hash::make('password'),
                'role'              => 'instructor',
                'expires_at'        => '2027-12-31',
                'email_verified_at' => now(),
            ]
        );
        $devInstructor->syncRoles('instructor');
        $devInstructor->update(['role' => 'instructor']);

        Instructor::firstOrCreate(
            ['user_id' => $devInstructor->id],
            [
                'compensation_type' => 'internal',
                'hourly_rate'       => 100,
                'fixed_salary'      => 5000,
            ]
        );

        // ─── Student dev account ───────────────────────────────────────
        $devStudent = User::firstOrCreate(
            ['email' => 'dev.student@iti.gov.eg'],
            [
                'name'              => 'Dev Student',
                'password'          => Hash::make('password'),
                'role'              => 'student',
                'expires_at'        => '2026-12-31',
                'email_verified_at' => now(),
            ]
        );
        $devStudent->syncRoles('student');
        $devStudent->update(['role' => 'student']);

        // Link to the first Intake 46 cohort found
        $firstIntake46CohortId = DB::table('cohorts')
            ->where('name', 'Intake 46')
            ->where('status', 'delivering')
            ->orderBy('id')
            ->value('id');

        if ($firstIntake46CohortId) {
            Student::firstOrCreate(
                ['user_id' => $devStudent->id],
                [
                    'cohort_id'    => $firstIntake46CohortId,
                    'lab_group_id' => null,
                    'is_at_risk'   => false,
                ]
            );
        }

        // ─── Print credentials table ───────────────────────────────────
        $border  = '╔══════════════════╦══════════════════════════════════╦════════════╗';
        $header  = '║ Role             ║ Email                            ║ Password   ║';
        $divider = '╠══════════════════╬══════════════════════════════════╬════════════╣';
        $footer  = '╚══════════════════╩══════════════════════════════════╩════════════╝';

        $this->command->line('');
        $this->command->line($border);
        $this->command->line('║         DEV TEST ACCOUNTS — CREDENTIALS                         ║');
        $this->command->line($divider);
        $this->command->line($header);
        $this->command->line($divider);
        $this->command->line('║ Branch Mgr       ║ dev.manager@iti.gov.eg           ║ password   ║');
        $this->command->line('║ Track Admin      ║ dev.trackadmin@iti.gov.eg        ║ password   ║');
        $this->command->line('║ Instructor       ║ dev.instructor@iti.gov.eg        ║ password   ║');
        $this->command->line('║ Student          ║ dev.student@iti.gov.eg           ║ password   ║');
        $this->command->line($footer);
        $this->command->line('');
        $this->command->info('✔ DevAccountSeeder — 4 dev accounts ready.');
    }
}
