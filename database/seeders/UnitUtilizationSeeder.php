<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\UnitUtilization;
use App\Models\User;

class UnitUtilizationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id');
        if ($users->isEmpty()) {
            return;
        }

        $descBreakdown = [
            'Unit macet di lokasi, perlu penarikan',
            'Ganti selang hidrolik bocor',
            'Overheat engine, suhu mesin tinggi',
            'Kerusakan ban / track',
            'Ganti komponen hidrolik utama',
            'Kerusakan transmisi / final drive',
        ];
        $descServis = [
            'Perawatan berkala (PMP)',
            'Ganti oli mesin & filter',
            'Servis mesin lengkap',
            'Perbaikan undercarriage',
            'Servis transmisi & girboks',
            'Kalibrasi & penggantian komponen hidrolik',
        ];

        // --- Units that are CURRENTLY in maintenance (open record, no ended_at) ---
        $inMaintenance = Unit::whereIn('status', ['maintenance', 'breakdown'])->get();
        foreach ($inMaintenance as $u) {
            $isBreakdown = $u->status === 'breakdown';

            // Give a recently-past completed breakdown/servis for realism before the open one.
            if (rand(0, 1) === 1) {
                $doneStart = now()->subDays(rand(4, 8))->setTime(7, 0)->addHours(rand(0, 4));
                $doneEnd = $doneStart->copy()->addHours(rand(8, 16));
                UnitUtilization::create([
                    'unit_id' => $u->id,
                    'status' => 'breakdown',
                    'started_at' => $doneStart,
                    'ended_at' => $doneEnd,
                    'deskripsi' => $descBreakdown[array_rand($descBreakdown)],
                    'user_id' => $users->random(),
                ]);
            }

            UnitUtilization::create([
                'unit_id' => $u->id,
                'status' => $isBreakdown ? 'breakdown' : 'servis',
                'started_at' => now()->subDays(rand(0, 3))->setTime(8, 0)->addHours(rand(0, 3)),
                'ended_at' => null, // masih berlangsung
                'deskripsi' => ($isBreakdown ? $descBreakdown : $descServis)[array_rand($isBreakdown ? $descBreakdown : $descServis)],
                'user_id' => $users->random(),
            ]);
        }

        // --- Units that ALREADY went through maintenance (completed chain → now ready) ---
        $scheduled = Unit::whereNotIn('status', ['maintenance', 'breakdown'])->where('is_active', true)->get();
        foreach ($scheduled as $u) {
            // Only some units had maintenance recently (app is "in production").
            if (rand(0, 100) >= 65) {
                continue;
            }

            $bdStart = now()->subDays(rand(2, 12))->setTime(7, 0)->addHours(rand(0, 3));
            $bdEnd = $bdStart->copy()->addHours(rand(6, 14));
            $svStart = $bdEnd;
            $svEnd = $svStart->copy()->addHours(rand(18, 60));

            UnitUtilization::create([
                'unit_id' => $u->id,
                'status' => 'breakdown',
                'started_at' => $bdStart,
                'ended_at' => $bdEnd,
                'deskripsi' => $descBreakdown[array_rand($descBreakdown)],
                'user_id' => $users->random(),
            ]);
            UnitUtilization::create([
                'unit_id' => $u->id,
                'status' => 'servis',
                'started_at' => $svStart,
                'ended_at' => $svEnd,
                'deskripsi' => $descServis[array_rand($descServis)],
                'user_id' => $users->random(),
            ]);
            // Latest record = ready → dashboard counts unit as active (already out of maintenance).
            UnitUtilization::create([
                'unit_id' => $u->id,
                'status' => 'ready',
                'started_at' => $svEnd->copy()->addMinutes(rand(5, 30)),
                'ended_at' => null,
                'deskripsi' => 'Selesai maintenance, unit siap operasi',
                'user_id' => $users->random(),
            ]);
        }
    }
}
