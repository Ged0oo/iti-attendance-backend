<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ExcuseStatus;

class ExcuseRequest extends Model
{
    protected $fillable = [
        'student_id',
        'attendance_record_id',
        'status',
        'attachment_path',
        'notes',
        'reviewer_id',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => ExcuseStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
