<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'kapasitas' => 'decimal:2',
        'fuel_consumption_rate' => 'decimal:2',
        'tahun' => 'integer',
        'is_active' => 'boolean',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
    ];

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'unit_area')->withTimestamps();
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'material_unit')->withPivot('consumption_rate');
    }

    public function ritasis()
    {
        return $this->hasMany(Ritasi::class);
    }

    public function nonRitasis()
    {
        return $this->hasMany(NonRitasi::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active' => 'badge-active',
            'maintenance' => 'badge-maintenance',
            'breakdown' => 'badge-breakdown',
            'standby' => 'badge-standby',
            default => 'badge-standby',
        };
    }

    public function getRealStatusAttribute()
    {
        // 1. Check if unit is in active maintenance (breakdown or servis in active utilization)
        $activeUtilization = \App\Models\UnitUtilization::where('unit_id', $this->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
            
        if ($activeUtilization) {
            return $activeUtilization->status; // 'breakdown' or 'servis'
        }
        
        // 2. Check if unit is active (used by any operator in the current shift)
        $today = now()->toDateString();
        $hour = now()->hour;
        $shift = ($hour >= 6 && $hour < 18) ? 'siang' : 'malam';
        
        $hasRitasi = $this->ritasis()->where('tanggal', $today)->where('shift', $shift)->exists();
        $hasNonRitasi = $this->nonRitasis()->where('tanggal', $today)->where('shift', $shift)->exists();
        
        if ($hasRitasi || $hasNonRitasi) {
            return 'active';
        }
        
        // 3. Otherwise standby
        return 'standby';
    }

    public function getRealStatusBadgeAttribute()
    {
        return match($this->real_status) {
            'active' => 'badge-active',
            'servis' => 'badge-maintenance',
            'breakdown' => 'badge-breakdown',
            'standby' => 'badge-standby',
            default => 'badge-standby',
        };
    }
}
