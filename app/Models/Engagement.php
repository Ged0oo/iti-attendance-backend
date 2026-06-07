<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Note: the engagements table started life as a temporary one from the
// attendance work. We added course_id to it in a follow up migration, so this
// model now maps the full set of columns we need.
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

    // a new engagement is scheduled until someone moves it along
    protected $attributes = [
        'status' => 'scheduled',
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
