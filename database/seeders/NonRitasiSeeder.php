<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NonRitasiSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = Pegawai::orderBy('id')->get();
        // Support equipment: Excavators, Bulldozers, Grader, Loader
        $supportUnits = Unit::whereIn('tipe', ['excavator', 'bulldozer', 'motor_grader', 'loader'])
            ->where('is_active', true)
            ->get();
        $areas = Area::where('status', 'active')->get();
        $spvs = User::where('role', 'spv')->get();
        $seniorSpvs = User::where('role', 'senior_spv')->get();
        $spvUser = $spvs->first();

        if ($pegawais->isEmpty() || $supportUnits->isEmpty() || $areas->isEmpty()) {
            return;
        }

        $deskripsiGeneral = ['Dozing material di Disposal 1', 'Grading & perawatan Hauling Road A', 'Drilling & blasting preparation', 'Excavating & ditching saluran air', 'Pushing topsoil di area Pit A'];
        $deskripsiNonRitasi = ['Perbaikan jalan tambang', 'Perataan lereng disposal', 'Pembersihan area pit dari lumpur', 'Penggarukan overburden keras'];
        $shifts = ['siang', 'malam'];
        $jamMulaiList = ['06:00', '07:00', '18:00', '19:00'];
        $jamSelesaiList = ['17:00', '18:00', '05:00', '06:00'];

        $rows = [];

        // Support and general activities including day 0 (today) and past 30 days
        for ($day = 0; $day <= 30; $day++) {
            $tanggal = now()->subDays($day)->format('Y-m-d');

            foreach ($shifts as $shift) {
                // Avoid assigning pegawais who are already in ritasi for this tanggal + shift
                $ritasiAssigned = DB::table('ritasis')
                    ->where('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->pluck('pegawai_id')
                    ->toArray();

                $availablePegawais = $pegawais->whereNotIn('id', $ritasiAssigned);
                if ($availablePegawais->isEmpty()) {
                    continue;
                }

                $shiftPegawais = $availablePegawais->shuffle()->take(rand(1, 4));
                $usedSlot = [];

                foreach ($shiftPegawais as $peg) {
                    if (isset($usedSlot[$peg->id])) {
                        continue;
                    }
                    // Also make sure we don't collide if already used in ritasi (or non-ritasi table unique constraint)
                    $usedSlot[$peg->id] = true;

                    $unit = $supportUnits->random();
                    $area = $areas->random();
                    $isGeneral = (rand(0, 100) < 50);

                    $hmAwal = 3000 + ($day * 10) + rand(0, 40) + (rand(0, 99) / 100);
                    $hmTotal = rand(5, 9) + (rand(0, 99) / 100);
                    $hmAkhir = $hmAwal + $hmTotal;
                    $isValidated = $day > 1;

                    $selectedSpv = $isGeneral && $spvs->isNotEmpty() ? $spvs->random()->id : null;
                    $selectedSrSpv = $isGeneral && $seniorSpvs->isNotEmpty() ? $seniorSpvs->random()->id : null;

                    $rows[] = [
                        'pegawai_id' => $peg->id,
                        'unit_id' => $isGeneral ? null : $unit->id,
                        'supervisor_id' => $selectedSpv,
                        'senior_spv_id' => $selectedSrSpv,
                        'area_id' => $area->id,
                        'tanggal' => $tanggal,
                        'shift' => $shift,
                        'hm_awal' => $isGeneral ? null : $hmAwal,
                        'hm_akhir' => $isGeneral ? null : $hmAkhir,
                        'hm_total' => $isGeneral ? null : $hmTotal,
                        'jam_mulai' => $isGeneral ? $jamMulaiList[array_rand($jamMulaiList)] : null,
                        'jam_selesai' => $isGeneral ? $jamSelesaiList[array_rand($jamSelesaiList)] : null,
                        'is_overtime' => $isGeneral ? (rand(0, 100) < 25) : false,
                        'deskripsi_pekerjaan' => $isGeneral
                            ? $deskripsiGeneral[array_rand($deskripsiGeneral)]
                            : $deskripsiNonRitasi[array_rand($deskripsiNonRitasi)],
                        'fuel_consumption' => $isGeneral ? null : round($hmTotal * rand(25, 45), 2),
                        'kendala' => rand(0, 10) > 8 ? 'Kondisi hujan licin 30 menit' : null,
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
            DB::table('non_ritasis')->insert($chunk);
        }
    }
}
