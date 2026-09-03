<?php

namespace App\Http\Controllers;

use App\Models\NonRitasi;
use App\Models\Area;
use App\Models\Unit;
use App\Models\UnitUtilization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiGeneralController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
                $units = Unit::orderBy('nama')->pluck('nama', 'id')->toArray();
        $latestStatus = UnitUtilization::latestPerUnit()->pluck('status', 'unit_id')->toArray();
        $areas = Area::orderBy('nama')->pluck('nama', 'id')->toArray();

        return view('operator.general.create', compact('pegawai', 'units', 'latestStatus', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'unit_id' => 'required|exists:units,id',
            'shift' => 'required|in:siang,malam',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'lokasi_pekerjaan' => 'nullable|string',
            'deskripsi_pekerjaan' => 'nullable|string',
            'is_overtime' => 'nullable|boolean',
        ]);

                $user = Auth::user();
        if (! $user->pegawai_id) {
            $pegawai = \App\Models\Pegawai::firstOrCreate(['nama' => $user->name]);
            $user->update(['pegawai_id' => $pegawai->id]);
        }
        $pegawaiId = $user->pegawai_id;

        if (UnitUtilization::active()->where('unit_id', $validated['unit_id'])->exists()) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => true, 'replayed' => true], 200);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unit sedang dalam maintenance; tidak dapat input pekerjaan.'], 422);
            }
            return back()->with('error', 'Unit sedang dalam maintenance; tidak dapat input pekerjaan.');
        }

        $exists = NonRitasi::where('pegawai_id', $pegawaiId)
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->exists();

        if ($exists) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => true, 'replayed' => true], 200);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Anda sudah melakukan input pekerjaan pada shift dan tanggal tersebut.'], 422);
            }
            return back()->with('error', 'Anda sudah melakukan input pekerjaan pada shift dan tanggal tersebut.');
        }

        $validated['pegawai_id'] = $pegawaiId;
        $validated['status'] = 'pending';
        $validated['hm_awal'] = $validated['hm_awal'] ?? null;
        $validated['hm_akhir'] = $validated['hm_akhir'] ?? null;
        $validated['hm_total'] = $validated['hm_total'] ?? null;

        NonRitasi::create($validated);

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Data pekerjaan general berhasil disimpan!');
    }
}
