<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class AdminMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::query();
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode', 'like', "%{$request->search}%")
                  ->orWhere('nama', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $materials = $query->orderBy('kode')->paginate(10);
        
        return view('admin.master-data.index', [
            'activeTab' => 'material',
            'materials' => $materials,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:materials,kode',
            'nama' => 'required|string',
            'satuan' => 'required|string',
            'kategori' => 'required|in:ore,waste,fuel,lubricant,explosive,spare_part,other',
            'stok' => 'required|numeric|min:0',
            'stok_minimal' => 'required|numeric|min:0',
            'harga_satuan' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,low_stock,inactive,restricted',
            'keterangan' => 'nullable|string',
        ]);

        Material::create($validated);

        return redirect()->route('admin.master-data.index', ['tab' => 'material'])
            ->with('success', 'Material berhasil ditambahkan');
    }

    public function edit(Material $material)
    {
        return response()->json($material);
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:materials,kode,' . $material->id,
            'nama' => 'required|string',
            'satuan' => 'required|string',
            'kategori' => 'required|in:ore,waste,fuel,lubricant,explosive,spare_part,other',
            'stok' => 'required|numeric|min:0',
            'stok_minimal' => 'required|numeric|min:0',
            'harga_satuan' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,low_stock,inactive,restricted',
            'keterangan' => 'nullable|string',
        ]);

        $material->update($validated);

        return redirect()->route('admin.master-data.index', ['tab' => 'material'])
            ->with('success', 'Material berhasil diupdate');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('admin.master-data.index', ['tab' => 'material'])
            ->with('success', 'Material berhasil dihapus');
    }
}