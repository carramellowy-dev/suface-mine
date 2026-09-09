<?php

namespace App\Http\Controllers;

use App\Models\NonRitasi;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiGeneralController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        
        $spvs = User::where('role', 'spv')->orderBy('name')->get();
        $seniorSpvs = User::where('role', 'senior_spv')->orderBy('name')->get();
        $areas = Area::orderBy('nama')->pluck('nama', 'id')->toArray();

        return view('operator.general.create', compact('pegawai', 'areas', 'spvs', 'seniorSpvs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'shift' => 'required|in:siang,malam',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'supervisor_id' => 'required|exists:users,id',
            'senior_spv_id' => 'nullable|exists:users,id|different:supervisor_id',
            'lokasi_pekerjaan' => 'nullable|string',
            'deskripsi_pekerjaan' => 'nullable|string',
            'is_overtime' => 'nullable|boolean',
        ], [
            'senior_spv_id.different' => 'Senior SPV tidak boleh sama dengan Supervisor.',
        ]);

        $user = Auth::user();
        if (! $user->pegawai_id) {
            $pegawai = \App\Models\Pegawai::firstOrCreate(['nama' => $user->name]);
            $user->update(['pegawai_id' => $pegawai->id]);
        }
        $pegawaiId = $user->pegawai_id;

        $validated['unit_id'] = null;

        $exists = NonRitasi::where('pegawai_id', $pegawaiId)
            ->whereNull('unit_id')
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->exists();

        if ($exists) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => true, 'replayed' => true], 200);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Anda sudah melakukan input pekerjaan general pada shift dan tanggal tersebut.'], 422);
            }
            return back()->with('error', 'Anda sudah melakukan input pekerjaan general pada shift dan tanggal tersebut.');
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

    public function riwayat(Request $request)
    {
        $pegawaiId = Auth::user()->pegawai_id;

        $query = NonRitasi::with(['area', 'supervisor', 'seniorSpv'])
            ->where('pegawai_id', $pegawaiId)
            ->whereNull('unit_id')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        $generals = $query->paginate(15);

        return view('operator.general.index', compact('generals'));
    }
}
