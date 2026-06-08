<?php

namespace Database\Seeders;

use App\Models\BillingRecord;
use Illuminate\Database\Seeder;

class BillingRecordSeeder extends Seeder
{
    public function run(): void
    {
        BillingRecord::factory()->count(3)->create();
    }
}
