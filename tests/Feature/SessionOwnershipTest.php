<?php

namespace Tests\Feature;

use App\Models\Session;
use App\Models\User;
use App\Models\Engagement;
use App\Models\Student;
use App\Models\NfcTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_instructor_cannot_access_unowned_session_endpoints()
    {
        $instructorA = User::factory()->create(['role' => 'instructor']);
        $instructorB = User::factory()->create(['role' => 'instructor']);

        $engagement = Engagement::factory()->create(['instructor_id' => $instructorA->id]);
        $session = Session::factory()->create(['engagement_id' => $engagement->id]);

        $this->actingAs($instructorB);

        $this->getJson("/api/sessions/{$session->id}/qr-code")->assertStatus(403);
        $this->getJson("/api/sessions/{$session->id}/attendance")->assertStatus(403);
        $this->postJson("/api/sessions/{$session->id}/close")->assertStatus(403);

        $this->actingAs($instructorA);

        $this->getJson("/api/sessions/{$session->id}/qr-code")->assertSuccessful();
        $this->getJson("/api/sessions/{$session->id}/attendance")->assertSuccessful();
        $this->postJson("/api/sessions/{$session->id}/close")->assertSuccessful();
    }

    public function test_track_admin_can_access_any_session_endpoints()
    {
        $instructorA = User::factory()->create(['role' => 'instructor']);
        $admin = User::factory()->create(['role' => 'track_admin']);

        $engagement = Engagement::factory()->create(['instructor_id' => $instructorA->id]);
        $session = Session::factory()->create(['engagement_id' => $engagement->id]);

        $this->actingAs($admin);

        $this->getJson("/api/sessions/{$session->id}/qr-code")->assertSuccessful();
        $this->getJson("/api/sessions/{$session->id}/attendance")->assertSuccessful();
        $this->postJson("/api/sessions/{$session->id}/close")->assertSuccessful();
    }

    public function test_closed_session_rejects_checkins()
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        
        DB::table('branches')->insertOrIgnore(['id' => 1, 'name' => 'Test Branch', 'location' => 'Test Location']);
        DB::table('tracks')->insertOrIgnore(['id' => 1, 'branch_id' => 1, 'name' => 'Test Track']);
        DB::table('cohorts')->insertOrIgnore(['id' => 1, 'track_id' => 1, 'name' => 'Test Cohort', 'status' => 'active', 'start_date' => now(), 'end_date' => now()->addDays(30)]);
        
        $student = Student::create(['user_id' => $studentUser->id, 'cohort_id' => 1]);
        
        $session = Session::factory()->create([
            'date' => now()->toDateString(),
            'closed_at' => now()
        ]);
        
        // NFC Scan
        $nfcTag = NfcTag::create([
            'student_id' => $student->id,
            'serial_number' => 'TEST-123',
            'status' => 'active'
        ]);

        $this->actingAs($studentUser);

        $response = $this->postJson('/api/nfc/scan', [
            'session_id' => $session->id,
            'serial_number' => 'TEST-123'
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Session is closed; check-in is not allowed']);

        // QR Scan
        $validSeconds = 15;
        $payload = [
            'session_id' => $session->id,
            'expires_at' => now()->addSeconds($validSeconds)->timestamp,
        ];
        $qrCode = Crypt::encryptString(json_encode($payload));

        $responseQr = $this->postJson('/api/attendance/scan', [
            'session_qr_code' => $qrCode,
            'student_id' => $student->id
        ]);

        $responseQr->assertStatus(422)
            ->assertJson(['message' => 'Session is closed; check-in is not allowed']);
    }

    public function test_open_session_accepts_checkins()
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        
        DB::table('branches')->insertOrIgnore(['id' => 1, 'name' => 'Test Branch', 'location' => 'Test Location']);
        DB::table('tracks')->insertOrIgnore(['id' => 1, 'branch_id' => 1, 'name' => 'Test Track']);
        DB::table('cohorts')->insertOrIgnore(['id' => 1, 'track_id' => 1, 'name' => 'Test Cohort', 'status' => 'active', 'start_date' => now(), 'end_date' => now()->addDays(30)]);
        
        $student = Student::create(['user_id' => $studentUser->id, 'cohort_id' => 1]);
        
        $session = Session::factory()->create([
            'date' => now()->toDateString(),
            'closed_at' => null
        ]);
        
        // QR Scan
        $validSeconds = 15;
        $payload = [
            'session_id' => $session->id,
            'expires_at' => now()->addSeconds($validSeconds)->timestamp,
        ];
        $qrCode = Crypt::encryptString(json_encode($payload));

        $this->actingAs($studentUser);
        $responseQr = $this->postJson('/api/attendance/scan', [
            'session_qr_code' => $qrCode,
            'student_id' => $student->id
        ]);

        $responseQr->assertStatus(200);

        // NFC Scan
        $nfcTag = NfcTag::create([
            'student_id' => $student->id,
            'serial_number' => 'TEST-123',
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/nfc/scan', [
            'session_id' => $session->id,
            'serial_number' => 'TEST-123'
        ]);

        $response->assertStatus(200);
    }
}
