<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTag extends Model
{
    use HasFactory;

    public const TAGS = [
        'uses_ai',
        'cheating',
        'loves_extra_work',
        'needs_support',
        'at_risk',
        'excellent_progress',
    ];

    protected $fillable = [
        'student_id',
        'tag',
        'tagged_by',
        'course_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function taggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tagged_by');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
