<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = ['Budi', 'Joko', 'Rudi', 'Agus', 'Hendra', 'Eko', 'Ahmad', 'Dedi', 'Bambang', 'Wawan', 'Andi', 'Iwan', 'Arief', 'Hasan', 'Fajar', 'Aditya', 'Rizky', 'Dimas', 'Ilham', 'Taufik'];
        $lastNames = ['Santoso', 'Wijaya', 'Kurniawan', 'Setiawan', 'Nugroho', 'Pratama', 'Saputra', 'Wibowo', 'Hidayat', 'Putra', 'Mahendra', 'Syahputra', 'Ramadhan', 'Fadillah', 'Pangestu'];

        for ($i = 1; $i <= 66; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            \App\Models\Pegawai::create(['nama' => $firstName . ' ' . $lastName]);
        }
    }
}
