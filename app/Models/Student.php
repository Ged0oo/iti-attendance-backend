<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'cohort_id',
        'lab_group_id',
        'is_at_risk',
    ];

    protected $casts = [
        'is_at_risk' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function (Student $student) {
            $student->ledger()->create([
                'balance' => 250
            ]);
        });
    }

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

    public function ledger(): HasOne
    {
        return $this->hasOne(AttendanceLedger::class);
    }

    public function excuseRequests(): HasMany
    {
        return $this->hasMany(ExcuseRequest::class);
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
