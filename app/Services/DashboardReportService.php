<?php

namespace App\Services;

use App\Models\Ritasi;
use App\Models\NonRitasi;
use App\Models\Unit;
use App\Models\UnitUtilization;
use App\Models\DailyTarget;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardReportService
{
    public function dailyData(Request $request): array
    {
        [$start, $end] = $this->range($request, 'daily');
        return $this->build($start, $end, 'daily', $request);
    }

    public function weeklyData(Request $request): array
    {
        [$start, $end] = $this->range($request, 'weekly');
        return $this->build($start, $end, 'weekly', $request);
    }

    public function monthlyData(Request $request): array
    {
        [$start, $end] = $this->range($request, 'monthly');
        return $this->build($start, $end, 'monthly', $request);
    }

    public function exportData(Request $request, string $period): array
    {
        [$start, $end] = $this->range($request, $period);
        $data = $this->build($start, $end, $period, $request);
        $rows = Ritasi::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift))
            ->with(['unit', 'material', 'pegawai', 'area'])
            ->orderBy('tanggal', 'desc')->orderBy('shift')
            ->get();
        return array_merge($data, [
            'rows'         => $rows,
            'selectedUnit' => $data['selectedUnit'] ?? 'm3',
            'meta'         => [
                'period' => $period,
                'start'  => $start->toDateString(),
                'end'    => $end->toDateString(),
                'unit'   => $data['selectedUnit'] ?? 'm3',
            ],
        ]);
    }

    public function activeUnitCount(Carbon $at): int
    {
        $latest = $this->latestStatuses($at)->keyBy('unit_id');
        return Unit::where('is_active', true)->count() - $this->maintenanceUnitCount($at);
    }

    public function maintenanceUnitCount(Carbon $at): int
    {
        $latest = $this->latestStatuses($at)->keyBy('unit_id');
        $maintenance = 0;
        foreach (Unit::where('is_active', true)->get() as $u) {
            $status = $latest->get($u->id)->status ?? 'ready';
            if (in_array($status, ['breakdown', 'servis'])) $maintenance++;
        }
        return $maintenance;
    }

    public function fuelConsumption(Ritasi $ritasi): float
    {
        if ($ritasi->fuel_consumption !== null) {
            return (float) $ritasi->fuel_consumption;
        }
        return round((float) (($ritasi->unit->fuel_consumption_rate ?? 0) * $ritasi->hm_total), 2);
    }

    // --- internals ---

    private function build(Carbon $start, Carbon $end, string $period, Request $request): array
    {
        $units = Unit::where('is_active', true)->orderBy('kode')->get();
        $unitCount = $units->count();
        $days = (int) $start->diffInDays($end) + 1;
        $hoursPerDay = $request->filled('shift') ? 12 : 24;
        $sh = $unitCount * $hoursPerDay * $days;
        // Batched (1 query): jam maintenance per unit — dipakai timeline, availability, uoa.
        $bdByUnit = $this->bdHoursByUnit($start, $end);
        $bd = array_sum($bdByUnit);
        $available = max(0.0, $sh - $bd);
        $pa = $sh > 0 ? min(100.0, ($available / $sh) * 100) : 0;

        $baseQ = fn () => Ritasi::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift));

        $baseNonRitasiQ = fn () => NonRitasi::whereNotNull('unit_id')
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift));

        $whRitasi = (float) $baseQ()->sum(DB::raw('LEAST(hm_total, 12)'));
        $whNonRitasi = (float) $baseNonRitasiQ()->sum(DB::raw('LEAST(hm_total, 12)'));
        $wh = $whRitasi + $whNonRitasi;
        $ua = $available > 0 ? min(100.0, ($wh / $available) * 100) : 0;

        $fuelRitasi = (float) $baseQ()
            ->leftJoin('units', 'units.id', '=', 'ritasis.unit_id')
            ->sum(DB::raw('COALESCE(ritasis.fuel_consumption, units.fuel_consumption_rate * ritasis.hm_total)'));
        $fuelNonRitasi = (float) $baseNonRitasiQ()
            ->leftJoin('units', 'units.id', '=', 'non_ritasis.unit_id')
            ->sum(DB::raw('COALESCE(non_ritasis.fuel_consumption, units.fuel_consumption_rate * non_ritasis.hm_total)'));
        $fuel = $fuelRitasi + $fuelNonRitasi;

        // Satuan tunggal: meter kubik (m3). Data lama ton/bcm/cbm dikonversi otomatis via quantityInUnit().
        $selectedUnit = 'm3';

        $ritasis = $baseQ()->with('material')->get();
        $tonnage = (float) $ritasis->sum(fn ($r) => $r->quantityInUnit($selectedUnit));

        $pies = [
            'day'    => (float) $ritasis->where('shift', 'siang')->sum(fn ($r) => $r->quantityInUnit($selectedUnit)),
            'night'  => (float) $ritasis->where('shift', 'malam')->sum(fn ($r) => $r->quantityInUnit($selectedUnit)),
        ];
        $pies['combined'] = $pies['day'] + $pies['night'];

        // Batched (2 query GROUP BY): WH per unit per shift — ganti 4N query sum per unit.
        $whRitasiByUnit = $this->whByUnitShift('ritasis', $start, $end, $request);
        $whNonRitasiByUnit = $this->whByUnitShift('non_ritasis', $start, $end, $request);

        $timelineSiang = [];
        $timelineMalam = [];
        foreach ($units as $u) {
            $redTotal = (float) ($bdByUnit[$u->id] ?? 0);
            $redS = min(12.0, $redTotal);
            $redM = min(12.0, max(0.0, $redTotal - 12.0));
            $greenS = (float) (($whRitasiByUnit[$u->id]['siang'] ?? 0) + ($whNonRitasiByUnit[$u->id]['siang'] ?? 0));
            $greenM = (float) (($whRitasiByUnit[$u->id]['malam'] ?? 0) + ($whNonRitasiByUnit[$u->id]['malam'] ?? 0));
            $whiteS = max(12 - $redS - $greenS, 0.0);
            $whiteM = max(12 - $redM - $greenM, 0.0);
            $timelineSiang[] = [
                'unit_id' => $u->id, 'kode' => $u->kode,
                'red' => round($redS, 2), 'green' => round($greenS, 2), 'white' => round($whiteS, 2), 'status' => $u->status,
            ];
            $timelineMalam[] = [
                'unit_id' => $u->id, 'kode' => $u->kode,
                'red' => round($redM, 2), 'green' => round($greenM, 2), 'white' => round($whiteM, 2), 'status' => $u->status,
            ];
        }
        $n = max($unitCount, 1);
        $avgS = ['red' => round(array_sum(array_column($timelineSiang, 'red')) / $n, 2), 'green' => round(array_sum(array_column($timelineSiang, 'green')) / $n, 2), 'white' => round(array_sum(array_column($timelineSiang, 'white')) / $n, 2)];
        $avgM = ['red' => round(array_sum(array_column($timelineMalam, 'red')) / $n, 2), 'green' => round(array_sum(array_column($timelineMalam, 'green')) / $n, 2), 'white' => round(array_sum(array_column($timelineMalam, 'white')) / $n, 2)];
        $timelineAvg = ['siang' => $avgS, 'malam' => $avgM];

        $byTipe = $units->groupBy('tipe');
        $timelineGrouped = [];
        foreach ($byTipe as $tipe => $tipeUnits) {
            $items = $tipeUnits->map(function ($u) use ($timelineSiang, $timelineMalam) {
                $s = collect($timelineSiang)->firstWhere('unit_id', $u->id) ?? ['red' => 0, 'green' => 0, 'white' => 0];
                $m = collect($timelineMalam)->firstWhere('unit_id', $u->id) ?? ['red' => 0, 'green' => 0, 'white' => 0];
                return ['unit_id' => $u->id, 'kode' => $u->kode, 'status' => $u->status, 'siang' => $s, 'malam' => $m];
            });
            $cnt = max($tipeUnits->count(), 1);
            $timelineGrouped[strtoupper($tipe)] = [
                'count' => $tipeUnits->count(),
                'avg' => [
                    'red'   => round($items->sum(fn ($i) => $i['siang']['red'] + $i['malam']['red']) / $cnt, 2),
                    'green' => round($items->sum(fn ($i) => $i['siang']['green'] + $i['malam']['green']) / $cnt, 2),
                    'white' => round($items->sum(fn ($i) => $i['siang']['white'] + $i['malam']['white']) / $cnt, 2),
                ],
                'items' => $items->values()->all(),
            ];
        }

        $hauling = $baseQ()->with(['unit', 'material', 'pegawai'])
            ->orderBy('tanggal', 'desc')->orderBy('shift')->paginate(15);

        $haulingByMaterial = $ritasis->groupBy(fn ($r) => $r->material->nama ?? 'Lainnya')
            ->map(fn ($items) => round((float) $items->sum(fn ($r) => $r->quantityInUnit($selectedUnit)), 2))
            ->sortDesc()->all();

        // Daily target per material (period-aware: harian/mingguan/bulanan)
        // so weekly & monthly charts use their own target, not the daily one.
        $periodKey = match ($period) {
            'weekly' => 'mingguan',
            'monthly' => 'bulanan',
            default => 'harian',
        };
        $targetsByName = DailyTarget::with('material')->where('periode', $periodKey)->get()
            ->filter(fn ($t) => $t->material?->nama !== null)
            ->mapWithKeys(fn ($t) => [$t->material->nama => (int) $t->target_ritasi]);

        $ritasiByName = $ritasis->groupBy(fn ($r) => $r->material->nama ?? 'Lainnya')
            ->map(fn ($items) => (int) $items->sum(fn ($r) => $r->jumlah_ritasi));

        $materialNames = array_keys($haulingByMaterial);
        $materialChart = [
            'names'        => $materialNames,
            'tonnage'      => array_values($haulingByMaterial),
            'target'       => array_map(fn ($n) => $targetsByName[$n] ?? 0, $materialNames),
            'actualRitasi' => array_map(fn ($n) => $ritasiByName[$n] ?? 0, $materialNames),
            'gap'          => array_map(
                fn ($n) => max(0, ($targetsByName[$n] ?? 0) - ($ritasiByName[$n] ?? 0)),
                $materialNames
            ),
        ];

        $oreNames = ['Bauxite Ore (Raw)', 'Processed Alumina', 'Pasir Hitam', 'Mining Tuff', 'Batu Pica (5/15)', 'Tuff Off', 'KCN', 'Cake', 'DSTuff'];
        $dailyOreOthers = $this->dailyOreOthers($start, $end, $ritasis, $oreNames, $selectedUnit);

        $availability = $this->availabilityByType($start, $end, $units, $request, $bdByUnit);
        $uoa = $this->uoaByType($start, $end, $units, $whRitasiByUnit, $whNonRitasiByUnit, $bdByUnit, $request);

        // maintenance_units dihitung 1x dari data yang sama (bukan 2 query berulang).
        $latestByUnit = $this->latestStatuses($end)->keyBy('unit_id');
        $maintenanceCount = 0;
        foreach ($units as $u) {
            $status = $latestByUnit->get($u->id)->status ?? 'ready';
            if (in_array($status, ['breakdown', 'servis'])) $maintenanceCount++;
        }

        return [
            'kpi' => [
                'fuel'             => round($fuel, 2),
                'tonnage'          => round($tonnage, 2),
                'selected_unit'    => $selectedUnit,
                'active_units'     => $unitCount - $maintenanceCount,
                'maintenance_units'=> $maintenanceCount,
                'pa'               => round($pa, 2),
                'ua'               => round($ua, 2),
                'sh'               => $sh,
                'wh'               => round($wh, 2),
                'bd'               => round($bd, 2),
            ],
            'selectedUnit'      => $selectedUnit,
            'supportedUnits'    => ['m3' => 'M³'],
            'pies'              => $pies,
            'hauling'           => $hauling,
            'timeline'          => $timelineSiang,
            'timelineSiang'     => $timelineSiang,
            'timelineMalam'     => $timelineMalam,
            'timelineAvg'       => $timelineAvg,
            'timelineGrouped'   => $timelineGrouped,
            'units'             => $units,
            'periodLabel'       => $this->periodLabel($period, $start, $end),
            'haulingByMaterial' => $haulingByMaterial,
            'materialChart'     => $materialChart,
            'dailyOreOthers'    => $dailyOreOthers,
            'availability'      => $availability,
            'uoa'               => $uoa,
        ];
    }

    private function range(Request $request, string $type): array
    {
        if ($type === 'weekly') {
            $w = $request->filled('week') ? Carbon::parse($request->week) : Carbon::today();
            return [$w->copy()->startOfWeek(), $w->copy()->endOfWeek()];
        }
        if ($type === 'monthly') {
            $m = $request->filled('month') ? Carbon::parse($request->month) : Carbon::today();
            return [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()];
        }
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();
        return [$date->copy(), $date->copy()];
    }

    private function latestStatuses(Carbon $at): \Illuminate\Support\Collection
    {
        return UnitUtilization::latestPerUnit()
            ->where('started_at', '<=', $at->copy()->endOfDay())
            ->get();
    }

    /** Total jam maintenance per unit dalam range — 1 query untuk semua unit. */
    private function bdHoursByUnit(Carbon $start, Carbon $end): array
    {
        // Portable: hitung overlap di PHP agar jalan di MySQL maupun pgsql
        // (raw TIMESTAMPDIFF hanya ada di MySQL).
        $rangeStart = $start->copy()->startOfDay();
        $rangeEnd = $end->copy()->endOfDay();
        $now = Carbon::now();

        $rows = UnitUtilization::whereIn('status', ['breakdown', 'servis'])
            ->where('started_at', '<=', $rangeEnd)
            ->where(function ($q) use ($rangeStart) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', $rangeStart);
            })
            ->get(['unit_id', 'started_at', 'ended_at']);

        $map = [];
        foreach ($rows as $row) {
            $map[$row->unit_id] = ($map[$row->unit_id] ?? 0)
                + $this->overlapHours($row->started_at, $row->ended_at, $rangeStart, $rangeEnd, $now);
        }
        return $map;
    }

    /** WH (cap 12/jam) per unit per shift — 1 query GROUP BY per tabel. */
    private function whByUnitShift(string $table, Carbon $start, Carbon $end, Request $request): array
    {
        $rows = DB::table($table)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift))
            ->when($table === 'non_ritasis', fn ($q) => $q->whereNotNull('unit_id'))
            ->select('unit_id', 'shift', DB::raw('SUM(LEAST(hm_total, 12)) as wh'))
            ->groupBy('unit_id', 'shift')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->unit_id][$row->shift] = (float) $row->wh;
        }
        return $map;
    }

    /** Durasi overlap (jam) antara interval [eventStart, eventEnd ?? now] dan range, di-clamp >= 0. */
    private function overlapHours($eventStart, $eventEnd, Carbon $rangeStart, Carbon $rangeEnd, Carbon $now): float
    {
        if ($eventStart === null) {
            return 0.0;
        }
        $s = $eventStart instanceof Carbon ? $eventStart : Carbon::parse($eventStart);
        $e = $eventEnd === null ? $now : ($eventEnd instanceof Carbon ? $eventEnd : Carbon::parse($eventEnd));
        $overlapStart = $s->greaterThan($rangeStart) ? $s : $rangeStart;
        $overlapEnd = $e->lessThan($rangeEnd) ? $e : $rangeEnd;
        if ($overlapEnd->lessThanOrEqualTo($overlapStart)) {
            return 0.0;
        }
        return $overlapStart->diffInSeconds($overlapEnd) / 3600;
    }

    private function periodLabel(string $period, Carbon $start, Carbon $end): string
    {
        return match ($period) {
            'daily'   => $start->format('d M Y'),
            'weekly'  => $start->format('d M') . ' – ' . $end->format('d M Y'),
            'monthly' => $start->format('F Y'),
            default   => $start->format('d M Y'),
        };
    }

    private function dailyOreOthers(Carbon $start, Carbon $end, $ritasis, array $oreNames, string $selectedUnit = 'm3'): array
    {
        $days = [];
        $cumulative = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            $dayStr = $current->toDateString();
            $dayRitasis = $ritasis->filter(fn ($r) => $r->tanggal?->toDateString() === $dayStr);
            $ore = (float) $dayRitasis->filter(fn ($r) => ($r->material->kategori ?? '') === 'ore' || in_array($r->material->nama ?? '', $oreNames))->sum(fn ($r) => $r->quantityInUnit($selectedUnit));
            $others = (float) $dayRitasis->sum(fn ($r) => $r->quantityInUnit($selectedUnit)) - $ore;
            $cumulative += $ore + $others;
            $days[] = [
                'date'       => $current->format('d M'),
                'ore'        => round($ore, 2),
                'others'     => round(max($others, 0), 2),
                'cumulative' => round($cumulative, 2),
            ];
            $current->addDay();
        }
        return $days;
    }

    private function availabilityByType(Carbon $start, Carbon $end, $units, Request $request, array $bdByUnit = []): array
    {
        $days = (int) $start->diffInDays($end) + 1;
        $hoursPerDay = $request->filled('shift') ? 12 : 24;
        // Hanya 4 tipe, urut: Sany=loader, ADT=dump_truck, Dozr=bulldozer, Exa=excavator
        $allowed = ['loader', 'dump_truck', 'bulldozer', 'excavator'];
        $byType = $units->whereIn('tipe', $allowed)->groupBy('tipe')->map(function ($typeUnits, $type) use ($days, $hoursPerDay, $bdByUnit) {
            $count = $typeUnits->count();
            $sh = $count * $hoursPerDay * $days;
            $bd = 0.0;
            foreach ($typeUnits as $u) {
                $bd += (float) ($bdByUnit[$u->id] ?? 0);
            }
            $available = max(0.0, $sh - $bd);
            $pct = $sh > 0 ? round(min(100.0, ($available / $sh) * 100), 1) : 0;
            return ['type' => $type, 'pct' => $pct];
        })->sortBy(fn ($row) => array_search($row['type'], $allowed))->values()->all();
        return $byType;
    }

    private function uoaByType(Carbon $start, Carbon $end, $units, array $whRitasiByUnit, array $whNonRitasiByUnit, array $bdByUnit, Request $request): array
    {
        $days = (int) $start->diffInDays($end) + 1;
        $hoursPerDay = $request->filled('shift') ? 12 : 24;
        // Hanya 4 tipe, urut: Sany=loader, ADT=dump_truck, Dozr=bulldozer, Exa=excavator
        $allowed = ['loader', 'dump_truck', 'bulldozer', 'excavator'];
        // Hormati filter shift seperti query lama ($baseQ tertapis shift bila diisi).
        $shifts = $request->filled('shift') ? [$request->shift] : ['siang', 'malam'];
        $byType = $units->whereIn('tipe', $allowed)->groupBy('tipe')->map(function ($typeUnits, $type) use ($days, $hoursPerDay, $whRitasiByUnit, $whNonRitasiByUnit, $bdByUnit, $shifts) {
            $count = $typeUnits->count();
            $sh = $count * $hoursPerDay * $days;
            $bd = 0.0;
            $wh = 0.0;
            foreach ($typeUnits as $u) {
                $bd += (float) ($bdByUnit[$u->id] ?? 0);
                foreach ($shifts as $s) {
                    $wh += (float) (($whRitasiByUnit[$u->id][$s] ?? 0) + ($whNonRitasiByUnit[$u->id][$s] ?? 0));
                }
            }
            $available = max(0.0, $sh - $bd);
            $pct = $available > 0 ? round(min(100.0, ($wh / $available) * 100), 1) : 0;
            return ['type' => $type, 'pct' => $pct];
        })->sortBy(fn ($row) => array_search($row['type'], $allowed))->values()->all();
        return $byType;
    }
}
