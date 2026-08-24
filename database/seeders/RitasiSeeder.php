<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ritasi;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\Area;
use App\Models\Material;

class RitasiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = Pegawai::all(['id']);
        // Only operational dump trucks are assigned for hauling (skip units in maintenance).
        $dtUnits = Unit::where('tipe', 'dump_truck')->whereNotIn('status', ['maintenance', 'breakdown'])->get();
        $areas = Area::take(15)->get();
        $materials = Material::whereIn('kategori', ['ore', 'waste'])->get();

        if ($pegawai->isEmpty() || $dtUnits->isEmpty() || $areas->isEmpty() || $materials->isEmpty()) {
            return;
        }

        $lokasiList = ['Pit 1 North', 'Pit 2 South', 'East Dump', 'Haul Road A', 'South Pit', 'North Pit', 'West Pit', 'Stockpile 1', 'Stockpile 2', 'Crusher Area'];
        $deskripsiList = ['Hauling ore', 'Hauling waste', 'Hauling overburden', 'Hauling topsoil'];
        $shifts = ['siang', 'malam'];

        $rows = [];
        $usedOperatorIds = [];

        for ($day = 0; $day < 30; $day++) {
            $tanggal = now()->subDays($day)->format('Y-m-d');

            foreach ($shifts as $shift) {
                $operators = $pegawai->shuffle()->take(rand(14, 34));

                foreach ($operators as $peg) {
                    $unit = $dtUnits->random();
                    $hmAwal = rand(8000, 16000) + (rand(0, 99) / 100);
                    $hmTotal = rand(6, 11) + (rand(0, 99) / 100);
                    $jumlahRitasi = rand(8, 25);
                    $material = $materials->random();
                    $tonPerRit = match ($material->kategori) {
                        'ore' => rand(30, 80),
                        default => rand(40, 100),
                    };

                    $now = now();
                    $rows[] = [
                        'pegawai_id' => $peg->id,
                        'unit_id' => $unit->id,
                        'area_id' => $areas->random()->id,
                        'material_id' => $material->id,
                        'tanggal' => $tanggal,
                        'shift' => $shift,
                        'hm_awal' => $hmAwal,
                        'hm_akhir' => $hmAwal + $hmTotal,
                        'hm_total' => $hmTotal,
                        'jumlah_ritasi' => $jumlahRitasi,
                        'quantity' => $jumlahRitasi * $tonPerRit,
                        'quantity_unit' => 'ton',
                        'fuel_consumption' => $hmTotal * rand(25, 45),
                        'lokasi_pekerjaan' => $lokasiList[array_rand($lokasiList)],
                        'deskripsi_pekerjaan' => $deskripsiList[array_rand($deskripsiList)],
                        'status' => $day > 2 ? 'validated' : 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $usedOperatorIds[$peg->id] = true;
                }
            }
        }

        // Ensure EVERY operator has at least one recorded hauling entry.
        $uncovered = $pegawai->whereNotIn('id', array_keys($usedOperatorIds));

        foreach ($uncovered as $peg) {
            $now = now();
            $rows[] = [
                'pegawai_id' => $peg->id,
                'unit_id' => $dtUnits->random()->id,
                'area_id' => $areas->random()->id,
                'material_id' => $materials->random()->id,
                'tanggal' => now()->subDays(rand(0, 6))->format('Y-m-d'),
                'shift' => $shifts[array_rand($shifts)],
                'hm_awal' => rand(9000, 15000) + (rand(0, 99) / 100),
                'hm_akhir' => rand(9000, 15000) + (rand(0, 99) / 100) + rand(6, 10) + (rand(0, 99) / 100),
                'hm_total' => rand(6, 10) + (rand(0, 99) / 100),
                'jumlah_ritasi' => rand(8, 22),
                'quantity' => rand(8, 22) * rand(40, 90),
                'quantity_unit' => 'ton',
                'fuel_consumption' => rand(150, 450),
                'lokasi_pekerjaan' => $lokasiList[array_rand($lokasiList)],
                'deskripsi_pekerjaan' => $deskripsiList[array_rand($deskripsiList)],
                'status' => 'validated',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('ritasis')->insert($chunk);
        }
    }
}

