<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            BranchManagerSeeder::class,
        ]);

        // M5
        $this->call([
            StudentSeeder::class,
            AttendanceLedgerSeeder::class,
            ExcuseRequestSeeder::class,
        ]);
    }
}
