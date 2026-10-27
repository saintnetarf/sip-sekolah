<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sekolah extends Model
{
    protected $fillable = [
        'npsn',
        'nama',
        'jenjang',
        'status',
        'alamat',
        'kecamatan',
        'telepon',
        'latitude',
        'longitude',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Profil detail sekolah.
     */
    public function profil(): HasOne
    {
        return $this->hasOne(ProfilSekolah::class);
    }

    /**
     * Daftar akun pengguna yang terikat dengan sekolah ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
