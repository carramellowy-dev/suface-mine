<?php

namespace App\Services;

use App\Models\Ritasi;
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
            ->with(['unit', 'material', 'pegawai'])
            ->orderBy('tanggal', 'desc')->orderBy('shift')
            ->get();
        return [
            'kpi'  => $data['kpi'],
            'rows' => $rows,
            'meta' => ['period' => $period, 'start' => $start->toDateString(), 'end' => $end->toDateString()],
        ];
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
        $sh = $unitCount * 12 * $days;
        $bd = $this->bdHoursForRange($start, $end);
        $available = $sh - $bd;
        $pa = $sh > 0 ? ($available / $sh) * 100 : 0;

        $baseQ = fn () => Ritasi::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift));

        $wh = (float) $baseQ()->sum(DB::raw('LEAST(hm_total, 12)'));
        $ua = $available > 0 ? ($wh / $available) * 100 : 0;

        $fuel = (float) $baseQ()
            ->leftJoin('units', 'units.id', '=', 'ritasis.unit_id')
            ->sum(DB::raw('COALESCE(ritasis.fuel_consumption, units.fuel_consumption_rate * ritasis.hm_total)'));

        $ritasis = $baseQ()->with('material')->get();
        $tonnage = (float) $ritasis->sum(fn ($r) => $r->quantity_tonnes);

        $pies = [
            'day'    => (float) $ritasis->where('shift', 'siang')->sum(fn ($r) => $r->quantity_tonnes),
            'night'  => (float) $ritasis->where('shift', 'malam')->sum(fn ($r) => $r->quantity_tonnes),
        ];
        $pies['combined'] = $pies['day'] + $pies['night'];

        $timelineSiang = [];
        $timelineMalam = [];
        foreach ($units as $u) {
            $red = $this->unitMaintenanceHours($u->id, $start, $end);
            $greenS = (float) $baseQ()->where('unit_id', $u->id)->where('shift', 'siang')
                ->sum(DB::raw('LEAST(hm_total, 12)'));
            $greenM = (float) $baseQ()->where('unit_id', $u->id)->where('shift', 'malam')
                ->sum(DB::raw('LEAST(hm_total, 12)'));
            $whiteS = max(12 - $red - $greenS, 0.0);
            $whiteM = max(12 - $red - $greenM, 0.0);
            $timelineSiang[] = [
                'unit_id' => $u->id, 'kode' => $u->kode,
                'red' => round($red, 2), 'green' => round($greenS, 2), 'white' => round($whiteS, 2), 'status' => $u->status,
            ];
            $timelineMalam[] = [
                'unit_id' => $u->id, 'kode' => $u->kode,
                'red' => round($red, 2), 'green' => round($greenM, 2), 'white' => round($whiteM, 2), 'status' => $u->status,
            ];
        }
        $n = max($unitCount, 1);
        $avgS = ['red' => round(array_sum(array_column($timelineSiang, 'red')) / $n, 2), 'green' => round(array_sum(array_column($timelineSiang, 'green')) / $n, 2), 'white' => round(array_sum(array_column($timelineSiang, 'white')) / $n, 2)];
        $avgM = ['red' => round(array_sum(array_column($timelineMalam, 'red')) / $n, 2), 'green' => round(array_sum(array_column($timelineMalam, 'green')) / $n, 2), 'white' => round(array_sum(array_column($timelineMalam, 'white')) / $n, 2)];
        $avgC = ['red' => round(($avgS['red'] + $avgM['red']) / 2, 2), 'green' => round(($avgS['green'] + $avgM['green']) / 2, 2), 'white' => round(($avgS['white'] + $avgM['white']) / 2, 2)];
        $timelineAvg = ['siang' => $avgS, 'malam' => $avgM, 'combined' => $avgC];

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
            ->map(fn ($items) => round((float) $items->sum(fn ($r) => $r->quantity_tonnes), 2))
            ->sortDesc()->all();

        // Daily target per material (period-aware: harian/mingguan/bulanan)
        // so weekly & monthly charts use their own target, not the daily one.
        $periodKey = match ($period) {
            'weekly' => 'mingguan',
            'monthly' => 'bulanan',
            default => 'harian',
        };
        $targetsByName = DailyTarget::with('material')->where('periode', $periodKey)->get()
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
        $dailyOreOthers = $this->dailyOreOthers($start, $end, $ritasis, $oreNames);

        $availability = $this->availabilityByType($start, $end, $units, $bd);
        $uoa = $this->uoaByType($start, $end, $units, $baseQ);

        return [
            'kpi' => [
                'fuel'             => round($fuel, 2),
                'tonnage'          => round($tonnage, 2),
                'active_units'     => $unitCount - $this->maintenanceUnitCount($end),
                'maintenance_units'=> $this->maintenanceUnitCount($end),
                'pa'               => round($pa, 2),
                'ua'               => round($ua, 2),
                'sh'               => $sh,
                'wh'               => round($wh, 2),
                'bd'               => round($bd, 2),
            ],
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
        if ($type === 'weekly' && $request->filled('week')) {
            $w = Carbon::parse($request->week);
            return [$w->copy()->startOfWeek(), $w->copy()->endOfWeek()];
        }
        if ($type === 'monthly' && $request->filled('month')) {
            $m = Carbon::parse($request->month);
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

    private function bdHoursForRange(Carbon $start, Carbon $end): float
    {
        return (float) UnitUtilization::whereIn('status', ['breakdown', 'servis'])
            ->where('started_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', $start);
            })
            ->sum(DB::raw(
                "COALESCE(EXTRACT(EPOCH FROM (COALESCE(ended_at, NOW()) - started_at)) / 3600, 0)"
            ));
    }

    private function unitMaintenanceHours(int $unitId, Carbon $start, Carbon $end): float
    {
        return (float) UnitUtilization::where('unit_id', $unitId)
            ->whereIn('status', ['breakdown', 'servis'])
            ->where('started_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', $start);
            })
            ->sum(DB::raw(
                "COALESCE(EXTRACT(EPOCH FROM (COALESCE(ended_at, NOW()) - started_at)) / 3600, 0)"
            ));
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

    private function dailyOreOthers(Carbon $start, Carbon $end, $ritasis, array $oreNames): array
    {
        $days = [];
        $cumulative = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            $dayStr = $current->toDateString();
            $dayRitasis = $ritasis->filter(fn ($r) => $r->tanggal?->toDateString() === $dayStr);
            $ore = (float) $dayRitasis->filter(fn ($r) => in_array($r->material->nama ?? '', $oreNames))->sum(fn ($r) => $r->quantity_tonnes);
            $others = (float) $dayRitasis->sum(fn ($r) => $r->quantity_tonnes) - $ore;
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

    private function availabilityByType(Carbon $start, Carbon $end, $units, float $bdHours): array
    {
        $days = (int) $start->diffInDays($end) + 1;
        $byType = $units->groupBy('tipe')->map(function ($typeUnits, $type) use ($start, $end, $days) {
            $count = $typeUnits->count();
            $sh = $count * 12 * $days;
            $bd = 0.0;
            foreach ($typeUnits as $u) {
                $bd += $this->unitMaintenanceHours($u->id, $start, $end);
            }
            $available = $sh - $bd;
            $pct = $sh > 0 ? round(($available / $sh) * 100, 1) : 0;
            return ['type' => $type, 'pct' => $pct];
        })->values()->all();
        return $byType;
    }

    private function uoaByType(Carbon $start, Carbon $end, $units, $baseQ): array
    {
        $days = (int) $start->diffInDays($end) + 1;
        $byType = $units->groupBy('tipe')->map(function ($typeUnits, $type) use ($start, $end, $days, $baseQ) {
            $count = $typeUnits->count();
            $sh = $count * 12 * $days;
            $bd = 0.0;
            foreach ($typeUnits as $u) {
                $bd += $this->unitMaintenanceHours($u->id, $start, $end);
            }
            $available = $sh - $bd;
            $unitIds = $typeUnits->pluck('id');
            $wh = (float) $baseQ()->whereIn('unit_id', $unitIds)
                ->sum(DB::raw('LEAST(hm_total, 12)'));
            $pct = $available > 0 ? round(($wh / $available) * 100, 1) : 0;
            return ['type' => $type, 'pct' => $pct];
        })->values()->all();
        return $byType;
    }
}
