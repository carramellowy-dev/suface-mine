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
            'shift' => 'required|in:siang,malam',
            'tanggal' => 'required|date',
            'hm_awal' => 'required|numeric|min:0',
            'hm_akhir' => 'required|numeric|min:0|gte:hm_awal',
            'hm_mulai_kerja' => 'required|array|min:1',
            'hm_mulai_kerja.*' => 'required|numeric|min:0',
            'area_id' => 'required|array|min:1',
            'area_id.*' => 'required|exists:areas,id',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i',
            'fuel_consumption' => 'nullable|numeric|min:0',
            'lokasi_pekerjaan' => 'nullable|string',
            'deskripsi_pekerjaan' => 'nullable|string',
            'kendala' => 'nullable|string',
        ]);

        $fail = function (string $msg) use ($request) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        };

        $rowCount = count($validated['area_id']);
        if (count($validated['hm_mulai_kerja']) !== $rowCount) {
            return $fail('Jumlah baris rincian tidak sama. Periksa kembali setiap baris.');
        }

        // Validasi berurutan: HM awal <= HM mulai baris-1 <= ... <= HM akhir
        $hmAwal = (float) $validated['hm_awal'];
        $hmAkhir = (float) $validated['hm_akhir'];
        $prev = $hmAwal;
        foreach ($validated['hm_mulai_kerja'] as $hmMulai) {
            $hmMulai = (float) $hmMulai;
            if ($hmMulai < $prev) {
                return $fail('HM mulai kerja tiap baris harus berurutan dan tidak kurang dari HM awal.');
            }
            $prev = $hmMulai;
        }
        if ($hmAkhir < $prev) {
            return $fail('HM akhir tidak boleh lebih kecil dari HM mulai kerja terakhir.');
        }

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
                return response()->json(['message' => 'Unit sedang dalam maintenance; tidak dapat input non-ritasi.'], 422);
            }
            return back()->with('error', 'Unit sedang dalam maintenance; tidak dapat input non-ritasi.');
        }

        $unitTaken = NonRitasi::where('unit_id', $validated['unit_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->where('pegawai_id', '!=', $pegawaiId)
            ->exists();

        if ($unitTaken) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => false, 'message' => 'Unit sudah digunakan oleh operator lain pada shift dan tanggal ini.'], 422);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unit sudah digunakan oleh operator lain pada shift dan tanggal ini.'], 422);
            }
            return back()->with('error', 'Unit sudah digunakan oleh operator lain pada shift dan tanggal ini.');
        }

        $exists = NonRitasi::where('pegawai_id', $pegawaiId)
            ->whereNotNull('unit_id')
            ->where('tanggal', $validated['tanggal'])
            ->where('shift', $validated['shift'])
            ->exists();

        if ($exists) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => true, 'replayed' => true], 200);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Anda sudah melakukan input non-ritasi pada shift dan tanggal tersebut.'], 422);
            }
            return back()->with('error', 'Anda sudah melakukan input non-ritasi pada shift dan tanggal tersebut.');
        }

        $hmTotal = $validated['hm_akhir'] - $validated['hm_awal'];
        if ($hmTotal > 12) {
            if ($request->header('X-Offline-Replay') === '1') {
                return response()->json(['success' => false, 'message' => 'Total Hour Meter (HM) tidak boleh melebihi 12 jam dalam 1 shift.'], 422);
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Total Hour Meter (HM) tidak boleh melebihi 12 jam dalam 1 shift.'], 422);
            }
            return back()->with('error', 'Total Hour Meter (HM) tidak boleh melebihi 12 jam dalam 1 shift.');
        }

        // 1 submit = N baris Non-Ritasi. HM total & fuel hanya dicatat di baris pertama
        // agar agregasi dashboard (WH, fuel) tidak terhitung ganda.
        $base = [
            'pegawai_id' => $pegawaiId,
            'unit_id' => $validated['unit_id'],
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'hm_awal' => $validated['hm_awal'],
            'hm_akhir' => $validated['hm_akhir'],
            'jam_mulai' => $validated['jam_mulai'] ?? null,
            'jam_selesai' => $validated['jam_selesai'] ?? null,
            'deskripsi_pekerjaan' => $validated['deskripsi_pekerjaan'] ?? null,
            'kendala' => $validated['kendala'] ?? null,
        ];

        for ($i = 0; $i < $rowCount; $i++) {
            NonRitasi::create(array_merge($base, [
                'hm_mulai_kerja' => $validated['hm_mulai_kerja'][$i],
                'area_id' => $validated['area_id'][$i],
                'hm_total' => $i === 0 ? $hmTotal : 0,
                'fuel_consumption' => $i === 0 ? ($validated['fuel_consumption'] ?? null) : null,
            ]));
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Data non-ritasi ({$rowCount} baris) berhasil disimpan!");
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