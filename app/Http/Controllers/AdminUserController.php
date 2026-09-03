<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(10);

        return view('admin.master-data.index', [
            'activeTab' => 'user',
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'role'     => 'required|in:spv,pegawai',
        ]);

        $role = $request->role;
        $username = trim((string) $request->username);

        if ($username === '') {
            $prefix = $role === 'spv' ? 'spv' : 'operator';
            $num = 1;
            $username = $prefix . $num;
            while (User::where('username', $username)->exists()) {
                $num++;
                $username = $prefix . $num;
            }
        }

        $user = User::create([
            'name'     => $request->name,
            'username' => $username,
            'password' => Hash::make('password'),
            'role'     => $role,
        ]);

        if ($role === 'pegawai') {
            $pegawai = \App\Models\Pegawai::firstOrCreate(['nama' => $request->name]);
            $user->update(['pegawai_id' => $pegawai->id]);
        }

        return redirect()->route('admin.master-data.index', ['tab' => 'user'])
            ->with('success', 'User ' . $user->name . ' berhasil ditambahkan dengan ID: ' . $user->username);
    }

    public function edit(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:admin,spv,pegawai',
        ]);

        $username = trim((string) ($validated['username'] ?? ''));
        if ($username === '') {
            $prefix = $validated['role'] === 'spv' ? 'spv' : ($validated['role'] === 'admin' ? 'admin' : 'operator');
            $num = 1;
            $username = $prefix . $num;
            while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $num++;
                $username = $prefix . $num;
            }
        }
        $validated['username'] = $username;

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($user->role === 'pegawai') {
            if ($user->pegawai_id && $user->pegawai) {
                $user->pegawai->update(['nama' => $user->name]);
            } else {
                $pegawai = \App\Models\Pegawai::firstOrCreate(['nama' => $user->name]);
                $user->update(['pegawai_id' => $pegawai->id]);
            }
        }

        return redirect()->route('admin.master-data.index', ['tab' => 'user'])
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.master-data.index', ['tab' => 'user'])
            ->with('success', 'User berhasil dihapus');
    }
}
