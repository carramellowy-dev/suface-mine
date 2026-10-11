<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUtilizationRequest;
use App\Models\Ritasi;
use App\Models\Unit;
use App\Models\UnitUtilization;
use Illuminate\Http\Request;

class UtilizationController extends Controller
{
    private function baseQuery(Request $request)
    {
        $query = UnitUtilization::with(['unit', 'user'])
            ->orderBy('started_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('tanggal_start') && $request->filled('tanggal_end')) {
            // Bandingkan per tanggal (bukan timestamp) agar filter 1 hari penuh ikut terhitung.
            $query->whereDate('started_at', '>=', $request->tanggal_start)
                ->whereDate('started_at', '<=', $request->tanggal_end);
        } elseif ($request->filled('tanggal_start')) {
            $query->whereDate('started_at', $request->tanggal_start);
        } elseif ($request->filled('tanggal_end')) {
            $query->whereDate('started_at', '<=', $request->tanggal_end);
        }

        return $query;
    }

    private function recapCounts(Request $request): array
    {
        // Rekap = hitung BARIS riwayat per status dalam lingkup filter.
        // Tiap input operator menambah 1 baris sehingga kartu selalu bergerak real-time,
        // dan jumlah kartu = jumlah baris tabel (mudah diverifikasi manual).
        $query = $this->baseQuery($request);
        return [
            'breakdown' => (clone $query)->where('status', 'breakdown')->count(),
            'servis' => (clone $query)->where('status', 'servis')->count(),
            'ready' => (clone $query)->where('status', 'ready')->count(),
        ];
    }

    public function index(Request $request)
    {
        $query = $this->baseQuery($request);

        // Rekap riwayat (ikut filter tanggal yang aktif): distinct unit per status
        // agar kartu konsisten dengan isi tabel riwayat di bawahnya.
        // (Dihitung SEBELUM paginate karena paginate menambah limit/offset.)
        $counts = $this->recapCounts($request);
        $recapBreakdown = $counts['breakdown'];
        $recapServis = $counts['servis'];
        $recapReady = $counts['ready'];

        $utilizations = $query->paginate(15)->withQueryString();

        $latestStatus = UnitUtilization::latestPerUnit()->pluck('status', 'unit_id');
        // Total unit aktif agar kartu Ready sinkron dengan seluruh armada
        // (unit yang belum pernah punya riwayat maintenance juga terhitung Ready).
        $totalUnits = Unit::where('is_active', true)->count();

        return view('utilization.index', compact('utilizations', 'latestStatus', 'totalUnits', 'recapBreakdown', 'recapServis', 'recapReady'));
    }

    /** Ringkasan kartu untuk auto-refresh real-time (dipoll halaman utilization). */
    public function summary(Request $request)
    {
        return response()->json($this->recapCounts($request));
    }

    public function create()
    {
        if (auth()->user()?->role !== 'pegawai') {
            abort(403, 'Akses ditolak. Hanya operator yang memiliki wewenang input utilization.');
        }

        $units = Unit::where('is_active', true)->orderBy('kode')->get();
        $latestStatus = UnitUtilization::latestPerUnit()->pluck('status', 'unit_id');
        return view('operator.utilization.create', compact('units', 'latestStatus'));
    }

    public function store(StoreUtilizationRequest $request)
    {
        if (auth()->user()?->role !== 'pegawai') {
            abort(403, 'Akses ditolak. Hanya operator yang memiliki wewenang untuk mencatat atau mencabut status utilization.');
        }

        $unitId = $request->unit_id;
        $status = $request->status;
        $current = UnitUtilization::active()->where('unit_id', $unitId)->latest('started_at')->first();

        // Transition rules (spec §3): at most one active entry per unit;
        // servis may start only while a breakdown is the active entry;
        // ready only if an active entry exists.
        if (in_array($status, ['breakdown', 'servis'])) {
            if ($current) {
                if ($request->header('X-Offline-Replay') === '1') {
                    return response()->json(['success' => true, 'replayed' => true], 200);
                }
                return back()->with('error', 'Unit masih dalam maintenance aktif.');
            }
        } elseif ($status === 'ready') {
            if (! $current) {
                if ($request->header('X-Offline-Replay') === '1') {
                    return response()->json(['success' => true, 'replayed' => true], 200);
                }
                return back()->with('error', 'Tidak ada maintenance aktif untuk unit ini.');
            }
        }

        // Idempotency for offline replay: same payload as an existing row.
        $exists = UnitUtilization::where('unit_id', $unitId)
            ->where('status', $status)
            ->where('started_at', $request->started_at ?? null)
            ->where(function ($q) use ($request) {
                if (empty($request->deskripsi)) {
                    $q->whereNull('deskripsi');
                } else {
                    $q->where('deskripsi', $request->deskripsi);
                }
            })
            ->exists();

        if ($exists) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => true, 'replayed' => true], 200);
            }
            return back()->with('error', 'Entri utilization yang sama sudah tercatat.');
        }

        $data = [
            'unit_id' => $unitId,
            'status' => $status,
            'deskripsi' => $request->deskripsi,
            'user_id' => auth()->id(),
        ];
        if (in_array($status, ['breakdown', 'servis'])) {
            $data['started_at'] = $request->started_at;
        } else { // ready
            $data['started_at'] = $current->started_at;
            $data['ended_at'] = $request->ended_at ?? now();
            $current->update(['ended_at' => $data['ended_at']]);
        }

        UnitUtilization::create($data);

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Data utilization berhasil disimpan!');
    }
}
