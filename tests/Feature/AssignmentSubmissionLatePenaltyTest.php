<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\GradeComponent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignmentSubmissionLatePenaltyTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_calculates_full_late_days_from_component_due_date(): void
    {
        $studentUser = User::factory()->create();
        Role::findOrCreate('student');
        $studentUser->assignRole('student');

        $course = Course::factory()->create();
        $component = GradeComponent::factory()->create([
            'course_id' => $course->id,
            'is_deliverable' => true,
            'due_at' => '2026-06-01 10:00:00',
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'cohort_id' => $course->cohort_id,
            'is_at_risk' => false,
        ]);

        Sanctum::actingAs($studentUser);

        $this->postJson('/api/assignment-submissions', [
            'student_id' => $student->id,
            'grade_component_id' => $component->id,
            'submission_type' => 'url',
            'url' => 'https://example.com/repo',
            'submitted_at' => '2026-06-03 11:00:00',
        ])->assertCreated()
            ->assertJsonPath('data.days_late', 2)
            ->assertJsonPath('data.late_penalty', '50.00');
    }
}
