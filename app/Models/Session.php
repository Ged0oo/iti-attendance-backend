<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Heads up: the sessions table right now is the temporary one from the
// attendance work, so its columns differ a little from what we want
// (scheduled_hours is an int, qr_code is required). Holding our own
// migration until the team agrees who owns this table.
class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'engagement_id',
        'date',
        'start_time',
        'end_time',
        'scheduled_hours',
        'is_delivered',
        'qr_code',
    ];

    protected $casts = [
        'date' => 'date',
        'is_delivered' => 'boolean',
    ];

    // a session is not delivered until it actually happens
    protected $attributes = [
        'is_delivered' => false,
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }
}
