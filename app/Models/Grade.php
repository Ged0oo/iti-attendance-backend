<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'grade_component_id',
        'lab_group_id',
        'raw_score',
        'normalized_score',
        'graded_by',
        'override_value',
        'override_note',
        'overridden_by',
        'overridden_at',
    ];

    protected $casts = [
        'raw_score' => 'decimal:2',
        'normalized_score' => 'decimal:2',
        'override_value' => 'decimal:2',
        'overridden_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradeComponent(): BelongsTo
    {
        return $this->belongsTo(GradeComponent::class, 'grade_component_id');
    }

    public function labGroup(): BelongsTo
    {
        return $this->belongsTo(LabGroup::class, 'lab_group_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function overrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
