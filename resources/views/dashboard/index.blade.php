@extends('layouts.app', ['headerTitle' => 'Dashboard'])

@section('title', 'Dashboard')

@section('content')
@php
    $tabs = [
        'daily'   => ['label' => 'Harian',   'icon' => 'calendar_today'],
        'weekly'  => ['label' => 'Mingguan',  'icon' => 'calendar_view_week'],
        'monthly' => ['label' => 'Bulanan',  'icon' => 'calendar_month'],
    ];
    $q = request()->collect()->except(['tab', 'date', 'week', 'month', 'shift'])->all();
    $currentDate = request('date', now()->format('Y-m-d'));
    $currentWeek = request('week', now()->format('Y-\WW'));
    $currentMonth = request('month', now()->format('Y-m'));
    $currentShift = request('shift', '');
@endphp

<div class="mb-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 pb-1">
    <nav class="-mb-px flex gap-6 overflow-x-auto">
        @foreach ($tabs as $key => $t)
            @php $active = ($tab === $key); @endphp
            <a href="{{ request()->fullUrlWithQuery(array_merge($q, ['tab' => $key])) }}"
               class="py-2.5 px-1 border-b-2 font-bold text-sm sm:text-base flex items-center gap-2 whitespace-nowrap transition-colors
                      {{ $active ? 'border-[var(--accent)] text-[var(--primary)]' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <span class="material-symbols-outlined text-base {{ $active ? 'text-[var(--accent)]' : 'text-slate-400' }}">{{ $t['icon'] }}</span>
                {{ $t['label'] }}
            </a>
        @endforeach
    </nav>

    <button type="button" onclick="location.href='{{ route(auth()->user()->role . '.dashboard.export') }}?{{ http_build_query(request()->collect()->except('tab')->all()) }}'"
            class="btn-secondary flex items-center gap-2 text-sm py-1.5 px-3">
        <span class="material-symbols-outlined text-base">download</span> Export
    </button>
</div>

<div class="flex flex-wrap items-center gap-6 mb-6 py-4 px-5 bg-white border border-slate-100 rounded-2xl shadow-sm">
    @if ($tab === 'daily')
        <div class="flex items-center gap-2.5">
            <label class="text-sm font-bold text-slate-700 whitespace-nowrap">Tanggal</label>
            <input type="date" id="filterDate" value="{{ $currentDate }}"
                   class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-4 focus:ring-[var(--accent)]/15 focus:border-[var(--accent)] outline-none bg-slate-50/50 hover:border-slate-400 transition-all"
                   onchange="applyFilter({date: this.value})">
        </div>
    @elseif ($tab === 'weekly')
        <div class="flex items-center gap-2.5">
            <label class="text-sm font-bold text-slate-700 whitespace-nowrap">Minggu</label>
            <input type="week" id="filterWeek" value="{{ $currentWeek }}"
                   class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-4 focus:ring-[var(--accent)]/15 focus:border-[var(--accent)] outline-none bg-slate-50/50 hover:border-slate-400 transition-all"
                   onchange="applyFilter({week: this.value})">
        </div>
    @elseif ($tab === 'monthly')
        <div class="flex items-center gap-2.5">
            <label class="text-sm font-bold text-slate-700 whitespace-nowrap">Bulan</label>
            <input type="month" id="filterMonth" value="{{ $currentMonth }}"
                   class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-4 focus:ring-[var(--accent)]/15 focus:border-[var(--accent)] outline-none bg-slate-50/50 hover:border-slate-400 transition-all"
                   onchange="applyFilter({month: this.value})">
        </div>
    @endif

    <div class="flex items-center gap-2.5">
        <label class="text-sm font-bold text-slate-700 whitespace-nowrap">Shift</label>
        <select id="filterShift"
                class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 w-32 focus:ring-4 focus:ring-[var(--accent)]/15 focus:border-[var(--accent)] outline-none bg-slate-50/50 hover:border-slate-400 transition-all"
                onchange="applyFilter({shift: this.value})">
            <option value="" {{ $currentShift === '' ? 'selected' : '' }}>Semua</option>
            <option value="siang" {{ $currentShift === 'siang' ? 'selected' : '' }}>Siang</option>
            <option value="malam" {{ $currentShift === 'malam' ? 'selected' : '' }}>Malam</option>
        </select>
    </div>
</div>

<div>
    @include('dashboard.partials.' . ($tab ?? 'daily'))
</div>

@push('scripts')
<script>
function applyFilter(overrides) {
    const params = new URLSearchParams(window.location.search);
    params.set('tab', '{{ $tab }}');
    Object.entries(overrides).forEach(([k, v]) => {
        if (v) params.set(k, v); else params.delete(k);
    });
    window.location.search = params.toString();
}
</script>
@endpush
@endsection
