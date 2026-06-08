<?php

namespace Database\Factories\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Branches, tracks and cohorts belong to other people. Until their seeders
 * exist we just drop plain rows in so our own factories have something to
 * point at.
 */
trait BuildsParents
{
    protected function cohortId(): int
    {
        $branchId = DB::table('branches')->insertGetId([
            'name' => fake()->city(),
            'location' => fake()->streetName(),
        ]);

        $trackId = DB::table('tracks')->insertGetId([
            'branch_id' => $branchId,
            'name' => fake()->randomElement(['Web', 'Mobile', 'AI']),
            'description' => null,
        ]);

        return DB::table('cohorts')->insertGetId([
            'track_id' => $trackId,
            'name' => 'Intake '.fake()->numberBetween(40, 50),
            'status' => 'configuring',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-01',
        ]);
    }
}
