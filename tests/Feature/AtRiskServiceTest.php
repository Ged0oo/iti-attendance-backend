<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Student;
use App\Models\User;
use App\Services\AtRiskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtRiskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_is_at_risk_when_any_course_total_is_below_sixty(): void
    {
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'cohort_id' => Course::factory()->create()->cohort_id,
            'is_at_risk' => false,
        ]);
        $course = Course::factory()->create([
            'cohort_id' => $student->cohort_id,
        ]);
        $component = GradeComponent::factory()->create([
            'course_id' => $course->id,
            'weight' => 100,
            'raw_max' => 100,
        ]);

        Grade::create([
            'student_id' => $student->id,
            'grade_component_id' => $component->id,
            'raw_score' => 55,
            'normalized_score' => 55,
            'graded_by' => User::factory()->create()->id,
        ]);

        AtRiskService::evaluate($student);

        $this->assertTrue($student->refresh()->is_at_risk);
    }
}
