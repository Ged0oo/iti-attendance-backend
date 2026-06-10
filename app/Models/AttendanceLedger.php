<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceLedger extends Model
{
    protected $fillable = [
        'student_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AttendanceLedgerEntry::class);
    }
}
