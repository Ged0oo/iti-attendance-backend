<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\ExcuseRequest;
use App\Models\AttendanceRecord;
use App\Enums\ExcuseStatus;
use Illuminate\Database\Seeder;

class ExcuseRequestSeeder extends Seeder
{
    public function run(): void
    {
        $student2 = Student::skip(1)->first();
        if (!$student2) return;

        $recordId = 1;

        ExcuseRequest::create([
            'student_id' => $student2->id,
            'attendance_record_id' => $recordId,
            'notes' => 'I was sick',
            'status' => ExcuseStatus::Requested,
        ]);
    }
}
