<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,   // untouched
            BranchSeeder::class,
            TrackSeeder::class,
            UserSeeder::class,
            CohortSeeder::class,
            CourseSeeder::class,
            StudentSeeder::class,
            LabGroupSeeder::class,
            EngagementSeeder::class,
            SessionSeeder::class,
            BillingRecordSeeder::class,
            AttendanceLedgerSeeder::class,
            ExcuseRequestSeeder::class,
            AnnouncementSeeder::class,
            DevAccountSeeder::class,  // always runs last
        ]);
    }
}
