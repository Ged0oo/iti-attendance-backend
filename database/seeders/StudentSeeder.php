<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create([
                'name' => "Student User $i",
                'email' => "student{$i}@example.com",
            ]);

            Student::create([
                'user_id' => $user->id,
                'cohort_id' => 1,
            ]);
        }
    }
}
