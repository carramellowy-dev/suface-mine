@extends('layouts.app', ['headerTitle' => 'Tambah Operator'])

@section('title', 'Tambah Operator')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.pegawai.index') }}" class="btn-secondary inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Kembali
    </a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.pegawai.store') }}">
        @csrf
        <div class="p-6 space-y-4">
            <div>
                <label for="nama" class="form-label">Nama Operator</label>
                <input type="text"
                       id="nama"
                       name="nama"
                       value="{{ old('nama') }}"
                       class="form-input"
                       required>
                @error('nama')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="p-6 border-t border-[var(--border)] flex justify-end gap-3">
            <a href="{{ route('admin.pegawai.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection