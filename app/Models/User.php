<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'password', 'role', 'pegawai_id', 'profile_photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->profile_photo
            ? \Illuminate\Support\Facades\Storage::url($this->profile_photo)
            : null;
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_spv', 'spv_id', 'area_id');
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function isSpv(): bool
    {
        return $this->role === 'spv';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
