<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Track;
use App\Models\Cohort;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScopeAndScanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_track_admin_scope_for_announcements()
    {
        $trackAdmin = User::factory()->create(['role' => 'track_admin']);
        
        $branchId = DB::table('branches')->insertGetId(['name' => 'Test Branch', 'location' => 'Test Location']);
        $track1Id = DB::table('tracks')->insertGetId(['branch_id' => $branchId, 'name' => 'Track 1']);
        $track2Id = DB::table('tracks')->insertGetId(['branch_id' => $branchId, 'name' => 'Track 2']);
        
        DB::table('track_admins')->insert(['user_id' => $trackAdmin->id, 'track_id' => $track1Id]);
        
        $cohort1Id = DB::table('cohorts')->insertGetId(['track_id' => $track1Id, 'name' => 'Cohort 1', 'status' => 'open', 'start_date' => now(), 'end_date' => now()->addDays(30)]);
        $cohort2Id = DB::table('cohorts')->insertGetId(['track_id' => $track2Id, 'name' => 'Cohort 2', 'status' => 'open', 'start_date' => now(), 'end_date' => now()->addDays(30)]);
        
        $this->actingAs($trackAdmin);

        // Can post to track 1
        $response1 = $this->postJson('/api/announcements', [
            'cohort_id' => $cohort1Id,
            'title' => 'Test Announcement',
            'body' => 'Announcement Body'
        ]);
        $response1->assertStatus(201);

        // Cannot post to track 2
        $response2 = $this->postJson('/api/announcements', [
            'cohort_id' => $cohort2Id,
            'title' => 'Test Announcement',
            'body' => 'Announcement Body'
        ]);
        $response2->assertStatus(403);
    }

    public function test_track_admin_scope_for_cohort_transitions()
    {
        $trackAdmin = User::factory()->create(['role' => 'track_admin']);
        
        $branchId = DB::table('branches')->insertGetId(['name' => 'Test Branch 2', 'location' => 'Test Location']);
        $track1Id = DB::table('tracks')->insertGetId(['branch_id' => $branchId, 'name' => 'Track 1']);
        $track2Id = DB::table('tracks')->insertGetId(['branch_id' => $branchId, 'name' => 'Track 2']);
        
        DB::table('track_admins')->insert(['user_id' => $trackAdmin->id, 'track_id' => $track1Id]);
        
        $cohort1Id = DB::table('cohorts')->insertGetId(['track_id' => $track1Id, 'name' => 'Cohort 1', 'status' => 'open', 'start_date' => now(), 'end_date' => now()->addDays(30)]);
        $cohort2Id = DB::table('cohorts')->insertGetId(['track_id' => $track2Id, 'name' => 'Cohort 2', 'status' => 'open', 'start_date' => now(), 'end_date' => now()->addDays(30)]);
        
        $this->actingAs($trackAdmin);

        // Can transition track 1
        $response1 = $this->patchJson("/api/cohorts/{$cohort1Id}/transition", [
            'status' => 'configuring'
        ]);
        $response1->assertStatus(200);

        // Cannot transition track 2
        $response2 = $this->patchJson("/api/cohorts/{$cohort2Id}/transition", [
            'status' => 'configuring'
        ]);
        $response2->assertStatus(403);
    }

    public function test_scan_with_invalid_payload_returns_422()
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        
        $branchId = DB::table('branches')->insertGetId(['name' => 'Test Branch', 'location' => 'Test Location']);
        $trackId = DB::table('tracks')->insertGetId(['branch_id' => $branchId, 'name' => 'Test Track']);
        $cohortId = DB::table('cohorts')->insertGetId(['track_id' => $trackId, 'name' => 'Test Cohort', 'status' => 'active', 'start_date' => now(), 'end_date' => now()->addDays(30)]);
        
        $student = Student::create(['user_id' => $studentUser->id, 'cohort_id' => $cohortId]);
        
        $this->actingAs($studentUser);

        // Missing session_id and expires_at
        $badPayload = Crypt::encryptString(json_encode(['foo' => 'bar']));

        $response = $this->postJson('/api/attendance/scan', [
            'session_qr_code' => $badPayload,
            'student_id' => $student->id
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Invalid QR payload.']);
    }
}
