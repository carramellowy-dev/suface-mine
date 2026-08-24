@extends('layouts.app', ['headerTitle' => 'Utilization Unit'])

@section('title', 'Utilization')

@section('content')
@php $role = Auth::user()->role; @endphp

<div class="card mb-6 overflow-hidden !p-0">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="section-title mb-0 flex items-center gap-2">
            <span class="material-symbols-outlined text-[var(--primary)]">filter_alt</span>
            Filter Utilization
        </h2>
    </div>
    <div class="p-4">
        <form method="GET" action="{{ route("$role.utilization.index") }}" class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400">calendar_today</span>
                <input type="date" name="tanggal_start" value="{{ request('tanggal_start') }}" class="form-input w-40">
                <span class="text-slate-400">-</span>
                <input type="date" name="tanggal_end" value="{{ request('tanggal_end') }}" class="form-input w-40">
            </div>
            <button type="submit" class="btn-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">filter_alt</span>
                Filter
            </button>
            @if(request()->filled('tanggal_start'))
                <a href="{{ route("$role.utilization.index") }}" class="btn-secondary">Reset</a>
            @endif
        </form>
    </div>
</div>

@php
    $breakdownCount = $latestStatus->filter(fn ($t) => $t === 'breakdown')->count();
    $servisCount = $latestStatus->filter(fn ($t) => $t === 'servis')->count();
    $readyCount = $latestStatus->filter(fn ($t) => $t === 'ready')->count();
@endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="stat-card border-l-4 border-red-500">
        <span class="material-symbols-outlined text-red-600">report</span>
        <div class="min-w-0">
            <p class="text-2xl font-bold">{{ $breakdownCount }}</p>
            <p class="text-sm text-slate-500">Unit Breakdown</p>
        </div>
    </div>
    <div class="stat-card border-l-4 border-amber-500">
        <span class="material-symbols-outlined text-amber-600">build</span>
        <div class="min-w-0">
            <p class="text-2xl font-bold">{{ $servisCount }}</p>
            <p class="text-sm text-slate-500">Unit Servis</p>
        </div>
    </div>
    <div class="stat-card border-l-4 border-green-500">
        <span class="material-symbols-outlined text-green-600">check_circle</span>
        <div class="min-w-0">
            <p class="text-2xl font-bold">{{ $readyCount }}</p>
            <p class="text-sm text-slate-500">Unit Ready</p>
        </div>
    </div>
</div>

<div class="card overflow-hidden !p-0">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="section-title mb-0 flex items-center gap-2">
            <span class="material-symbols-outlined text-[var(--primary)]">list_alt</span>
            Riwayat Status Utilization
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">TANGGAL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">UNIT</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">STATUS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">DESKRIPSI</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">PENGISI</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($utilizations as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm">{{ $u->started_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $u->unit->kode ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($u->status === 'breakdown')
                                <span class="badge badge-breakdown">Breakdown</span>
                            @elseif($u->status === 'servis')
                                <span class="badge badge-servis">Servis</span>
                            @else
                                <span class="badge badge-active">Ready</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $u->deskripsi ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $u->user->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t">
        {{ $utilizations->links() }}
    </div>
</div>
@endsection
