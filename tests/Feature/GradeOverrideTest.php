<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\LabGroup;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GradeOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_override_requires_a_note(): void
    {
        $admin = $this->trackAdmin();
        $grade = $this->grade();

        Sanctum::actingAs($admin);

        $this->patchJson("/api/grades/{$grade->id}/override", [
            'override_value' => 18,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['override_note']);
    }

    public function test_override_preserves_original_score_and_records_audit_fields(): void
    {
        $admin = $this->trackAdmin();
        $grade = $this->grade();

        Sanctum::actingAs($admin);

        $this->patchJson("/api/grades/{$grade->id}/override", [
            'override_value' => 18,
            'override_note' => 'Adjusted after rubric review.',
        ])->assertOk()
            ->assertJsonPath('data.raw_score', '50.00')
            ->assertJsonPath('data.normalized_score', '20.00')
            ->assertJsonPath('data.override_value', '18.00')
            ->assertJsonPath('data.override_note', 'Adjusted after rubric review.')
            ->assertJsonPath('data.overridden_by', $admin->id)
            ->assertJsonPath('data.effective_score', '18.00');

        $grade->refresh();

        $this->assertSame('50.00', $grade->raw_score);
        $this->assertSame('20.00', $grade->normalized_score);
        $this->assertSame($admin->id, $grade->overridden_by);
        $this->assertNotNull($grade->overridden_at);
    }

    private function trackAdmin(): User
    {
        $user = User::factory()->create();

        Role::findOrCreate('track_admin');
        $user->assignRole('track_admin');

        return $user;
    }

    private function grade(): Grade
    {
        $instructor = User::factory()->create();
        $studentUser = User::factory()->create();
        $course = Course::factory()->create();
        $component = GradeComponent::factory()->create([
            'course_id' => $course->id,
            'weight' => 40,
            'raw_max' => 100,
        ]);
        $labGroup = LabGroup::factory()->create([
            'cohort_id' => $course->cohort_id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'cohort_id' => $course->cohort_id,
            'lab_group_id' => $labGroup->id,
            'is_at_risk' => false,
        ]);

        return Grade::create([
            'student_id' => $student->id,
            'grade_component_id' => $component->id,
            'lab_group_id' => $labGroup->id,
            'raw_score' => 50,
            'normalized_score' => 20,
            'graded_by' => $instructor->id,
        ]);
    }
}
