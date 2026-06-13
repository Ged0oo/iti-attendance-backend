<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('student');
        Role::findOrCreate('instructor');
        Role::findOrCreate('track_admin');
        Role::findOrCreate('branch_manager');
    }

    public function test_student_gets_own_profile(): void
    {
        $course = Course::factory()->create();
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');
        
        $student = Student::create([
            'user_id' => $user->id,
            'cohort_id' => $course->cohort_id,
            'is_at_risk' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user' => ['name', 'email'],
                    'cohort_id',
                    'lab_group_id',
                    'national_id',
                    'is_at_risk',
                    'ledger_balance',
                ]
            ])
            ->assertJsonPath('data.ledger_balance', 250);
    }

    public function test_instructor_gets_own_profile(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $user->assignRole('instructor');

        $instructor = Instructor::create([
            'user_id' => $user->id,
            'compensation_type' => 'salary',
            'fixed_salary' => 5000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'user_name',
                    'compensation_type',
                    'hourly_rate',
                    'fixed_salary',
                ]
            ])
            ->assertJsonPath('data.user_name', $user->name);
    }

    public function test_track_admin_gets_own_profile(): void
    {
        $user = User::factory()->create(['role' => 'track_admin']);
        $user->assignRole('track_admin');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me/profile');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'track_admin',
            ]);
    }

    public function test_branch_manager_gets_own_profile(): void
    {
        $user = User::factory()->create(['role' => 'branch_manager']);
        $user->assignRole('branch_manager');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me/profile');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'branch_manager',
            ]);
    }

    public function test_user_can_update_basic_profile(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/me', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Updated Name')
            ->assertJsonPath('user.email', 'updated@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'password' => bcrypt('old_password'),
        ]);
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/me', [
            'current_password' => 'old_password',
            'password' => 'new_password_123',
            'password_confirmation' => 'new_password_123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('new_password_123', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'password' => bcrypt('old_password'),
        ]);
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/me', [
            'current_password' => 'wrong_password',
            'password' => 'new_password_123',
            'password_confirmation' => 'new_password_123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_cannot_change_restricted_fields_like_role_or_expiry(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'expires_at' => now()->addDays(10),
        ]);
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/me', [
            'role' => 'branch_manager',
            'expires_at' => now()->addDays(50)->toIso8601String(),
        ]);

        $response->assertStatus(200);

        $user = $user->fresh();
        $this->assertEquals('student', $user->roles->first()?->name);
        $this->assertTrue($user->hasRole('student'));
        $this->assertFalse($user->hasRole('branch_manager'));
    }

    public function test_re_sends_welcome_notification_if_unactivated_email_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => null,
            'email' => 'old@example.com',
        ]);
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/me', [
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_does_not_send_welcome_notification_if_activated_email_changes(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'email' => 'old@example.com',
        ]);
        $user->assignRole('student');

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/me', [
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200);

        Notification::assertNotSentTo($user, WelcomeNotification::class);
    }
}
