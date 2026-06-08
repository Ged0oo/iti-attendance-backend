<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'location'];

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
