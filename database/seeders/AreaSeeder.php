<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areaNames = ['Pit A', 'Pit B', 'Pit C', 'Pit D', 'Pit E', 'Pit F', 'Pit North', 'Pit South', 'Pit East', 'Pit West', 'Disposal 1', 'Disposal 2', 'Disposal 3', 'Disposal North', 'Disposal South', 'Hauling Road A', 'Hauling Road B', 'Hauling Road C', 'Stockpile 1', 'Stockpile 2', 'Stockpile 3', 'Crusher Area 1', 'Crusher Area 2', 'Port Area', 'Workshop Area', 'Office Area', 'Camp Area', 'Fuel Station 1', 'Fuel Station 2', 'Explosive Magazine', 'Nursery Area'];

        for ($i = 0; $i < 31; $i++) {
            \App\Models\Area::create([
                'nama' => $areaNames[$i] ?? ('Area Tambang ' . ($i + 1)),
                'kode' => 'AREA-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
        }
    }
}
