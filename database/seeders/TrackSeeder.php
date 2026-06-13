<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = DB::table('branches')->where('name', 'ITI Smart Village')->value('id');

        if (!$branchId) {
            $this->command->error('TrackSeeder: ITI Smart Village branch not found. Run BranchSeeder first.');
            return;
        }

        $tracks = [
            [
                'name'        => 'Open Source Application Development',
                'description' => 'Linux, PHP, Python, Django, Laravel, ERP systems',
            ],
            [
                'name'        => 'Artificial Intelligence & Machine Learning',
                'description' => 'ML, Deep Learning, NLP, Computer Vision, Data Science',
            ],
            [
                'name'        => 'Embedded Systems & IoT',
                'description' => 'C/C++, RTOS, microcontrollers, IoT protocols',
            ],
            [
                'name'        => 'Cybersecurity',
                'description' => 'Network security, ethical hacking, SIEM, incident response',
            ],
            [
                'name'        => 'UI/UX Design',
                'description' => 'Figma, user research, prototyping, design systems',
            ],
            [
                'name'        => 'Full Stack Web Development',
                'description' => 'React, Node.js, databases, REST APIs, cloud deployment',
            ],
            [
                'name'        => 'Business Intelligence & Data Engineering',
                'description' => 'SQL, Power BI, ETL pipelines, data warehousing',
            ],
            [
                'name'        => 'Mobile Application Development',
                'description' => 'Android, iOS, Flutter, React Native',
            ],
        ];

        $created = 0;
        foreach ($tracks as $track) {
            $exists = DB::table('tracks')->where('name', $track['name'])->exists();
            if (!$exists) {
                DB::table('tracks')->insert([
                    'branch_id'   => $branchId,
                    'name'        => $track['name'],
                    'description' => $track['description'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $created++;
            }
        }

        $total = DB::table('tracks')->where('branch_id', $branchId)->count();
        $this->command->info("✔ TrackSeeder — {$total} tracks present ({$created} new).");
    }
}
