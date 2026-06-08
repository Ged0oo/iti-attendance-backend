<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLedgerEntry extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'attendance_ledger_id',
        'attendance_record_id',
        'delta',
        'balance_after',
        'reason',
    ];

    protected $casts = [
        'delta' => 'integer',
        'balance_after' => 'integer',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(AttendanceLedger::class, 'attendance_ledger_id');
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
