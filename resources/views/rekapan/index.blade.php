@extends('layouts.app', ['headerTitle' => 'Rekapan Operator'])

@section('title', 'Rekapan Operator')

@section('content')
@php $role = Auth::user()->role; @endphp

<div class="card mb-6 overflow-hidden !p-0">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="section-title mb-0 flex items-center gap-2">
            <span class="material-symbols-outlined text-[var(--primary)]">filter_alt</span>
            Filter Rekapan
        </h2>
        <form method="POST" action="{{ route("$role.rekapan.export") }}" class="flex items-center gap-2 m-0">
            @csrf
            <input type="hidden" name="tanggal_start" value="{{ request('tanggal_start') }}">
            <input type="hidden" name="tanggal_end" value="{{ request('tanggal_end') }}">
            <input type="hidden" name="shift" value="{{ request('shift') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
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
            <div>
                <label class="form-label">Cari Operator</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <span class="material-symbols-outlined text-lg">search</span>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama operator..." class="form-input pl-10">
                </div>
            </div>
            <div class="flex items-center gap-2 sm:col-span-2 xl:col-span-4 pt-1">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">filter_alt</span>
                    Filter
                </button>
                @if(request()->has('tanggal_start') || request()->has('shift') || request()->has('search'))
                    <a href="{{ route("$role.rekapan.index") }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card overflow-hidden !p-0">
    <div class="p-4 border-b">
        <h2 class="section-title">Rekap HM Operator</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">NAMA OPERATOR</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">RITASI</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">NON-RITASI</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">GENERAL</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">TOTAL HM</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($rows as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-medium">
                            <a href="{{ route("$role.rekapan.show", ['pegawai' => $r['pegawai']->id, 'tanggal_start' => request('tanggal_start'), 'tanggal_end' => request('tanggal_end'), 'shift' => request('shift')]) }}" class="text-[var(--primary)] hover:underline">
                                {{ $r['pegawai']->nama }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">{{ $r['ritasi'] }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $r['non_ritasi'] }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ $r['general'] }}</td>
                        <td class="px-4 py-3 text-sm text-center">{{ number_format($r['ritasi_hm'] + $r['non_ritasi_hm'], 1) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
</div>
@endsection
