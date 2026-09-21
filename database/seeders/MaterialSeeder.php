<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            [
                'kode' => 'ORE-001',
                'nama' => 'Bauxite Ore (Raw)',
                'satuan' => 'M3',
                'kategori' => 'ore',
                'stok' => 25000,
                'stok_minimal' => 5000,
                'status' => 'active',
                'is_active' => true,
                'unit_default' => 'm3',
                'to_ton_factor' => 1.0,
            ],
            [
                'kode' => 'ORE-002',
                'nama' => 'Nickel Ore (High Grade)',
                'satuan' => 'M3',
                'kategori' => 'ore',
                'stok' => 18000,
                'stok_minimal' => 4000,
                'status' => 'active',
                'is_active' => true,
                'unit_default' => 'm3',
                'to_ton_factor' => 1.0,
            ],
            [
                'kode' => 'WST-001',
                'nama' => 'Overburden',
                'satuan' => 'M3',
                'kategori' => 'waste',
                'stok' => 99999,
                'stok_minimal' => 0,
                'status' => 'active',
                'is_active' => true,
                'unit_default' => 'm3',
                'to_ton_factor' => 1.3,
            ],
            [
                'kode' => 'WST-002',
                'nama' => 'Mining Tuff',
                'satuan' => 'M3',
                'kategori' => 'waste',
                'stok' => 50000,
                'stok_minimal' => 0,
                'status' => 'active',
                'is_active' => true,
                'unit_default' => 'm3',
                'to_ton_factor' => 1.0,
            ],
            [
                'kode' => 'FUL-001',
                'nama' => 'Solar B35 (High Speed Diesel)',
                'satuan' => 'M3',
                'kategori' => 'fuel',
                'stok' => 45000,
                'stok_minimal' => 10000,
                'status' => 'active',
                'is_active' => true,
                'unit_default' => 'm3',
                'to_ton_factor' => 0.00085,
            ],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['kode' => $material['kode']],
                $material
            );
        }
    }
}
