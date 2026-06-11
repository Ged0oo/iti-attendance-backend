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

class GradeDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_admin_gets_distribution_for_assigned_track_only(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('track_admin');
        $admin->assignRole('track_admin');

        $ownGrade = $this->gradeForNewTrack(normalizedScore: 50, overrideValue: 92);
        $this->gradeForNewTrack(normalizedScore: 85);

        TrackAdmin::create([
            'user_id' => $admin->id,
            'track_id' => $this->trackIdForCourse($ownGrade->gradeComponent->course),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/grade-distribution')
            ->assertOk();

        $data = $response->json('data');

        $this->assertSame(1, $data['total_students']);
        $this->assertSame(1, $data['student_course_count']);
        $this->assertEquals(92, $data['average_score']);
        $this->assertSame(1, collect($data['buckets'])->firstWhere('label', '90-100')['count']);
        $this->assertSame(0, collect($data['buckets'])->firstWhere('label', '80-89')['count']);
    }

    public function test_instructor_gets_distribution_for_their_lab_groups_only(): void
    {
        $instructor = User::factory()->create();
        Role::findOrCreate('instructor');
        $instructor->assignRole('instructor');

        $this->gradeForNewTrack(instructor: $instructor, normalizedScore: 72);
        $this->gradeForNewTrack(normalizedScore: 95);

        Sanctum::actingAs($instructor);

        $response = $this->getJson('/api/grade-distribution')
            ->assertOk();

        $data = $response->json('data');

        $this->assertSame(1, $data['total_students']);
        $this->assertSame(1, $data['student_course_count']);
        $this->assertEquals(72, $data['average_score']);
        $this->assertSame(1, collect($data['buckets'])->firstWhere('label', '70-79')['count']);
        $this->assertSame(0, collect($data['buckets'])->firstWhere('label', '90-100')['count']);
    }

    private function gradeForNewTrack(?User $instructor = null, float $normalizedScore = 80, ?float $overrideValue = null): Grade
    {
        $instructor ??= User::factory()->create();
        $studentUser = User::factory()->create();
        $course = Course::factory()->create();
        $component = GradeComponent::factory()->create([
            'course_id' => $course->id,
            'weight' => 100,
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
            'raw_score' => $normalizedScore,
            'normalized_score' => $normalizedScore,
            'graded_by' => $instructor->id,
            'override_value' => $overrideValue,
            'override_note' => $overrideValue ? 'Dashboard test override.' : null,
            'overridden_by' => $overrideValue ? User::factory()->create()->id : null,
            'overridden_at' => $overrideValue ? now() : null,
        ]);
    }

    private function trackIdForCourse(Course $course): int
    {
        return DB::table('cohorts')
            ->where('id', $course->cohort_id)
            ->value('track_id');
    }
}
