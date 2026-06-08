<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instructor extends Model
{
    use HasFactory;

    public const TYPES = ['external', 'internal'];

    protected $fillable = [
        'user_id',
        'compensation_type',
        'hourly_rate',
        'fixed_salary',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'fixed_salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
