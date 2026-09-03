<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        return $query->whereIn('status', ['breakdown', 'servis'])->whereNull('ended_at');
    }

    public static function latestPerUnit()
    {
        $latest = static::query()
            ->select('unit_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('unit_id');

        return static::query()
            ->joinSub($latest, 'latest', function ($join) {
                $join->on('unit_utilizations.id', '=', 'latest.max_id');
            })
            ->select('unit_utilizations.*');
    }
}
