@extends('layouts.app', ['headerTitle' => 'Detail Rekapan Operator'])

@section('title', 'Detail Rekapan Operator')

@section('content')
@php $role = Auth::user()->role; @endphp

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-slate-500">Operator: <strong>{{ $pegawai->nama }}</strong></p>
    <a href="{{ route("$role.rekapan.index", request()->query()) }}" class="btn-secondary flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        Kembali
    </a>
</div>

@if(request()->has('tanggal_start') || request()->has('tanggal_end') || request()->has('shift'))
<div class="mb-6 p-4 bg-amber-50 border border-amber-100 rounded-xl flex items-center gap-3 text-sm text-amber-900 shadow-sm">
    <span class="material-symbols-outlined text-amber-600 text-xl">info</span>
    <div>
        Menampilkan data berdasarkan filter:
        @if(request('tanggal_start')) <strong>Dari:</strong> {{ request('tanggal_start') }} @endif
        @if(request('tanggal_end')) <strong>Sampai:</strong> {{ request('tanggal_end') }} @endif
        @if(request('shift')) <strong>Shift:</strong> {{ ucfirst(request('shift')) }} @endif
    </div>
</div>
@endif
<div class="card p-4 mb-6">
    <form method="GET" action="{{ route("$role.rekapan.show", $pegawai->id) }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
        <div>
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tanggal_start" value="{{ request('tanggal_start') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" name="tanggal_end" value="{{ request('tanggal_end') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Shift</label>
            <select name="shift" class="form-input">
                <option value="">All Shifts</option>
                <option value="siang" {{ request('shift') === 'siang' ? 'selected' : '' }}>Day</option>
                <option value="malam" {{ request('shift') === 'malam' ? 'selected' : '' }}>Night</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">filter_alt</span>
                Filter
            </button>
            @if(request()->has('tanggal_start') || request()->has('tanggal_end') || request()->has('shift'))
                <a href="{{ route("$role.rekapan.show", $pegawai->id) }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Ritasi Section -->
<div class="card overflow-hidden mb-6">
    <div class="p-4 border-b flex items-center gap-2">
        <span class="material-symbols-outlined text-[var(--primary)]">local_shipping</span>
        <h2 class="section-title mb-0">Ritasi (Hauling)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">TANGGAL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">SHIFT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">UNIT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">AREA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">MATERIAL</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">RITASI</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">QTY</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">TOTAL HM</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($ritasis as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $r->tanggal?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm">{{ ucfirst($r->shift) }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $r->unit->kode ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $r->area->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $r->material->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-right">{{ $r->jumlah_ritasi }}</td>
                        <td class="px-4 py-3 text-sm text-right">{{ number_format($r->quantity, 2) }} {{ $r->quantity_unit }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-[var(--primary)]">{{ number_format($r->hm_total, 1) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">Tidak ada data ritasi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Non Ritasi Section -->
<div class="card overflow-hidden mb-6">
    <div class="p-4 border-b flex items-center gap-2">
        <span class="material-symbols-outlined text-[var(--primary)]">construction</span>
        <h2 class="section-title mb-0">Non-Ritasi (Operasional)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">TANGGAL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">SHIFT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">UNIT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">AREA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">LOKASI / AKTIVITAS</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">HM AWAL</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">HM AKHIR</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">TOTAL HM</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($nonRitasis as $nr)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $nr->tanggal?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm">{{ ucfirst($nr->shift) }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $nr->unit->kode ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $nr->area->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $nr->lokasi_pekerjaan }}</div>
                            <div class="text-xs text-slate-500">{{ $nr->deskripsi_pekerjaan }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">{{ number_format($nr->hm_awal, 1) }}</td>
                        <td class="px-4 py-3 text-sm text-right">{{ number_format($nr->hm_akhir, 1) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-[var(--primary)]">{{ number_format($nr->hm_total, 1) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">Tidak ada data non-ritasi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- General Section -->
<div class="card overflow-hidden">
    <div class="p-4 border-b flex items-center gap-2">
        <span class="material-symbols-outlined text-[var(--primary)]">engineering</span>
        <h2 class="section-title mb-0">Tugas General</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">TANGGAL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">SHIFT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">UNIT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">AREA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">LOKASI / AKTIVITAS</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">JAM MULAI</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">JAM SELESAI</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($generals as $gen)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $gen->tanggal?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm">{{ ucfirst($gen->shift) }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $gen->unit->kode ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $gen->area->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium">{{ $gen->lokasi_pekerjaan }}</div>
                            <div class="text-xs text-slate-500">{{ $gen->deskripsi_pekerjaan }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-mono">{{ substr($gen->jam_mulai, 0, 5) }}</td>
                        <td class="px-4 py-3 text-sm text-center font-mono">{{ substr($gen->jam_selesai, 0, 5) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">Tidak ada data tugas general</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
