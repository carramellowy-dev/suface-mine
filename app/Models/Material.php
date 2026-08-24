<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'stok' => 'decimal:2',
        'stok_minimal' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'material_unit')->withPivot('consumption_rate');
    }

    public function ritasis()
    {
        return $this->hasMany(Ritasi::class);
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->stok <= $this->stok_minimal) return 'badge-low-stock';
        return match($this->status) {
            'active' => 'badge-active',
            'inactive' => 'badge-inactive',
            'restricted' => 'badge-restricted',
            default => 'badge-active',
        };
    }

    public function isLowStock()
    {
        return $this->stok <= $this->stok_minimal;
    }
}
