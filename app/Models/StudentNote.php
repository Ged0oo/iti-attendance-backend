<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'note',
        'written_by',
        'course_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function writtenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'written_by');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
