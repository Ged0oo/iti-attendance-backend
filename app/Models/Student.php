<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cohort_id',
        'lab_group_id',
        'national_id',
        'is_at_risk',
    ];

    protected $casts = [
        'is_at_risk' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function labGroup(): BelongsTo
    {
        return $this->belongsTo(LabGroup::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(StudentTag::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }
}
