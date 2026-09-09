<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = Pegawai::orderBy('id')->get();
        $areas = Area::orderBy('id')->get();
        $areaIds = $areas->pluck('id')->toArray();

        // 1. Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'pegawai_id' => null,
            ]
        );

        // 2. 5 Akun SPV (Supervisor)
        $spvList = ['Sugeng', 'Darma', 'Hendro', 'Pras', 'Bayu'];
        foreach ($spvList as $name) {
            $spv = User::updateOrCreate(
                ['username' => strtolower($name)],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'spv',
                    'pegawai_id' => null,
                ]
            );
            $spv->areas()->sync($areaIds);
        }

        // 3. 5 Akun Senior SPV
        $seniorSpvList = ['Teguh', 'Surya', 'Hadi', 'Santoso', 'Wibowo'];
        foreach ($seniorSpvList as $name) {
            $srSpv = User::updateOrCreate(
                ['username' => strtolower($name)],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'senior_spv',
                    'pegawai_id' => null,
                ]
            );
            $srSpv->areas()->sync($areaIds);
        }

        // 4. 10 Akun Operator (1-to-1 dengan data Pegawai)
        foreach ($pegawais as $pegawai) {
            User::updateOrCreate(
                ['username' => strtolower($pegawai->nama)],
                [
                    'name' => $pegawai->nama,
                    'password' => Hash::make('password'),
                    'role' => 'pegawai',
                    'pegawai_id' => $pegawai->id,
                ]
            );
        }
    }
}
