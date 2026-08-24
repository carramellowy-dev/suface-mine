<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyTarget;
use App\Models\Material;

class DailyTargetSeeder extends Seeder
{
    const PERIODS = ['harian', 'mingguan', 'bulanan'];

    public function run(): void
    {
        $materials = Material::whereIn('nama', ['Bauxite Ore (Raw)', 'Mining Tuff', 'Cake', 'Pasir Hitam', 'Tuff Off'])->get();

        if ($materials->isEmpty()) {
            return;
        }

        foreach ($materials as $material) {
            foreach (self::PERIODS as $periode) {
                $target = match ([$material->nama, $periode]) {
                    ['Bauxite Ore (Raw)', 'harian'] => rand(80, 150),
                    ['Bauxite Ore (Raw)', 'mingguan'] => rand(500, 900),
                    ['Bauxite Ore (Raw)', 'bulanan'] => rand(2200, 3600),
                    ['Mining Tuff', 'harian'] => rand(30, 70),
                    ['Mining Tuff', 'mingguan'] => rand(180, 400),
                    ['Mining Tuff', 'bulanan'] => rand(800, 1600),
                    ['Cake', 'harian'] => rand(20, 50),
                    ['Cake', 'mingguan'] => rand(120, 300),
                    ['Cake', 'bulanan'] => rand(500, 1200),
                    ['Pasir Hitam', 'harian'] => rand(40, 80),
                    ['Pasir Hitam', 'mingguan'] => rand(240, 460),
                    ['Pasir Hitam', 'bulanan'] => rand(1000, 1800),
                    ['Tuff Off', 'harian'] => rand(25, 60),
                    ['Tuff Off', 'mingguan'] => rand(150, 350),
                    ['Tuff Off', 'bulanan'] => rand(600, 1400),
                    default => rand(20, 60),
                };

                // Target is set once per material per period (not per date).
                DailyTarget::updateOrCreate(
                    ['material_id' => $material->id, 'periode' => $periode],
                    ['target_ritasi' => $target]
                );
            }
        }
    }
}
