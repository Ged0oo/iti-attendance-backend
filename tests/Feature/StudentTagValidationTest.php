<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentTag;
use App\Models\TrackAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentTagValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_tags_must_use_predefined_values(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('track_admin');
        $admin->assignRole('track_admin');

        $course = Course::factory()->create();
        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'cohort_id' => $course->cohort_id,
            'is_at_risk' => false,
        ]);

        TrackAdmin::create([
            'user_id' => $admin->id,
            'track_id' => DB::table('cohorts')->where('id', $course->cohort_id)->value('track_id'),
        ]);

        Sanctum::actingAs($admin);

        $this->postJson('/api/student-tags', [
            'student_id' => $student->id,
            'tag' => 'random_tag',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['tag']);

        $this->postJson('/api/student-tags', [
            'student_id' => $student->id,
            'tag' => StudentTag::TAGS[0],
        ])->assertCreated()
            ->assertJsonPath('data.tag', StudentTag::TAGS[0]);
    }
}
