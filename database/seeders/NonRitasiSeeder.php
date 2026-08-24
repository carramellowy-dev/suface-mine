<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\Area;

class NonRitasiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawai = Pegawai::all(['id']);
        // Non-ritasi & general tasks use support equipment (skip units in maintenance).
        $units = Unit::whereIn('tipe', ['excavator', 'bulldozer', 'motor_grader', 'loader'])
            ->whereNotIn('status', ['maintenance', 'breakdown'])
            ->get();
        $areas = Area::take(15)->get();

        if ($pegawai->isEmpty() || $units->isEmpty() || $areas->isEmpty()) {
            return;
        }

        $lokasiList = ['Pit 1 North', 'Pit 2 South', 'Workshop', 'Office Area', 'Fuel Station', 'Stockpile 1', 'Haul Road A', 'Crusher Area'];
        $deskripsiGeneral = ['Dozing', 'Grading', 'Drilling', 'Blasting preparation', 'Excavating', 'Loading', 'Pushing', 'Maintaining haul road'];
        $deskripsiNonRitasi = ['Perbaikan jalan produksi', 'Perataan area disposal', 'Pembersihan area pit', 'Penggarukan overburden'];
        $shifts = ['siang', 'malam'];
        $jamMulaiList = ['06:00', '07:00', '18:00', '19:00'];
        $jamSelesaiList = ['17:00', '18:00', '05:00', '06:00'];

        $rows = [];

        for ($day = 0; $day < 30; $day++) {
            $tanggal = now()->subDays($day)->format('Y-m-d');

            foreach ($shifts as $shift) {
                $operators = $pegawai->shuffle()->take(rand(8, 18));
                $usedThisSlot = [];

                foreach ($operators as $peg) {
                    // Avoid duplicate (pegawai, tanggal, shift) within this slot.
                    if (isset($usedThisSlot[$peg->id])) {
                        continue;
                    }
                    $usedThisSlot[$peg->id] = true;

                    $unit = $units->random();
                    $isGeneral = rand(0, 100) < 50; // ~50% general, 50% non-ritasi
                    $hmAwal = rand(5000, 14000) + (rand(0, 99) / 100);
                    $hmTotal = rand(6, 11) + (rand(0, 99) / 100);
                    $now = now();

                    $rows[] = [
                        'pegawai_id' => $peg->id,
                        'unit_id' => $unit->id,
                        'area_id' => $areas->random()->id,
                        'tanggal' => $tanggal,
                        'shift' => $shift,
                        'hm_awal' => $hmAwal,
                        'hm_akhir' => $hmAwal + $hmTotal,
                        'hm_total' => $hmTotal,
                        'jam_mulai' => $isGeneral ? $jamMulaiList[array_rand($jamMulaiList)] : null,
                        'jam_selesai' => $isGeneral ? $jamSelesaiList[array_rand($jamSelesaiList)] : null,
                        'is_overtime' => $isGeneral ? (rand(0, 100) < 20) : false,
                        'lokasi_pekerjaan' => $lokasiList[array_rand($lokasiList)],
                        'deskripsi_pekerjaan' => $isGeneral
                            ? $deskripsiGeneral[array_rand($deskripsiGeneral)]
                            : $deskripsiNonRitasi[array_rand($deskripsiNonRitasi)],
                        'status' => $day > 2 ? 'validated' : 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('non_ritasis')->insert($chunk);
        }
    }
}

