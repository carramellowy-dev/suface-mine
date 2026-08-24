<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitUtilization extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => 'string',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public static function latestPerUnit()
    {
        return static::query()
            ->selectRaw('DISTINCT ON (unit_id) unit_id, status, started_at, ended_at')
            ->orderBy('unit_id')
            ->orderBy('started_at', 'desc')
            ->orderBy('id', 'desc');
    }
}
