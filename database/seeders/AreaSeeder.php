<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['kode' => 'AREA-001', 'nama' => 'Pit A (North)', 'status' => 'active'],
            ['kode' => 'AREA-002', 'nama' => 'Pit B (South)', 'status' => 'active'],
            ['kode' => 'AREA-003', 'nama' => 'Pit C (East)', 'status' => 'active'],
            ['kode' => 'AREA-004', 'nama' => 'Pit D (West)', 'status' => 'active'],
            ['kode' => 'AREA-005', 'nama' => 'Disposal 1 (North Dump)', 'status' => 'active'],
            ['kode' => 'AREA-006', 'nama' => 'Disposal 2 (East Dump)', 'status' => 'active'],
            ['kode' => 'AREA-007', 'nama' => 'Hauling Road A', 'status' => 'active'],
            ['kode' => 'AREA-008', 'nama' => 'Hauling Road B', 'status' => 'active'],
            ['kode' => 'AREA-009', 'nama' => 'Stockpile 1 (Raw)', 'status' => 'active'],
            ['kode' => 'AREA-010', 'nama' => 'Stockpile 2 (Blending)', 'status' => 'active'],
            ['kode' => 'AREA-011', 'nama' => 'Crusher Area 1', 'status' => 'active'],
            ['kode' => 'AREA-012', 'nama' => 'Workshop Central', 'status' => 'active'],
            ['kode' => 'AREA-013', 'nama' => 'Fuel Station 1', 'status' => 'active'],
            ['kode' => 'AREA-014', 'nama' => 'Port Area', 'status' => 'active'],
        ];

        foreach ($areas as $area) {
            Area::updateOrCreate(
                ['kode' => $area['kode']],
                ['nama' => $area['nama'], 'status' => $area['status']]
            );
        }
    }
}
