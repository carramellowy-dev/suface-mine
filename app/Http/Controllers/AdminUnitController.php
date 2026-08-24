<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Area;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUnitController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'unit');
        $search = $request->input('search');
        $data = ['activeTab' => $activeTab];

        switch ($activeTab) {
            case 'unit':
                $query = Unit::query();
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('kode', 'like', "%{$search}%")
                          ->orWhere('nama', 'like', "%{$search}%")
                          ->orWhere('model', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                $data['units'] = $query->orderBy('kode')->paginate(10);
                break;

            case 'material':
                $query = Material::query();
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('kode', 'like', "%{$search}%")
                          ->orWhere('nama', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                $data['materials'] = $query->orderBy('kode')->paginate(10);
                break;

            case 'area':
                $query = Area::query();
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                          ->orWhere('kode', 'like', "%{$search}%");
                    });
                }
                $data['areas'] = $query->orderBy('nama')->paginate(10);
                break;

            case 'user':
                $query = User::with('area', 'pegawai');
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('role')) {
                    $query->where('role', $request->role);
                }
                $data['users'] = $query->orderBy('name')->paginate(10);
                break;

            case 'target':
                $query = \App\Models\DailyTarget::with('material');
                if ($search) {
                    $query->whereHas('material', function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                          ->orWhere('kode', 'like', "%{$search}%");
                    });
                }
                $data['targets'] = $query->orderBy('tanggal', 'desc')->paginate(10);
                $data['materials'] = Material::orderBy('nama')->get();
                break;
        }

        return view('admin.master-data.index', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:units,kode',
            'nama' => 'required|string',
            'tipe' => 'required|in:excavator,dump_truck,bulldozer,motor_grader,loader,other',
            'merk' => 'nullable|string',
            'model' => 'nullable|string',
            'tahun' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'kapasitas' => 'nullable|numeric|min:0',
            'fuel_consumption_rate' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['status'] = 'active';

        Unit::create($validated);

        return redirect()->route('admin.master-data.index', ['tab' => 'unit'])
            ->with('success', 'Unit berhasil ditambahkan');
    }

    public function edit(Unit $unit)
    {
        return response()->json($unit);
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:units,kode,' . $unit->id,
            'nama' => 'required|string',
            'tipe' => 'required|in:excavator,dump_truck,bulldozer,motor_grader,loader,other',
            'merk' => 'nullable|string',
            'model' => 'nullable|string',
            'tahun' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'kapasitas' => 'nullable|numeric|min:0',
            'fuel_consumption_rate' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['status'] = 'active';

        $unit->update($validated);

        return redirect()->route('admin.master-data.index', ['tab' => 'unit'])
            ->with('success', 'Unit berhasil diupdate');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('admin.master-data.index', ['tab' => 'unit'])
            ->with('success', 'Unit berhasil dihapus');
    }
}