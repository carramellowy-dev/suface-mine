<?php

namespace Database\Seeders;

use App\Models\DailyTarget;
use App\Models\Material;
use Illuminate\Database\Seeder;

class DailyTargetSeeder extends Seeder
{
    public function run(): void
    {
        $materials = Material::whereIn('kategori', ['ore', 'waste'])->get();

        foreach ($materials as $material) {
            $baseTarget = match ($material->kode) {
                'ORE-001' => 120,
                'ORE-002' => 80,
                'WST-001' => 150,
                'WST-002' => 60,
                default => 50,
            };

            // Harian
            DailyTarget::updateOrCreate(
                ['material_id' => $material->id, 'periode' => 'harian'],
                ['target_ritasi' => $baseTarget]
            );

            // Mingguan (~6.5x harian)
            DailyTarget::updateOrCreate(
                ['material_id' => $material->id, 'periode' => 'mingguan'],
                ['target_ritasi' => $baseTarget * 6]
            );

            // Bulanan (~26x harian)
            DailyTarget::updateOrCreate(
                ['material_id' => $material->id, 'periode' => 'bulanan'],
                ['target_ritasi' => $baseTarget * 26]
            );
        }
    }
}
