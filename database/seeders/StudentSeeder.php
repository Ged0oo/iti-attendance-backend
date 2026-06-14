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
        $firstNames = [
            'Mohamed', 'Ahmed', 'Omar', 'Ali', 'Hassan', 'Ibrahim', 'Khaled', 'Youssef',
            'Mahmoud', 'Tarek', 'Mariam', 'Nour', 'Sara', 'Dina', 'Aya', 'Rania',
            'Mona', 'Heba', 'Layla', 'Yasmine', 'Fatima', 'Salma', 'Nada', 'Rana',
            'Ziad', 'Kareem', 'Sherif', 'Adel', 'Walid', 'Basem',
        ];

        $lastNames = [
            'El-Sayed', 'Hassan', 'Ibrahim', 'Mostafa', 'Khalil', 'Mahmoud', 'Abdel-Rahman',
            'El-Sharkawy', 'Farouk', 'Nasser', 'Galal', 'Osman', 'Amer', 'Tawfik',
            'Hamdy', 'Ramadan', 'Helmy', 'Fathy', 'Wahba', 'Lotfy', 'Sobhy', 'Badawi',
            'Youssef', 'Samir', 'Abdel-Aziz', 'El-Sherif', 'Ezzat', 'Adel', 'Gamal', 'El-Din',
        ];

        // Get all Intake 46 delivering cohorts
        $intake46Cohorts = DB::table('cohorts')
            ->where('name', 'Intake 46')
            ->where('status', 'delivering')
            ->get();

        if ($intake46Cohorts->isEmpty()) {
            $this->command->error('StudentSeeder: No Intake 46 (delivering) cohorts found.');
            return;
        }

        $totalCreated = 0;
        $trackIndex   = 0;

        foreach ($intake46Cohorts as $cohort) {
            $trackIndex++;
            $trackName = DB::table('tracks')->where('id', $cohort->track_id)->value('name') ?? "Track{$trackIndex}";

            // Build a short slug for the track (e.g. "open-source" → "os", just use index)
            $trackSlug = $trackIndex;

            $cohortCreated = 0;

            // Use a seeded random pool so names are deterministic but varied
            // We combine first+last names by offsetting with student index
            $fnCount = count($firstNames);
            $lnCount = count($lastNames);

            for ($studentIndex = 1; $studentIndex <= 20; $studentIndex++) {
                $firstName = $firstNames[($trackIndex * 3 + $studentIndex) % $fnCount];
                $lastName  = $lastNames[($trackIndex * 7 + $studentIndex * 2) % $lnCount];
                $fullName  = "{$firstName} {$lastName}";

                // Email: slug-of-name.trackIndex.studentIndex@student.iti.gov.eg
                $nameSlug = strtolower(str_replace([' ', "'"], ['.', ''], $firstName . '.' . $lastName));
                $nameSlug = preg_replace('/[^a-z0-9.\-]/', '', $nameSlug);
                $email    = "{$nameSlug}.{$trackSlug}.{$studentIndex}@student.iti.gov.eg";

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'               => $fullName,
                        'password'           => Hash::make('password'),
                        'role'               => 'student',
                        'expires_at'         => '2026-06-30',
                        'email_verified_at'  => now(),
                    ]
                );
                $user->syncRoles('student');
                $user->update(['role' => 'student']);

                Student::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'cohort_id'    => $cohort->id,
                        'lab_group_id' => null,
                        'is_at_risk'   => false,
                    ]
                );

                $cohortCreated++;
            }

            $totalCreated += $cohortCreated;
            $this->command->info("  → [{$trackName}] Intake 46 — {$cohortCreated} students.");
        }

        $total = Student::count();
        $this->command->info("✔ StudentSeeder — {$total} students in DB ({$totalCreated} seeded this run).");
    }
}
