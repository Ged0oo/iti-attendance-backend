<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeComponent extends Model
{
    use HasFactory;

    public const TYPES = ['lab_deliverable', 'exam', 'project'];

    protected $fillable = [
        'course_id',
        'name',
        'type',
        'weight',
        'raw_max',
        'is_deliverable',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'raw_max' => 'decimal:2',
        'is_deliverable' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
