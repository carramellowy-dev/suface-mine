<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTarget extends Model
{
    use HasFactory;

    protected $fillable = ['material_id', 'periode', 'target_ritasi'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
