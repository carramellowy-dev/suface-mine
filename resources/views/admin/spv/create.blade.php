@extends('layouts.app', ['headerTitle' => 'Tambah SPV'])

@section('title', 'Tambah SPV')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.spv.index') }}" class="btn-secondary inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Kembali
    </a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.spv.store') }}">
        @csrf
        <div class="p-6 space-y-4">
            <div>
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       class="form-input"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="username" class="form-label">Username</label>
                <input type="text"
                       id="username"
                       name="username"
                       value="{{ old('username') }}"
                       class="form-input"
                       required>
                @error('username')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-input"
                       required>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label mb-2">Area Tugas</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($areas as $area)
                        <label class="flex items-center gap-2 p-2 border border-[var(--border)] rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="checkbox"
                                   name="areas[]"
                                   value="{{ $area->id }}"
                                   {{ in_array($area->id, old('areas', [])) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                            <span class="text-sm text-[var(--text)]">{{ $area->nama }}</span>
                        </label>
                    @endforeach
                </div>
                @error('areas')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="p-6 border-t border-[var(--border)] flex justify-end gap-3">
            <a href="{{ route('admin.spv.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection