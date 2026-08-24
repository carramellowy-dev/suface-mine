<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AdminAreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query();
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('kode', 'like', "%{$request->search}%");
            });
        }
        
        $areas = $query->orderBy('nama')->paginate(10);
        
        return view('admin.master-data.index', [
            'activeTab' => 'area',
            'areas' => $areas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:255|unique:areas,kode',
            'keterangan' => 'nullable|string',
        ]);

        Area::create($validated);

        return redirect()->route('admin.master-data.index', ['tab' => 'area'])
            ->with('success', 'Area berhasil ditambahkan');
    }

    public function edit(Area $area)
    {
        return response()->json($area);
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:255|unique:areas,kode,' . $area->id,
            'keterangan' => 'nullable|string',
        ]);

        $area->update($validated);

        return redirect()->route('admin.master-data.index', ['tab' => 'area'])
            ->with('success', 'Area berhasil diupdate');
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return redirect()->route('admin.master-data.index', ['tab' => 'area'])
            ->with('success', 'Area berhasil dihapus');
    }
}