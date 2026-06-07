<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Heads up: the engagements table right now is the temporary one from the
// attendance work and it does not have course_id yet. Keep course_id in the
// model so we are ready, but don't save it until the real table is in place.
class Engagement extends Model
{
    use HasFactory;

    public const TYPES = ['lecture', 'lab', 'business_session'];
    public const STATUSES = ['scheduled', 'in_progress', 'completed', 'cancelled'];

    protected $fillable = [
        'cohort_id',
        'course_id',
        'instructor_id',
        'type',
        'date_range_start',
        'date_range_end',
        'scheduled_hours',
        'status',
    ];

    protected $casts = [
        'date_range_start' => 'date',
        'date_range_end' => 'date',
        'scheduled_hours' => 'integer',
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** instructor_id references users.id (the teaching seat). */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
