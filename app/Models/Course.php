<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'cohort_id',
        'name',
        'description',
        'max_score',
    ];

    protected $casts = [
        'max_score' => 'integer',
    ];

    // every course is out of 100
    protected $attributes = [
        'max_score' => 100,
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function gradeComponents(): HasMany
    {
        return $this->hasMany(GradeComponent::class);
    }

    public function labGroups(): HasMany
    {
        return $this->hasMany(LabGroup::class);
    }
}
