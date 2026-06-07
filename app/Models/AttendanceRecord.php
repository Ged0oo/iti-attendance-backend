<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'session_id',
        'student_id',
        'track_id',
        'arrived_at',
        'left_at',
        'status',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    // Member 2
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    // Member 3
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    // Member 5
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}