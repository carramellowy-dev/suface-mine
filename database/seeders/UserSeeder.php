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

        // 1. Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'pegawai_id' => null,
            ]
        );

        // 2. SPV Utama
        $spvUtama = User::updateOrCreate(
            ['username' => 'spv'],
            [
                'name' => 'Supervisor Utama',
                'password' => Hash::make('password'),
                'role' => 'spv',
                'pegawai_id' => null,
            ]
        );
        // SPV Utama oversees all areas
        $spvUtama->areas()->sync($areas->pluck('id')->toArray());

        // 3. SPV Lapangan (spv1 - spv4)
        $spvConfig = [
            ['username' => 'spv1', 'name' => 'SPV Sugiantoro (Pit)', 'pegawai_idx' => 10, 'area_kodes' => ['AREA-001', 'AREA-002', 'AREA-003', 'AREA-004']],
            ['username' => 'spv2', 'name' => 'SPV Darmawan (Disposal & Haul)', 'pegawai_idx' => 11, 'area_kodes' => ['AREA-005', 'AREA-006', 'AREA-007', 'AREA-008']],
            ['username' => 'spv3', 'name' => 'SPV Hendrawan (Stockpile & Crusher)', 'pegawai_idx' => 12, 'area_kodes' => ['AREA-009', 'AREA-010', 'AREA-011']],
            ['username' => 'spv4', 'name' => 'SPV Prasetyo (Facilities & Port)', 'pegawai_idx' => 13, 'area_kodes' => ['AREA-012', 'AREA-013', 'AREA-014']],
        ];

        foreach ($spvConfig as $cfg) {
            $pegawai = $pegawais->get($cfg['pegawai_idx']);
            $spv = User::updateOrCreate(
                ['username' => $cfg['username']],
                [
                    'name' => $cfg['name'],
                    'password' => Hash::make('password'),
                    'role' => 'spv',
                    'pegawai_id' => $pegawai?->id,
                ]
            );

            $assignedAreaIds = Area::whereIn('kode', $cfg['area_kodes'])->pluck('id')->toArray();
            $spv->areas()->sync($assignedAreaIds);
        }

        // 4. Operators (operator1 - operator10)
        $opConfig = [
            ['username' => 'operator1', 'name' => 'Operator Budi (DT)', 'pegawai_idx' => 0],
            ['username' => 'operator2', 'name' => 'Operator Agus (EXC)', 'pegawai_idx' => 1],
            ['username' => 'operator3', 'name' => 'Operator Hendra (BLD)', 'pegawai_idx' => 2],
            ['username' => 'operator4', 'name' => 'Operator Rudi (MG)', 'pegawai_idx' => 3],
            ['username' => 'operator5', 'name' => 'Operator Joko (LOD)', 'pegawai_idx' => 4],
            ['username' => 'operator6', 'name' => 'Operator Wawan (DT)', 'pegawai_idx' => 5],
            ['username' => 'operator7', 'name' => 'Operator Eko (DT)', 'pegawai_idx' => 6],
            ['username' => 'operator8', 'name' => 'Operator Ahmad (EXC)', 'pegawai_idx' => 7],
            ['username' => 'operator9', 'name' => 'Operator Dedi (BLD)', 'pegawai_idx' => 8],
            ['username' => 'operator10', 'name' => 'Operator Bambang (DT)', 'pegawai_idx' => 9],
        ];

        foreach ($opConfig as $cfg) {
            $pegawai = $pegawais->get($cfg['pegawai_idx']);
            User::updateOrCreate(
                ['username' => $cfg['username']],
                [
                    'name' => $cfg['name'],
                    'password' => Hash::make('password'),
                    'role' => 'pegawai',
                    'pegawai_id' => $pegawai?->id,
                ]
            );
        }
    }
}
