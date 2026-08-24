<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $guarded = [];

    public function ritasis()
    {
        return $this->hasMany(Ritasi::class);
    }

    public function nonRitasis()
    {
        return $this->hasMany(NonRitasi::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
