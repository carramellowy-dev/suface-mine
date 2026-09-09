<?php

namespace App\Services;

use App\Models\Ritasi;
use App\Models\Unit;
use App\Models\UnitUtilization;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single Source of Truth untuk perhitungan HM, WH, SH, BD, PA, UA
 * Sesuai spec: HM Total = HM Akhir - HM Awal, WH = SUM(HM Total),
 * SH = unit aktif ×12×hari, BD = Breakdown+Servis, PA=(SH-BD)/SH*100, UA=WH/(SH-BD)*100
 * Satuan konsisten: jam decimal (0.5 jam = 30 menit)
 */
class MineCalculationService
{
    /**
     * HM Total per entri - auto-cap maksimal 12 jam, harus >0
     * @return float decimal hours, rounded 2, max 12.0
     * @throws \InvalidArgumentException jika <=0 (HM Akhir harus > HM Awal)
     */
    public static function hmTotal(float $hmAwal, float $hmAkhir): float
    {
        $total = round($hmAkhir - $hmAwal, 2);
        if ($total <= 0) {
            throw new \InvalidArgumentException('HM Akhir harus lebih besar dari HM Awal. HM Total = HM Akhir - HM Awal harus > 0.');
        }
        // Auto-cap ke 12 jam (sesuai request UI)
        if ($total > 12) {
            return 12.0;
        }
        return $total;
    }

    /**
     * Validasi HM Total tanpa throw, untuk controller
     * Jika >12 akan di-cap ke 12 (valid), jika <=0 tidak valid
     */
    public static function validateHmTotal(float $hmAwal, float $hmAkhir): array
    {
        $total = round($hmAkhir - $hmAwal, 2);
        if ($total <= 0) {
            return ['valid' => false, 'hm_total' => $total, 'capped' => false, 'hm_akhir_capped' => $hmAkhir, 'message' => 'HM Akhir harus lebih besar dari HM Awal. HM Total harus > 0.'];
        }
        if ($total > 12) {
            $cappedAkhir = round($hmAwal + 12, 2);
            return ['valid' => true, 'hm_total' => 12.0, 'capped' => true, 'hm_akhir_capped' => $cappedAkhir, 'message' => "HM Total {$total} jam melebihi batas 12 jam, di-cap otomatis ke 12 jam."];
        }
        return ['valid' => true, 'hm_total' => $total, 'capped' => false, 'hm_akhir_capped' => $hmAkhir, 'message' => null];
    }

    /**
     * Helper untuk cap HM Akhir jika total >12
     */
    public static function cappedHmAkhir(float $hmAwal, float $hmAkhir): float
    {
        $total = round($hmAkhir - $hmAwal, 2);
        if ($total > 12) {
            return round($hmAwal + 12, 2);
        }
        return $hmAkhir;
    }

    /** WH = SUM(HM Total) seluruh ritasi pada rentang */
    public static function wh(Carbon $start, Carbon $end, ?string $shift = null, ?array $unitIds = null): float
    {
        $q = Ritasi::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);
        if ($shift) $q->where('shift', $shift);
        if ($unitIds) $q->whereIn('unit_id', $unitIds);
        return (float) $q->sum('hm_total');
    }

    /** SH = jumlah unit aktif ×12×jumlah hari */
    public static function sh(Carbon $start, Carbon $end): float
    {
        $unitCount = Unit::where('is_active', true)->count();
        $days = (int) $start->diffInDays($end) + 1;
        return (float) $unitCount * 12 * $days;
    }

    /** BD = total durasi Breakdown + Servis saja, WAITING excluded. ended_at null => NOW(). Satuan jam. */
    public static function bd(Carbon $start, Carbon $end, ?int $unitId = null): float
    {
        $driver = DB::getDriverName();
        $hoursExpr = $driver === 'pgsql'
            ? "COALESCE(EXTRACT(EPOCH FROM (COALESCE(ended_at, NOW()) - started_at)) / 3600, 0)"
            : "COALESCE(TIMESTAMPDIFF(SECOND, started_at, COALESCE(ended_at, NOW())) / 3600, 0)";

        $q = UnitUtilization::whereIn('status', ['breakdown', 'servis'])
            ->where('started_at', '<=', $end)
            ->where(function ($w) use ($start) {
                $w->whereNull('ended_at')->orWhere('ended_at', '>=', $start);
            });
        if ($unitId) $q->where('unit_id', $unitId);
        return (float) $q->sum(DB::raw($hoursExpr));
    }

    /** PA = ((SH - BD) / SH) ×100 ; SH<=0 atau SH<=BD =>0 */
    public static function pa(float $sh, float $bd): float
    {
        if ($sh <= 0 || $sh <= $bd) return 0.0;
        return round((($sh - $bd) / $sh) * 100, 2);
    }

    /** UA = WH / (SH - BD) ×100 ; SH<=0 atau SH<=BD atau (SH-BD)<=0 =>0 */
    public static function ua(float $wh, float $sh, float $bd): float
    {
        if ($sh <= 0 || $sh <= $bd) return 0.0;
        $available = $sh - $bd;
        if ($available <= 0) return 0.0;
        return round(($wh / $available) * 100, 2);
    }

    /**
     * Helper lengkap untuk dashboard/reporting agar sama sumbernya
     * return ['sh'=>float,'bd'=>float,'wh'=>float,'pa'=>float,'ua'=>float,'available'=>float]
     */
    public static function kpi(Carbon $start, Carbon $end, ?string $shift = null): array
    {
        $sh = self::sh($start, $end);
        $bd = self::bd($start, $end);
        $wh = self::wh($start, $end, $shift);
        $pa = self::pa($sh, $bd);
        $ua = self::ua($wh, $sh, $bd);
        return [
            'sh' => round($sh, 2),
            'bd' => round($bd, 2),
            'wh' => round($wh, 2),
            'available' => round($sh - $bd, 2),
            'pa' => $pa,
            'ua' => $ua,
        ];
    }
}
