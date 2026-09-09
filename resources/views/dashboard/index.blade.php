@extends('layouts.app', ['headerTitle' => 'Dashboard'])

@section('title', 'Dashboard')

@section('content')
@php
    $tabs = [
        'daily'   => ['label' => 'Harian',   'icon' => 'calendar_today'],
        'weekly'  => ['label' => 'Mingguan',  'icon' => 'calendar_view_week'],
        'monthly' => ['label' => 'Bulanan',  'icon' => 'calendar_month'],
    ];
    $q = request()->collect()->except(['tab', 'date', 'week', 'month', 'shift', 'unit'])->all();
    $currentDate = request('date', now()->format('Y-m-d'));
    $currentWeek = request('week', now()->format('Y-\WW'));
    $currentMonth = request('month', now()->format('Y-m'));
    $currentShift = request('shift', '');
    $currentUnit = request('unit', $selectedUnit ?? 'ton');
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

    <button type="button" onclick="openModal('exportModal')"
            class="btn-secondary flex items-center gap-2 text-sm py-1.5 px-3 hover:border-[var(--accent)] hover:text-[var(--primary)] transition-all">
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

    <div class="flex items-center gap-2.5">
        <label class="text-sm font-bold text-slate-700 whitespace-nowrap">Satuan</label>
        <select id="filterUnit"
                class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 w-32 focus:ring-4 focus:ring-[var(--accent)]/15 focus:border-[var(--accent)] outline-none bg-slate-50/50 hover:border-slate-400 transition-all font-semibold text-[var(--primary)]"
                onchange="applyFilter({unit: this.value})">
            <option value="ton" {{ $currentUnit === 'ton' ? 'selected' : '' }}>Ton (Berat)</option>
            <option value="bcm" {{ $currentUnit === 'bcm' ? 'selected' : '' }}>BCM (Volume)</option>
            <option value="m3" {{ $currentUnit === 'm3' ? 'selected' : '' }}>M³ (Volume)</option>
            <option value="cbm" {{ $currentUnit === 'cbm' ? 'selected' : '' }}>CBM (Volume)</option>
        </select>
    </div>
</div>

<div>
    @include('dashboard.partials.' . ($tab ?? 'daily'))
</div>

{{-- Modal Pilih Format Ekspor --}}
<div id="exportModal" onclick="if(event.target === this) closeModal('exportModal')" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[var(--primary)]/10 text-[var(--primary)] flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">ios_share</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Pilih Format Ekspor Laporan</h3>
                    <p class="text-xs text-slate-500">Pilih tipe keluaran laporan yang Anda perlukan</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('exportModal')" class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <div class="my-4 p-3 bg-slate-50 rounded-xl border border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-600 gap-2">
            <span>Periode: <strong class="text-slate-800">{{ ucfirst($tab) }} ({{ $headerDate ?? '-' }})</strong></span>
            <span>Satuan: <strong class="text-[var(--primary)] uppercase font-bold">{{ $currentUnit }}</strong></span>
            <span>Shift: <strong class="text-slate-800">{{ $currentShift ? ucfirst($currentShift) : 'Semua' }}</strong></span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-5">
            <!-- Pilihan 1: Excel (Data Tabular Mentah) -->
            <button type="button" onclick="triggerExport('excel')"
                    class="group relative flex flex-col text-left p-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/40 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform shadow-sm">
                    <span class="material-symbols-outlined text-2xl">table_view</span>
                </div>
                <h4 class="font-bold text-sm text-slate-800 group-hover:text-emerald-700 mb-1">Format Excel (.xls)</h4>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Data tabular mentah seluruh catatan hauling per ritasi, jam kerja HM, dan konsumsi fuel untuk analisis spreadsheet.
                </p>
                <div class="mt-auto pt-2 flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                    <span class="material-symbols-outlined text-base">download</span> Download Excel
                </div>
            </button>

            <!-- Pilihan 2: PDF (Laporan Visual Grafik) -->
            <button type="button" onclick="triggerExport('pdf')"
                    class="group relative flex flex-col text-left p-4 rounded-xl border-2 border-slate-200 hover:border-rose-500 hover:bg-rose-50/40 transition-all focus:outline-none focus:ring-2 focus:ring-rose-400">
                <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center mb-3 group-hover:scale-105 transition-transform shadow-sm">
                    <span class="material-symbols-outlined text-2xl">bar_chart</span>
                </div>
                <h4 class="font-bold text-sm text-slate-800 group-hover:text-rose-700 mb-1">Laporan Grafis (PDF)</h4>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Laporan visual lengkap dengan grafik statistik (Hauling by Material, Timeline, Availability, UoA) sama persis seperti dashboard.
                </p>
                <div class="mt-auto pt-2 flex items-center gap-1.5 text-xs font-semibold text-rose-600">
                    <span class="material-symbols-outlined text-base">open_in_new</span> Buka & Cetak Grafik PDF
                </div>
            </button>
        </div>

        <div class="flex justify-end pt-3 border-t border-slate-100">
            <button type="button" onclick="closeModal('exportModal')" class="btn-secondary text-sm py-1.5 px-4">
                Batal
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal('exportModal');
});

function triggerExport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('tab', '{{ $tab }}');
    params.set('period', '{{ $tab }}');
    params.set('format', format);
    const exportUrl = '{{ route(auth()->user()->role . ".dashboard.export") }}?' + params.toString();
    closeModal('exportModal');
    if (format === 'pdf') {
        window.open(exportUrl, '_blank');
    } else {
        window.location.href = exportUrl;
    }
}

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
