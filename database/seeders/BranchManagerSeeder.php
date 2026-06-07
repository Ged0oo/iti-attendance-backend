<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BranchManagerSeeder extends Seeder
{
    /**
     * Seeds the root account
     */
    public function run(): void
    {
        $manager = User::firstOrCreate(
            ['email' => 'manager@iti.gov.eg'],
            [
                'name'       => 'Branch Manager',
                'password'   => Hash::make('password'),
                'expires_at' => null, // Never expires
            ]
        );

        $manager->syncRoles('branch_manager');

        $this->command->info('  Branch Manager seeded.');
        $this->command->info('  Email   : manager@iti.gov.eg');
        $this->command->info('  Password: password');
        $this->command->warn('  Change this password before production!');
    }
}
