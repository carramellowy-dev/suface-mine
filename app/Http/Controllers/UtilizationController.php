<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUtilizationRequest;
use App\Models\Ritasi;
use App\Models\Unit;
use App\Models\UnitUtilization;
use Illuminate\Http\Request;

class UtilizationController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitUtilization::with(['unit', 'user'])
            ->orderBy('started_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('tanggal_start') && $request->filled('tanggal_end')) {
            $query->whereBetween('started_at', [$request->tanggal_start, $request->tanggal_end]);
        } elseif ($request->filled('tanggal_start')) {
            $query->whereDate('started_at', $request->tanggal_start);
        }

        $utilizations = $query->paginate(15);

        $latestStatus = UnitUtilization::latestPerUnit()->pluck('status', 'unit_id');

        return view('utilization.index', compact('utilizations', 'latestStatus'));
    }

    public function create()
    {
        $units = Unit::where('is_active', true)->orderBy('kode')->get();
        $latestStatus = UnitUtilization::latestPerUnit()->pluck('status', 'unit_id');
        return view('operator.utilization.create', compact('units', 'latestStatus'));
    }

    public function store(StoreUtilizationRequest $request)
    {
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
            if ($current->user_id !== auth()->id()) {
                if ($request->header('X-Offline-Replay') === '1') {
                    return response()->json(['success' => true, 'replayed' => true], 200);
                }
                return back()->with('error', 'Hanya operator yang melaporkan maintenance ini yang dapat menyelesaikan.');
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
