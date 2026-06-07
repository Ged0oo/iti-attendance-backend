<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingRecord extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'finalized', 'forwarded'];

    protected $fillable = [
        'cohort_id',
        'user_id',
        'compensation_type',
        'scheduled_hours',
        'delivered_hours',
        'hourly_rate',
        'fixed_salary',
        'total_amount',
        'billing_period_start',
        'billing_period_end',
        'status',
    ];

    protected $casts = [
        'scheduled_hours' => 'decimal:2',
        'delivered_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'fixed_salary' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
    ];

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    /** user_id is the billed instructor. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
