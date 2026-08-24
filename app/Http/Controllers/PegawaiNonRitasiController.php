<?php

namespace App\Http\Controllers;

use App\Models\NonRitasi;
use App\Models\Unit;
use App\Models\UnitUtilization;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiNonRitasiController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
                $units = Unit::where('is_active', true)->orderBy('kode')->pluck('kode', 'id')->toArray();
        $latestStatus = UnitUtilization::latestPerUnit()->pluck('status', 'unit_id')->toArray();
        $areas = Area::orderBy('nama')->pluck('nama', 'id')->toArray();

        return view('operator.non-ritasi.create', compact('pegawai', 'units', 'latestStatus', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'area_id' => 'required|exists:areas,id',
            'shift' => 'required|in:siang,malam',
            'tanggal' => 'required|date',
            'hm_awal' => 'required|numeric|min:0',
            'hm_akhir' => 'required|numeric|min:0|gte:hm_awal',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'fuel_consumption' => 'nullable|numeric|min:0',
            'lokasi_pekerjaan' => 'nullable|string',
            'deskripsi_pekerjaan' => 'nullable|string',
            'kendala' => 'nullable|string',
        ]);

        if (UnitUtilization::active()->where('unit_id', $validated['unit_id'])->exists()) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => true, 'replayed' => true], 200);
            }
            return back()->with('error', 'Unit sedang dalam maintenance; tidak dapat input ritasi.');
        }

        $pegawaiId = Auth::user()->pegawai_id;

        $exists = NonRitasi::where('pegawai_id', $pegawaiId)
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->exists();

        if ($exists) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => true, 'replayed' => true], 200);
            }
            return back()->with('error', 'Anda sudah melakukan input non-ritasi pada shift dan tanggal tersebut.');
        }

        $validated['pegawai_id'] = $pegawaiId;
        $validated['hm_total'] = $validated['hm_akhir'] - $validated['hm_awal'];

        NonRitasi::create($validated);

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Data non-ritasi berhasil disimpan!');
    }

    public function riwayat(Request $request)
    {
        $pegawaiId = Auth::user()->pegawai_id;
        
        $query = NonRitasi::with(['unit', 'area'])
            ->where('pegawai_id', $pegawaiId)
            ->orderBy('tanggal', 'desc');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        $nonRitasis = $query->paginate(15);

        return view('operator.non-ritasi.index', compact('nonRitasis'));
    }
}