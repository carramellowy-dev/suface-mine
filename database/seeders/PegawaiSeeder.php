<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawaiNames = [
            'Budi',
            'Eka',
            'Agus',
            'Rudi',
            'Joko',
            'Wawan',
            'Hendra',
            'Dedi',
            'Ahmad',
            'Bambang',
        ];

        foreach ($pegawaiNames as $nama) {
            Pegawai::firstOrCreate(['nama' => $nama]);
        }
    }
}
