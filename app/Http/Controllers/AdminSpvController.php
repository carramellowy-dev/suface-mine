<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminSpvController extends Controller
{
    public function index()
    {
        $spvs = User::where('role', 'spv')->with('areas')->paginate(10);
        return view('admin.spv.index', compact('spvs'));
    }

    public function create()
    {
        $areas = Area::orderBy('nama')->get();
        return view('admin.spv.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'areas' => 'required|array',
            'areas.*' => 'exists:areas,id'
        ]);

        $spv = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => 'spv'
        ]);

        $spv->areas()->sync($validated['areas']);

        return redirect()->route('admin.spv.index')->with('success', 'Akun SPV berhasil dibuat dan area ditugaskan.');
    }

    public function edit(User $spv)
    {
        $areas = Area::orderBy('nama')->get();
        $assignedAreas = $spv->areas->pluck('id')->toArray();
        return view('admin.spv.edit', compact('spv', 'areas', 'assignedAreas'));
    }

    public function update(Request $request, User $spv)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($spv->id)],
            'password' => 'nullable|string|min:6',
            'areas' => 'required|array',
            'areas.*' => 'exists:areas,id'
        ]);

        $spv->name = $validated['name'];
        $spv->username = $validated['username'];
        if (!empty($validated['password'])) {
            $spv->password = Hash::make($validated['password']);
        }
        $spv->save();

        $spv->areas()->sync($validated['areas']);

        return redirect()->route('admin.spv.index')->with('success', 'Data SPV berhasil diperbarui.');
    }

    public function destroy(User $spv)
    {
        if ($spv->areas()->count() > 0) {
            $spv->areas()->detach();
        }
        $spv->delete();
        return redirect()->route('admin.spv.index')->with('success', 'Akun SPV berhasil dihapus.');
    }
}
