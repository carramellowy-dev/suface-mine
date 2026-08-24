<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['kode' => 'EXC-001', 'nama' => 'Excavator PC2000-8', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC2000-8', 'tahun' => 2021, 'status' => 'active', 'kapasitas' => 10.5, 'fcr' => 32.5, 'last' => 28, 'cycle' => 90, 'ket' => null],
            ['kode' => 'DT-104', 'nama' => 'Dump Truck HD785-7', 'tipe' => 'dump_truck', 'merk' => 'Komatsu', 'model' => 'HD785-7', 'tahun' => 2020, 'status' => 'active', 'kapasitas' => 45, 'fcr' => 38.0, 'last' => 10, 'cycle' => 60, 'ket' => null],
            ['kode' => 'BDZ-022', 'nama' => 'Bulldozer D375A-6', 'tipe' => 'bulldozer', 'merk' => 'Komatsu', 'model' => 'D375A-6', 'tahun' => 2018, 'status' => 'maintenance', 'kapasitas' => 0, 'fcr' => 40.0, 'last' => 5, 'cycle' => 45, 'ket' => 'Overhaul engine & perbaikan undercarriage'],
            ['kode' => 'EXC-005', 'nama' => 'Excavator R220LC-9', 'tipe' => 'excavator', 'merk' => 'Hyundai', 'model' => 'R220LC-9', 'tahun' => 2019, 'status' => 'breakdown', 'kapasitas' => 8.0, 'fcr' => 28.0, 'last' => 2, 'cycle' => 90, 'ket' => 'Ganti komponen hidrolik utama (breakdown)'],
            ['kode' => 'MG-011', 'nama' => 'Motor Grader GD825A-2', 'tipe' => 'motor_grader', 'merk' => 'Komatsu', 'model' => 'GD825A-2', 'tahun' => 2022, 'status' => 'standby', 'kapasitas' => 0, 'fcr' => 22.0, 'last' => 15, 'cycle' => 120, 'ket' => null],
            ['kode' => 'EXC-022', 'nama' => 'Excavator Long Arm', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC1250-8', 'tahun' => 2020, 'status' => 'active', 'kapasitas' => 11.0, 'fcr' => 34.0, 'last' => 20, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-024', 'nama' => 'Excavator PC320', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC320-8', 'tahun' => 2021, 'status' => 'active', 'kapasitas' => 9.0, 'fcr' => 30.0, 'last' => 35, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-025', 'nama' => 'Excavator PC320', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC320-8', 'tahun' => 2021, 'status' => 'active', 'kapasitas' => 9.0, 'fcr' => 30.0, 'last' => 12, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-027', 'nama' => 'Excavator PC340', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC340-8', 'tahun' => 2022, 'status' => 'active', 'kapasitas' => 9.5, 'fcr' => 31.0, 'last' => 40, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-028', 'nama' => 'Excavator PC320', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC320-8', 'tahun' => 2020, 'status' => 'active', 'kapasitas' => 9.0, 'fcr' => 30.0, 'last' => 8, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-029', 'nama' => 'Excavator SY215', 'tipe' => 'excavator', 'merk' => 'Sany', 'model' => 'SY215C', 'tahun' => 2022, 'status' => 'active', 'kapasitas' => 7.0, 'fcr' => 26.0, 'last' => 25, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-032', 'nama' => 'Excavator SY215', 'tipe' => 'excavator', 'merk' => 'Sany', 'model' => 'SY215C', 'tahun' => 2023, 'status' => 'active', 'kapasitas' => 7.0, 'fcr' => 26.0, 'last' => 18, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-033', 'nama' => 'Excavator SY215', 'tipe' => 'excavator', 'merk' => 'Sany', 'model' => 'SY215C', 'tahun' => 2023, 'status' => 'active', 'kapasitas' => 7.0, 'fcr' => 26.0, 'last' => 30, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-034', 'nama' => 'Excavator SY215', 'tipe' => 'excavator', 'merk' => 'Sany', 'model' => 'SY215C', 'tahun' => 2023, 'status' => 'active', 'kapasitas' => 7.0, 'fcr' => 26.0, 'last' => 6, 'cycle' => 90, 'ket' => null],
            ['kode' => 'EXC-035', 'nama' => 'Excavator PC300', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC300-8', 'tahun' => 2022, 'status' => 'maintenance', 'kapasitas' => 9.0, 'fcr' => 30.0, 'last' => 3, 'cycle' => 90, 'ket' => 'Perawatan berkala & ganti filter hidrolik'],
            ['kode' => 'DT-1042', 'nama' => 'Dump Truck DT-1042', 'tipe' => 'dump_truck', 'merk' => 'Komatsu', 'model' => 'HD785-7', 'tahun' => 2020, 'status' => 'active', 'kapasitas' => 45, 'fcr' => 38.0, 'last' => 22, 'cycle' => 60, 'ket' => null],
            ['kode' => 'DT-1055', 'nama' => 'Dump Truck DT-1055', 'tipe' => 'dump_truck', 'merk' => 'Komatsu', 'model' => 'HD785-7', 'tahun' => 2021, 'status' => 'active', 'kapasitas' => 45, 'fcr' => 38.0, 'last' => 4, 'cycle' => 60, 'ket' => null],
            ['kode' => 'DT-1060', 'nama' => 'Dump Truck HD465-7', 'tipe' => 'dump_truck', 'merk' => 'Komatsu', 'model' => 'HD465-7', 'tahun' => 2021, 'status' => 'maintenance', 'kapasitas' => 40, 'fcr' => 36.0, 'last' => 1, 'cycle' => 60, 'ket' => 'Servis mesin & transmisi'],
            ['kode' => 'EX-2015', 'nama' => 'Excavator EX-2015', 'tipe' => 'excavator', 'merk' => 'Komatsu', 'model' => 'PC200-8', 'tahun' => 2019, 'status' => 'active', 'kapasitas' => 7.5, 'fcr' => 27.0, 'last' => 14, 'cycle' => 90, 'ket' => null],
            ['kode' => 'LV-008', 'nama' => 'Leviathan LV-008', 'tipe' => 'loader', 'merk' => 'Caterpillar', 'model' => '966M', 'tahun' => 2020, 'status' => 'active', 'kapasitas' => 5.5, 'fcr' => 24.0, 'last' => 26, 'cycle' => 75, 'ket' => null],
        ];

        foreach ($units as $u) {
            $lastMaintenance = now()->subDays($u['last'])->startOfDay()->addHours(rand(8, 16));

            Unit::create([
                'kode' => $u['kode'],
                'nama' => $u['nama'],
                'tipe' => $u['tipe'],
                'merk' => $u['merk'],
                'model' => $u['model'],
                'tahun' => $u['tahun'],
                'status' => $u['status'],
                'kapasitas' => $u['kapasitas'],
                'fuel_consumption_rate' => $u['fcr'],
                'last_maintenance' => $lastMaintenance->toDateString(),
                'next_maintenance' => $lastMaintenance->copy()->addDays($u['cycle'])->toDateString(),
                'keterangan' => $u['ket'],
                'is_active' => true,
            ]);
        }
    }
}
