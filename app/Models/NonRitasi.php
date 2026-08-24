<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NonRitasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'unit_id',
        'area_id',
        'shift',
        'tanggal',
        'hm_awal',
        'hm_akhir',
        'hm_total',
        'jam_mulai',
        'jam_selesai',
        'is_overtime',
        'lokasi_pekerjaan',
        'deskripsi_pekerjaan',
        'status',
        'fuel_consumption',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'hm_awal' => 'decimal:2',
        'hm_akhir' => 'decimal:2',
        'hm_total' => 'decimal:2',
        'jam_mulai' => 'string',
        'jam_selesai' => 'string',
        'is_overtime' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'validated' => 'badge-validated',
            'pending' => 'badge-pending',
            'in_progress' => 'badge-in-progress',
            default => 'badge-pending',
        };
    }

    public function getShiftLabelAttribute()
    {
        return $this->shift === 'siang' ? 'Day' : 'Night';
    }
}
