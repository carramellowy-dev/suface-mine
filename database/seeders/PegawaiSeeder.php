<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawaiNames = [
            'Budi Santoso',      // ID 1 -> operator1 (Dump Truck)
            'Agus Wijaya',       // ID 2 -> operator2 (Excavator)
            'Hendra Setiawan',   // ID 3 -> operator3 (Bulldozer)
            'Rudi Kurniawan',    // ID 4 -> operator4 (Motor Grader)
            'Joko Nugroho',      // ID 5 -> operator5 (Loader)
            'Wawan Pratama',     // ID 6 -> operator6 (Dump Truck)
            'Eko Saputra',       // ID 7 -> operator7 (Dump Truck)
            'Ahmad Wibowo',      // ID 8 -> operator8 (Excavator)
            'Dedi Hidayat',      // ID 9 -> operator9 (Bulldozer)
            'Bambang Putra',     // ID 10 -> operator10 (Dump Truck)
            'Andi Mahendra',     // ID 11 -> SPV Sugiantoro
            'Iwan Syahputra',    // ID 12 -> SPV Darmawan
            'Arief Ramadhan',    // ID 13 -> SPV Hendrawan
            'Hasan Fadillah',    // ID 14 -> SPV Prasetyo
            'Fajar Pangestu',    // ID 15 -> SPV Kurniawan
            'Dimas Surya',       // ID 16
            'Ilham Bagas',       // ID 17
            'Taufik Fauzi',      // ID 18
            'Rizky Yusuf',       // ID 19
            'Aditya Riyan',      // ID 20
        ];

        foreach ($pegawaiNames as $nama) {
            Pegawai::firstOrCreate(['nama' => $nama]);
        }
    }
}
