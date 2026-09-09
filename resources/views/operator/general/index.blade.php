@extends('layouts.app', ['headerTitle' => 'Riwayat Pekerjaan General'])

@section('title', 'Riwayat Pekerjaan General')

@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-slate-500">Daftar riwayat pekerjaan general yang telah Anda laporkan.</p>
    <a href="{{ route('pegawai.general.create') }}" class="btn-primary flex items-center gap-2 text-sm">
        <span class="material-symbols-outlined text-lg">add</span>
        Input Pekerjaan General
    </a>
</div>

<div class="card p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
        <div>
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Shift</label>
            <select name="shift" class="form-input">
                <option value="">Semua Shift</option>
                <option value="siang" {{ request('shift') === 'siang' ? 'selected' : '' }}>Day</option>
                <option value="malam" {{ request('shift') === 'malam' ? 'selected' : '' }}>Night</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn-primary flex items-center justify-center gap-2 w-full">
                <span class="material-symbols-outlined text-lg">filter_alt</span>
                Filter
            </button>
            @if(request('tanggal') || request('shift'))
                <a href="{{ route('pegawai.general.riwayat') }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Shift</th>
                    <th class="px-4 py-3 text-left">Area / Lokasi</th>
                    <th class="px-4 py-3 text-left">Jam Kerja</th>
                    <th class="px-4 py-3 text-left">Overtime</th>
                    <th class="px-4 py-3 text-left">Supervisor</th>
                    <th class="px-4 py-3 text-left">Deskripsi Pekerjaan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($generals as $g)
                <tr>
                    <td class="px-4 py-3">{{ $g->tanggal?->format('d M Y') ?? $g->tanggal }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $g->shift === 'siang' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700' }}">
                            {{ $g->shift === 'siang' ? 'Day' : 'Night' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-slate-800">{{ $g->area->nama ?? '-' }}</span>
                        @if($g->lokasi_pekerjaan)
                            <span class="block text-xs text-slate-400">{{ $g->lokasi_pekerjaan }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">
                        {{ $g->jam_mulai ? substr($g->jam_mulai, 0, 5) : '-' }} - {{ $g->jam_selesai ? substr($g->jam_selesai, 0, 5) : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($g->is_overtime)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Ya</span>
                        @else
                            <span class="text-xs text-slate-400">Tidak</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $g->supervisor->name ?? '-' }}</td>
                    <td class="px-4 py-3 max-w-xs truncate" title="{{ $g->deskripsi_pekerjaan }}">
                        {{ $g->deskripsi_pekerjaan ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $g->status_badge }}">{{ ucfirst(str_replace('_', ' ', $g->status)) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat pekerjaan general</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">
        {{ $generals->withQueryString()->links() }}
    </div>
</div>

@endsection
