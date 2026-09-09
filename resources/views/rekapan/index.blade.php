@extends('layouts.app', ['headerTitle' => 'Rekapan Operator'])

@section('title', 'Rekapan Operator')

@section('content')
@php
    $userRole = Auth::user()->role;
    $role = in_array($userRole, ['spv', 'senior_spv']) ? 'spv' : $userRole;
@endphp

<div class="card mb-6 overflow-hidden !p-0">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="section-title mb-0 flex items-center gap-2">
            <span class="material-symbols-outlined text-[var(--primary)]">filter_alt</span>
            Filter Aktivitas Operator
        </h2>
        <form method="POST" action="{{ route("$role.rekapan.export") }}" class="flex items-center gap-2 m-0">
            @csrf
            <input type="hidden" name="tanggal_start" value="{{ $tanggalStart }}">
            <input type="hidden" name="tanggal_end" value="{{ $tanggalEnd }}">
            <input type="hidden" name="shift" value="{{ $shift }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <button type="submit" class="btn-primary flex items-center gap-2 text-sm py-1.5 px-3">
                <span class="material-symbols-outlined text-base">download</span>
                Export Excel
            </button>
        </form>
    </div>
    <div class="p-4">
        <form method="GET" action="{{ route("$role.rekapan.index") }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
            <div>
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_start" value="{{ $tanggalStart }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="tanggal_end" value="{{ $tanggalEnd }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Shift</label>
                <select name="shift" class="form-input">
                    <option value="">All Shifts</option>
                    <option value="siang" {{ ($shift ?? '') === 'siang' ? 'selected' : '' }}>Day</option>
                    <option value="malam" {{ ($shift ?? '') === 'malam' ? 'selected' : '' }}>Night</option>
                </select>
            </div>
            <div>
                <label class="form-label">Cari Operator / Unit</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <span class="material-symbols-outlined text-lg">search</span>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari operator, unit, area..." class="form-input pl-10">
                </div>
            </div>
            <div class="flex items-center gap-2 sm:col-span-2 xl:col-span-4 pt-1">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">filter_alt</span>
                    Filter
                </button>
                @if(request()->has('tanggal_start') || request()->has('tanggal_end') || request()->has('shift') || request()->has('search'))
                    <a href="{{ route("$role.rekapan.index") }}" class="btn-secondary">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card overflow-hidden !p-0">
    <div class="p-4 border-b flex items-center justify-between">
        <div>
            <h2 class="section-title mb-0 flex items-center gap-2">
                <span class="material-symbols-outlined text-[var(--primary)]">engineering</span>
                Aktivitas Operasional Operator
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                @if($hasDateFilter)
                    Menampilkan catatan operasional terfilter berdasarkan rentang tanggal.
                @else
                    Menampilkan catatan operasional terbaru dari lapangan (tanpa hitungan abstrak).
                @endif
            </p>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
            {{ count($rows) }} Catatan Aktivitas
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">TANGGAL & SHIFT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">OPERATOR</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">PEKERJAAN</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">UNIT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">LOKASI / AREA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">HM / JAM KERJA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">OUTPUT / DESKRIPSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $r)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <div class="font-medium text-slate-800">
                                {{ $r['tanggal'] ? (\Illuminate\Support\Carbon::parse($r['tanggal'])->format('d M Y')) : '-' }}
                            </div>
                            <div class="mt-0.5">
                                @if($r['shift'] === 'siang')
                                    <span class="inline-flex items-center text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.2 rounded">Day Shift</span>
                                @else
                                    <span class="inline-flex items-center text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.2 rounded">Night Shift</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route("$role.rekapan.show", ['pegawai' => $r['pegawai']?->id ?? 0]) }}" class="font-semibold text-[var(--primary)] hover:underline inline-flex items-center gap-1">
                                {{ $r['pegawai']?->nama ?? '-' }}
                                <span class="material-symbols-outlined text-xs text-slate-400">open_in_new</span>
                            </a>
                            <span class="block text-xs text-slate-400">NIK: {{ $r['pegawai']?->nik ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $r['badge_class'] }}">
                                {{ $r['tipe_pekerjaan'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            <span class="font-medium text-slate-800">{{ $r['unit_kode'] }}</span>
                            @if(!empty($r['unit_model']))
                                <span class="block text-xs text-slate-400">{{ $r['unit_model'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="font-medium text-slate-700">{{ $r['area_nama'] }}</span>
                            @if($r['lokasi_pekerjaan'] && $r['lokasi_pekerjaan'] !== $r['area_nama'])
                                <span class="block text-xs text-slate-500">{{ $r['lokasi_pekerjaan'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            @if($r['source_type'] === 'general')
                                <span class="font-semibold text-slate-800">{{ $r['jam_mulai'] }} - {{ $r['jam_selesai'] }}</span>
                                @if($r['is_overtime'])
                                    <span class="block text-[11px] font-semibold text-rose-600">Overtime</span>
                                @endif
                            @else
                                <div class="font-semibold text-slate-800">{{ number_format((float)$r['hm_total'], 1) }} Jam</div>
                                <div class="text-xs text-slate-400 font-mono">{{ number_format((float)$r['hm_awal'], 1) }} → {{ number_format((float)$r['hm_akhir'], 1) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($r['source_type'] === 'ritasi')
                                <div class="font-semibold text-slate-800">{{ $r['ritasi_count'] }} Rit</div>
                                @if($r['quantity'])
                                    <div class="text-xs text-slate-500">
                                        {{ number_format((float)$r['quantity'], 0) }} {{ $r['quantity_unit'] }}
                                        @if($r['material_nama']) • {{ $r['material_nama'] }} @endif
                                    </div>
                                @endif
                            @else
                                <div class="text-slate-700 line-clamp-2">{{ $r['deskripsi'] ?: '-' }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">event_busy</span>
                            Tidak ada aktivitas operasional operator pada periode / filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
