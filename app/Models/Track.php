<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Track extends Model
{
    protected $fillable = ['branch_id', 'name', 'description'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(TrackAdmin::class);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class);
    }
}
