<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────
        // 1. Branch Manager
        // ─────────────────────────────────────────────
        $manager = User::firstOrCreate(
            ['email' => 'khaled.mansour@iti.gov.eg'],
            [
                'name'               => 'Khaled Mansour',
                'password'           => Hash::make('password'),
                'role'               => 'branch_manager',
                'expires_at'         => null,
                'email_verified_at'  => now(),
            ]
        );
        $manager->syncRoles('branch_manager');
        $manager->update(['role' => 'branch_manager']);

        // ─────────────────────────────────────────────
        // 2. Track Admins (one per track)
        // ─────────────────────────────────────────────
        $trackAdmins = [
            ['Open Source Application Development',        'Amr Khalil',        'amr.khalil@iti.gov.eg'],
            ['Artificial Intelligence & Machine Learning', 'Dina Mostafa',      'dina.mostafa@iti.gov.eg'],
            ['Embedded Systems & IoT',                     'Tarek Hassan',      'tarek.hassan@iti.gov.eg'],
            ['Cybersecurity',                              'Nour El-Din Samir', 'nour.samir@iti.gov.eg'],
            ['UI/UX Design',                               'Rania Abdel-Aziz',  'rania.aziz@iti.gov.eg'],
            ['Full Stack Web Development',                 'Omar Farouk',       'omar.farouk@iti.gov.eg'],
            ['Business Intelligence & Data Engineering',   'Heba Ezzat',        'heba.ezzat@iti.gov.eg'],
            ['Mobile Application Development',             'Youssef Nasser',    'youssef.nasser@iti.gov.eg'],
        ];

        foreach ($trackAdmins as [$trackName, $name, $email]) {
            $trackId = DB::table('tracks')->where('name', $trackName)->value('id');
            if (!$trackId) {
                $this->command->warn("  Track '{$trackName}' not found — skipping admin {$email}.");
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'               => $name,
                    'password'           => Hash::make('password'),
                    'role'               => 'track_admin',
                    'expires_at'         => '2027-12-31',
                    'email_verified_at'  => now(),
                ]
            );
            $user->syncRoles('track_admin');
            $user->update(['role' => 'track_admin']);

            // Pivot: track_admins
            $pivotExists = DB::table('track_admins')
                ->where('user_id', $user->id)
                ->where('track_id', $trackId)
                ->exists();

            if (!$pivotExists) {
                DB::table('track_admins')->insert([
                    'user_id'    => $user->id,
                    'track_id'   => $trackId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ─────────────────────────────────────────────
        // 3. Instructors (3 per track, 24 total)
        //    [name, email, track_name, comp_type, hourly_rate, fixed_salary]
        // ─────────────────────────────────────────────
        $instructors = [
            // Open Source
            ['Mahmoud Sayed',     'mahmoud.sayed@iti.gov.eg',    'Open Source Application Development',        'internal', 80,  5000],
            ['Sara El-Sharkawy', 'sara.sharkawy@iti.gov.eg',     'Open Source Application Development',        'external', 120, null],
            ['Karim Adel',        'karim.adel@iti.gov.eg',       'Open Source Application Development',        'external', 100, null],
            // AI & ML
            ['Mona Ibrahim',      'mona.ibrahim@iti.gov.eg',     'Artificial Intelligence & Machine Learning', 'internal', 90,  6000],
            ['Ahmed Galal',       'ahmed.galal@iti.gov.eg',      'Artificial Intelligence & Machine Learning', 'external', 150, null],
            ['Yasmine Fouad',     'yasmine.fouad@iti.gov.eg',    'Artificial Intelligence & Machine Learning', 'external', 130, null],
            // Embedded
            ['Hazem Nabil',       'hazem.nabil@iti.gov.eg',      'Embedded Systems & IoT',                     'internal', 85,  5500],
            ['Layla Mostafa',     'layla.mostafa@iti.gov.eg',    'Embedded Systems & IoT',                     'external', 110, null],
            ['Sherif Osman',      'sherif.osman@iti.gov.eg',     'Embedded Systems & IoT',                     'external', 95,  null],
            // Cybersecurity
            ['Walid Badawi',      'walid.badawi@iti.gov.eg',     'Cybersecurity',                              'internal', 95,  6500],
            ['Noha Amer',         'noha.amer@iti.gov.eg',        'Cybersecurity',                              'external', 140, null],
            ['Basem Tawfik',      'basem.tawfik@iti.gov.eg',     'Cybersecurity',                              'external', 120, null],
            // UI/UX
            ['Nada Salah',        'nada.salah@iti.gov.eg',       'UI/UX Design',                               'internal', 75,  4500],
            ['Mariam Youssef',    'mariam.youssef@iti.gov.eg',   'UI/UX Design',                               'external', 100, null],
            ['Hassan El-Sayed',   'hassan.sayed@iti.gov.eg',     'UI/UX Design',                               'external', 90,  null],
            // Full Stack
            ['Ziad Ramadan',      'ziad.ramadan@iti.gov.eg',     'Full Stack Web Development',                 'internal', 85,  5000],
            ['Aya Hamdy',         'aya.hamdy@iti.gov.eg',        'Full Stack Web Development',                 'external', 110, null],
            ['Mohamed Gamal',     'mohamed.gamal@iti.gov.eg',    'Full Stack Web Development',                 'external', 100, null],
            // BI & Data
            ['Rana Helmy',        'rana.helmy@iti.gov.eg',       'Business Intelligence & Data Engineering',   'internal', 90,  5800],
            ['Islam Fathy',       'islam.fathy@iti.gov.eg',      'Business Intelligence & Data Engineering',   'external', 130, null],
            ['Salma Khaled',      'salma.khaled@iti.gov.eg',     'Business Intelligence & Data Engineering',   'external', 115, null],
            // Mobile
            ['Adel Wahba',        'adel.wahba@iti.gov.eg',       'Mobile Application Development',             'internal', 80,  4800],
            ['Ghada Lotfy',       'ghada.lotfy@iti.gov.eg',      'Mobile Application Development',             'external', 105, null],
            ['Tamer Sobhy',       'tamer.sobhy@iti.gov.eg',      'Mobile Application Development',             'external', 95,  null],
        ];

        foreach ($instructors as [$name, $email, $trackName, $compType, $hourlyRate, $fixedSalary]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'               => $name,
                    'password'           => Hash::make('password'),
                    'role'               => 'instructor',
                    'expires_at'         => '2027-12-31',
                    'email_verified_at'  => now(),
                ]
            );
            $user->syncRoles('instructor');
            $user->update(['role' => 'instructor']);

            Instructor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'compensation_type' => $compType,
                    'hourly_rate'       => $hourlyRate,
                    'fixed_salary'      => $fixedSalary,
                ]
            );
        }

        // ─────────────────────────────────────────────
        // Summary
        // ─────────────────────────────────────────────
        $bmCount    = User::role('branch_manager')->count();
        $taCount    = User::role('track_admin')->count();
        $instCount  = User::role('instructor')->count();
        $studCount  = User::role('student')->count();

        $this->command->table(
            ['Role', 'Count'],
            [
                ['branch_manager', $bmCount],
                ['track_admin',    $taCount],
                ['instructor',     $instCount],
                ['student',        $studCount],
            ]
        );
        $this->command->info('✔ UserSeeder — branch manager, 8 track admins, 24 instructors seeded.');
    }
}
