<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UnitUtilization;
use App\Models\User;
use Illuminate\Database\Seeder;

class UnitUtilizationSeeder extends Seeder
{
    public function run(): void
    {
        $operators = User::where('role', 'pegawai')->get();
        if ($operators->isEmpty()) {
            return;
        }

        $descBreakdown = [
            'Overheat engine & indikator temperatur tinggi',
            'Ganti selang hidrolik utama bocor',
            'Kerusakan transmisi dan final drive',
            'Kerusakan track shoe / baut roda patah',
            'Sistem pengereman berkurang tekanan angin',
        ];

        $descServis = [
            'Perawatan berkala 250 Jam (PMP)',
            'Ganti oli engine, transmisi, & filter hidrolik',
            'Servis sistem injeksi dan pendingin',
            'Penggantian grease autolube & kalibrasi',
        ];

        $allUnits = Unit::all();

        // 1. DT-001: Active Breakdown reported by operator1
        $dt001 = $allUnits->where('kode', 'DT-001')->first();
        if ($dt001) {
            // Past maintenance completed
            $pastStart = now()->subDays(7)->setTime(7, 30);
            $pastEnd = $pastStart->copy()->addHours(6);
            UnitUtilization::create([
                'unit_id' => $dt001->id,
                'status' => 'breakdown',
                'started_at' => $pastStart,
                'ended_at' => $pastEnd,
                'deskripsi' => 'Ganti selang hidrolik bocor',
                'user_id' => $operators[0]->id,
            ]);
            UnitUtilization::create([
                'unit_id' => $dt001->id,
                'status' => 'ready',
                'started_at' => $pastEnd->copy()->addMinutes(15),
                'ended_at' => $pastEnd->copy()->addMinutes(15),
                'deskripsi' => 'Perbaikan selesai, unit siap operasi',
                'user_id' => $operators[0]->id,
            ]);

            // Active breakdown now
            UnitUtilization::create([
                'unit_id' => $dt001->id,
                'status' => 'breakdown',
                'started_at' => now()->subDay()->setTime(8, 0),
                'ended_at' => null,
                'deskripsi' => 'Overheat engine & selang hidrolik bocor di Pit A',
                'user_id' => $operators[0]->id,
            ]);
        }

        // 2. DT-002: Active Servis reported by operator2
        $dt002 = $allUnits->where('kode', 'DT-002')->first();
        if ($dt002) {
            UnitUtilization::create([
                'unit_id' => $dt002->id,
                'status' => 'servis',
                'started_at' => now()->subDay()->setTime(6, 30),
                'ended_at' => null,
                'deskripsi' => 'Perawatan berkala 500 Jam (PMP) di Workshop',
                'user_id' => $operators[1]->id,
            ]);
        }

        // 3. Historical completed logs for other units
        foreach ($allUnits as $u) {
            if (in_array($u->kode, ['DT-001', 'DT-002'])) {
                continue;
            }

            // Create completed maintenance chains in the past 14 days
            $bdStart = now()->subDays(rand(4, 14))->setTime(7, 0)->addHours(rand(0, 3));
            $bdEnd = $bdStart->copy()->addHours(rand(4, 8));
            $svStart = $bdEnd;
            $svEnd = $svStart->copy()->addHours(rand(8, 24));
            $op = $operators->random();

            UnitUtilization::create([
                'unit_id' => $u->id,
                'status' => 'breakdown',
                'started_at' => $bdStart,
                'ended_at' => $bdEnd,
                'deskripsi' => $descBreakdown[array_rand($descBreakdown)],
                'user_id' => $op->id,
            ]);

            UnitUtilization::create([
                'unit_id' => $u->id,
                'status' => 'servis',
                'started_at' => $svStart,
                'ended_at' => $svEnd,
                'deskripsi' => $descServis[array_rand($descServis)],
                'user_id' => $op->id,
            ]);

            $readyTime = $svEnd->copy()->addMinutes(rand(10, 30));
            UnitUtilization::create([
                'unit_id' => $u->id,
                'status' => 'ready',
                'started_at' => $readyTime,
                'ended_at' => $readyTime,
                'deskripsi' => 'Selesai perbaikan/servis, unit beroperasi normal',
                'user_id' => $op->id,
            ]);
        }
    }
}
