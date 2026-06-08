<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory;

    public const SUBMISSION_TYPES = ['url', 'file'];

    protected $fillable = [
        'grade_component_id',
        'student_id',
        'submission_type',
        'url',
        'file_path',
        'submitted_at',
        'days_late',
        'late_penalty',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'days_late' => 'integer',
        'late_penalty' => 'decimal:2',
    ];

    public function gradeComponent(): BelongsTo
    {
        return $this->belongsTo(GradeComponent::class, 'grade_component_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
