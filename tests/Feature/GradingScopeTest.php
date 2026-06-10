<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\LabGroup;
use App\Models\Student;
use App\Models\TrackAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GradingScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_admin_only_sees_grades_for_their_track(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('track_admin');
        $admin->assignRole('track_admin');

        $ownGrade = $this->gradeForNewTrack();
        $otherGrade = $this->gradeForNewTrack();

        TrackAdmin::create([
            'user_id' => $admin->id,
            'track_id' => $this->trackIdForCourse($ownGrade->gradeComponent->course),
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/grades')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownGrade->id);

        $this->getJson("/api/grades/{$otherGrade->id}")
            ->assertForbidden();
    }

    private function gradeForNewTrack(): Grade
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

    private function trackIdForCourse(Course $course): int
    {
        return DB::table('cohorts')
            ->where('id', $course->cohort_id)
            ->value('track_id');
    }
}
