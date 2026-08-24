<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            ['kode' => 'MAT-BX-001', 'nama' => 'Bauxite Ore (Raw)', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 15000, 'stok_minimal' => 5000, 'status' => 'active'],
            ['kode' => 'MAT-AL-045', 'nama' => 'Processed Alumina', 'satuan' => 'Kilograms (kg)', 'kategori' => 'ore', 'stok' => 8000, 'stok_minimal' => 2000, 'status' => 'active'],
            ['kode' => 'CNS-LB-112', 'nama' => 'Industrial Lubricant (Heavy)', 'satuan' => 'Liters (L)', 'kategori' => 'lubricant', 'stok' => 150, 'stok_minimal' => 200, 'status' => 'low_stock'],
            ['kode' => 'PRT-CB-099', 'nama' => 'Obsolete Conveyor Belt (Type B)', 'satuan' => 'Meters (m)', 'kategori' => 'spare_part', 'stok' => 0, 'stok_minimal' => 50, 'status' => 'inactive'],
            ['kode' => 'EXP-DC-002', 'nama' => 'Detonator Cord (Standard)', 'satuan' => 'Meters (m)', 'kategori' => 'explosive', 'stok' => 500, 'stok_minimal' => 100, 'status' => 'restricted'],
            ['kode' => 'FUEL-DS-001', 'nama' => 'Diesel Fuel', 'satuan' => 'Liters (L)', 'kategori' => 'fuel', 'stok' => 50000, 'stok_minimal' => 10000, 'status' => 'active'],
            ['kode' => 'MAT-WA-001', 'nama' => 'Waste', 'satuan' => 'Tonnes (t)', 'kategori' => 'waste', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-MU-001', 'nama' => 'Mud - Lumpur', 'satuan' => 'Tonnes (t)', 'kategori' => 'waste', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-PA-001', 'nama' => 'Pasir Hitam', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-MT-001', 'nama' => 'Mining Tuff', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-BP-001', 'nama' => 'Batu Pica (5/15)', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-TO-001', 'nama' => 'Tuff Off', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-KC-001', 'nama' => 'KCN', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-CG-001', 'nama' => 'Cake', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
            ['kode' => 'MAT-DS-001', 'nama' => 'DSTuff', 'satuan' => 'Tonnes (t)', 'kategori' => 'ore', 'stok' => 99999, 'stok_minimal' => 0, 'status' => 'active'],
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }
    }
}
