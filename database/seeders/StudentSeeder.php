<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a cohort safely
        $cohortId = DB::table('cohorts')->value('id');
        if (!$cohortId) {
            $branchId = DB::table('branches')->value('id') ?? DB::table('branches')->insertGetId([
                'name' => 'Smart Village',
                'location' => 'Cairo',
            ]);

            $trackId = DB::table('tracks')->value('id') ?? DB::table('tracks')->insertGetId([
                'branch_id' => $branchId,
                'name' => 'Web Development',
                'description' => 'Full-stack web development track',
            ]);

            $cohortId = DB::table('cohorts')->insertGetId([
                'track_id' => $trackId,
                'name' => 'Intake 46',
                'status' => 'delivering',
                'start_date' => '2026-05-01',
                'end_date' => '2026-08-30',
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            $user = User::firstOrCreate(
                ['email' => "student{$i}@example.com"],
                [
                    'name' => "Student User $i",
                    'password' => Hash::make('password'),
                ]
            );

            // Assign the Spatie student role
            $user->syncRoles('student');

            // Find or create student profile
            Student::firstOrCreate(
                ['user_id' => $user->id],
                ['cohort_id' => $cohortId]
            );
        }
    }
}
