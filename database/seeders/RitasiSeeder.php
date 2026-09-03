<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Material;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RitasiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = Pegawai::orderBy('id')->get();
        // Operational dump trucks (DT-003 to DT-006)
        $dtUnits = Unit::where('tipe', 'dump_truck')
            ->whereNotIn('kode', ['DT-001', 'DT-002'])
            ->get();
        $areas = Area::where('status', 'active')->get();
        $materials = Material::whereIn('kategori', ['ore', 'waste'])->get();
        $spvUser = User::where('role', 'spv')->first();

        if ($pegawais->isEmpty() || $dtUnits->isEmpty() || $areas->isEmpty() || $materials->isEmpty()) {
            return;
        }

        $lokasiList = ['Pit A North', 'Pit B South', 'Disposal 1', 'Hauling Road A', 'Stockpile 1', 'Pit C East'];
        $deskripsiList = ['Hauling Bauxite Ore ke Stockpile', 'Hauling Overburden ke Disposal 1', 'Hauling Mining Tuff ke Crusher'];
        $shifts = ['siang', 'malam'];

        $rows = [];

        // Generate past 30 days of hauling data (from day 1 to 30; day 0 = today, leaving day 0 clear for manual test)
        for ($day = 1; $day <= 30; $day++) {
            $tanggal = now()->subDays($day)->format('Y-m-d');

            foreach ($shifts as $shift) {
                // Select a subset of pegawais for this shift
                $shiftPegawais = $pegawais->shuffle()->take(rand(5, 10));

                foreach ($shiftPegawais as $peg) {
                    $unit = $dtUnits->random();
                    $material = $materials->random();
                    $area = $areas->random();

                    $hmAwal = 5000 + ($day * 12) + rand(0, 50) + (rand(0, 99) / 100);
                    $hmTotal = rand(6, 10) + (rand(0, 99) / 100);
                    $hmAkhir = $hmAwal + $hmTotal;
                    $ritasi = rand(10, 24);
                    $quantity = $ritasi * ($material->kategori === 'ore' ? rand(45, 85) : rand(40, 75));
                    $fuel = round($hmTotal * rand(30, 42), 2);
                    $isValidated = $day > 1;

                    $rows[] = [
                        'pegawai_id' => $peg->id,
                        'unit_id' => $unit->id,
                        'area_id' => $area->id,
                        'material_id' => $material->id,
                        'tanggal' => $tanggal,
                        'shift' => $shift,
                        'hm_awal' => $hmAwal,
                        'hm_akhir' => $hmAkhir,
                        'hm_total' => $hmTotal,
                        'jumlah_ritasi' => $ritasi,
                        'quantity' => $quantity,
                        'quantity_unit' => 'ton',
                        'fuel_consumption' => $fuel,
                        'lokasi_pekerjaan' => $lokasiList[array_rand($lokasiList)],
                        'deskripsi_pekerjaan' => $deskripsiList[array_rand($deskripsiList)],
                        'kendala' => rand(0, 10) > 8 ? 'Antrean di crusher sempat padat 15 menit' : null,
                        'status' => $isValidated ? 'validated' : 'pending',
                        'validated_by' => $isValidated ? $spvUser?->id : null,
                        'validated_at' => $isValidated ? now()->subDays($day)->addHours(12) : null,
                        'created_at' => now()->subDays($day),
                        'updated_at' => now()->subDays($day),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('ritasis')->insert($chunk);
        }
    }
}
