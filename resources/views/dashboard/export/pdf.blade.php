@extends('layouts.app')

@section('title', 'Laporan Grafis PDF - ' . ucfirst($meta['period'] ?? 'daily'))

@push('styles')
<style>
@page {
    size: A4 portrait;
    margin: 12mm 10mm;
}

@media print {
    .no-print, nav, aside, header, .topbar, #sidebar, .sidebar {
        display: none !important;
    }
    main.main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        padding-top: 0 !important;
        min-height: auto !important;
    }
    body {
        background: #ffffff !important;
        color: #0f172a !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .print-card {
        border: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }
    .page-break {
        page-break-before: always !important;
    }
}

.report-header-gradient {
    background: linear-gradient(135deg, #0f1d36 0%, #1e3a5f 100%);
}
</style>
@endpush

@section('content')
@php
    $targetUnit = strtolower($selectedUnit ?? $meta['unit'] ?? 'ton');
    $unitLabel = strtoupper($targetUnit);
    $period = $meta['period'] ?? 'daily';
    $periodTitle = match($period) {
        'weekly' => 'Mingguan (Weekly)',
        'monthly' => 'Bulanan (Monthly)',
        default => 'Harian (Daily)',
    };
    $tonnage = (float)($kpi['tonnage'] ?? 0);
    $fuel = (float)($kpi['fuel'] ?? 0);
    $active = (int)($kpi['active_units'] ?? 0);
    $maintenance = (int)($kpi['maintenance_units'] ?? 0);
    $pa = (float)($kpi['pa'] ?? 0);
    $ua = (float)($kpi['ua'] ?? 0);
    $materialChart = $materialChart ?? ['names' => [], 'tonnage' => [], 'target' => [], 'actualRitasi' => [], 'gap' => []];
    $haulingByMaterial = $haulingByMaterial ?? [];
    $timelineAvg = $timelineAvg ?? ['siang' => ['red' => 0, 'green' => 0, 'white' => 0], 'malam' => ['red' => 0, 'green' => 0, 'white' => 0], 'combined' => ['red' => 0, 'green' => 0, 'white' => 0]];
    $availability = $availability ?? [];
    $uoa = $uoa ?? [];
    $dailyOreOthers = $dailyOreOthers ?? [];
    $typeShort = ['excavator' => 'Exc', 'dump_truck' => 'DT', 'bulldozer' => 'Dozer', 'loader' => 'LV', 'motor_grader' => 'MG'];
@endphp

{{-- Toolbar Atas (Hanya tampil di layar, tersembunyi saat dicetak) --}}
<div class="mb-6 p-4 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-wrap items-center justify-between gap-4 no-print">
    <div class="flex items-center gap-3">
        <a href="{{ route((auth()->user()?->role ?? 'admin') . '.dashboard.index') }}?tab={{ $period }}&unit={{ $targetUnit }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            Dashboard
        </a>
        <div class="h-5 w-px bg-slate-200"></div>
        <div>
            <h1 class="text-base font-bold text-slate-800">Preview Laporan Grafis PDF</h1>
            <p class="text-xs text-slate-500">Tampilan siap cetak dengan grafik operasional lengkap</p>
        </div>
    </div>

    <div class="flex items-center gap-2.5">
        <a href="{{ route((auth()->user()?->role ?? 'admin') . '.dashboard.export') }}?{{ http_build_query(request()->except('format')) }}&format=excel"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors">
            <span class="material-symbols-outlined text-base">table_view</span>
            Unduh Excel
        </a>
        <button type="button" onclick="window.print()"
                class="btn-primary inline-flex items-center gap-2 text-xs py-1.5 px-4 shadow-sm bg-[var(--primary)] hover:opacity-95">
            <span class="material-symbols-outlined text-base">print</span>
            Cetak / Simpan PDF
        </button>
    </div>
</div>

{{-- AREA DOKUMEN CETAK (PRINT AREA) --}}
<div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm print-card text-slate-800">
    
    {{-- Header Dokumen Resmi --}}
    <div class="flex flex-wrap items-start justify-between gap-4 pb-6 border-b-2 border-slate-800 mb-6">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-[var(--primary)] text-white flex items-center justify-center font-bold text-xl shadow-sm">
                <span class="material-symbols-outlined text-2xl">monitoring</span>
            </div>
            <div>
                <span class="text-[11px] uppercase tracking-wider font-bold text-[var(--accent)] block">Surface Mine Production Monitoring</span>
                <h2 class="text-xl font-extrabold text-[var(--primary)] tracking-tight">LAPORAN OPERASIONAL & PERFORMA ALAT (PA/UA)</h2>
                <p class="text-xs text-slate-500 font-medium">Periode Pelaporan: <strong class="text-slate-700">{{ $periodTitle }}</strong> &bull; {{ $meta['start'] ?? '' }} s/d {{ $meta['end'] ?? '' }}</p>
            </div>
        </div>
        <div class="text-right text-xs text-slate-500 space-y-1">
            <div class="inline-block bg-slate-100 border border-slate-200 rounded-lg px-3 py-1 font-mono font-semibold text-slate-700">
                Satuan: <strong class="text-[var(--primary)] font-bold uppercase">{{ $unitLabel }}</strong>
            </div>
            <div>Shift: <strong>{{ request('shift') ? ucfirst(request('shift')) : 'Semua Shift' }}</strong></div>
            <div class="text-[11px] text-slate-400">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
        </div>
    </div>

    {{-- SECTION 1: KPI EXECUTIVE SUMMARY --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-sm text-[var(--primary)]">dashboard</span>
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Ringkasan Indikator Kinerja Utama (KPI)</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <p class="text-[11px] text-slate-500 font-semibold uppercase">Total Produksi</p>
                <p class="text-xl font-extrabold text-[var(--primary)] mt-1">
                    {{ number_format($tonnage, 1) }}
                    <span class="text-xs font-medium">{{ $unitLabel }}</span>
                </p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <p class="text-[11px] text-slate-500 font-semibold uppercase">Konsumsi Fuel</p>
                <p class="text-xl font-extrabold text-amber-600 mt-1">
                    {{ number_format($fuel, 1) }}
                    <span class="text-xs font-medium">L</span>
                </p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <p class="text-[11px] text-slate-500 font-semibold uppercase">Status Unit</p>
                <p class="text-xl font-extrabold text-slate-700 mt-1">
                    <span class="text-green-600">{{ $active }}</span>
                    <span class="text-xs text-slate-400 font-normal">Aktif /</span>
                    <span class="text-red-500 text-base">{{ $maintenance }}</span>
                </p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <p class="text-[11px] text-slate-500 font-semibold uppercase">Availability (PA)</p>
                <p class="text-xl font-extrabold text-[var(--primary)] mt-1">
                    {{ number_format($pa, 1) }}%
                </p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                <p class="text-[11px] text-slate-500 font-semibold uppercase">Utilization (UA)</p>
                <p class="text-xl font-extrabold text-[var(--primary)] mt-1">
                    {{ number_format($ua, 1) }}%
                </p>
            </div>
        </div>
    </div>

    {{-- SECTION 2: GRAFIK OPERASIONAL SESUAI PERIODE --}}
    <div class="mb-8 space-y-6">
        @if ($period === 'daily')
            {{-- GRAFIK HARIAN (DAILY) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Grafik 1: Hauling by Material --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 print-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-[var(--primary)]">bar_chart</span>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Hauling by Material</h4>
                        </div>
                        <span class="text-[11px] text-slate-500">Aktual vs Target Ritasi</span>
                    </div>
                    @if (count($haulingByMaterial) > 0)
                        <div class="relative" style="height: {{ max(count($haulingByMaterial) * 34 + 30, 160) }}px;">
                            <canvas id="pdfMaterialChart"></canvas>
                        </div>
                    @else
                        <div class="flex items-center justify-center h-32 text-xs text-slate-400">Tidak ada data hauling pada tanggal ini</div>
                    @endif
                </div>

                {{-- Grafik 2: Timeline Pemakaian Unit --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 print-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-[var(--primary)]">timelapse</span>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Proporsi Pemakaian Unit</h4>
                        </div>
                        <span class="text-[11px] text-slate-500">Maintenance / In Use / Standby</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center pt-2">
                        <div>
                            <div class="relative inline-block" style="width:110px;height:110px;">
                                <canvas id="pdfAvgCombined"></canvas>
                            </div>
                            <p class="text-[11px] font-bold text-slate-700 mt-1">Gabungan</p>
                            <p class="text-[10px] text-slate-400">Siang + Malam</p>
                        </div>
                        <div>
                            <div class="relative inline-block" style="width:110px;height:110px;">
                                <canvas id="pdfAvgSiang"></canvas>
                            </div>
                            <p class="text-[11px] font-bold text-slate-700 mt-1">Shift Siang</p>
                            <p class="text-[10px] text-slate-400">07:00 - 19:00</p>
                        </div>
                        <div>
                            <div class="relative inline-block" style="width:110px;height:110px;">
                                <canvas id="pdfAvgMalam"></canvas>
                            </div>
                            <p class="text-[11px] font-bold text-slate-700 mt-1">Shift Malam</p>
                            <p class="text-[10px] text-slate-400">19:00 - 07:00</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-4 mt-3 pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Maintenance</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> In Use</span>
                        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-slate-200"></span> Standby</span>
                    </div>
                </div>
            </div>

        @elseif ($period === 'weekly')
            {{-- GRAFIK MINGGUAN (WEEKLY) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Grafik Weekly All Hauling --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 print-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-[var(--primary)]">bar_chart</span>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Weekly All Hauling</h4>
                        </div>
                        <span class="text-xs font-bold text-[var(--primary)]">{{ number_format($tonnage, 0) }} {{ $unitLabel }}</span>
                    </div>
                    @if (count($haulingByMaterial) > 0)
                        <div class="relative" style="height: {{ max(count($haulingByMaterial) * 34 + 30, 160) }}px;">
                            <canvas id="pdfWeeklyMaterialChart"></canvas>
                        </div>
                    @else
                        <div class="flex items-center justify-center h-32 text-xs text-slate-400">Tidak ada data hauling minggu ini</div>
                    @endif
                </div>

                {{-- Gauges Availability & UoA --}}
                <div class="space-y-4">
                    <div class="p-4 bg-white rounded-xl border border-slate-200 print-card">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-base text-[var(--primary)]">speed</span>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Availability per Tipe Unit</h4>
                        </div>
                        <div class="grid grid-cols-5 gap-1.5 text-center">
                            @foreach ($availability as $a)
                                @php
                                    $label = $typeShort[$a['type']] ?? $a['type'];
                                    $pct = (float) $a['pct'];
                                    $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                                @endphp
                                <div class="flex flex-col items-center">
                                    <canvas id="pdf_avail_{{ $a['type'] }}" width="68" height="42"></canvas>
                                    <p class="text-[11px] font-bold text-[var(--primary)] mt-1">{{ $label }}</p>
                                    <p class="text-[11px] font-bold" style="color:{{ $color }}">{{ $a['pct'] }}%</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-4 bg-white rounded-xl border border-slate-200 print-card">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-base text-[var(--primary)]">trending_up</span>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">UoA (Operating Availability) per Tipe Unit</h4>
                        </div>
                        <div class="grid grid-cols-5 gap-1.5 text-center">
                            @foreach ($uoa as $u)
                                @php
                                    $label = $typeShort[$u['type']] ?? $u['type'];
                                    $pct = (float) $u['pct'];
                                    $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                                @endphp
                                <div class="flex flex-col items-center">
                                    <canvas id="pdf_uoa_{{ $u['type'] }}" width="68" height="42"></canvas>
                                    <p class="text-[11px] font-bold text-[var(--primary)] mt-1">{{ $label }}</p>
                                    <p class="text-[11px] font-bold" style="color:{{ $color }}">{{ $u['pct'] }}%</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        @elseif ($period === 'monthly')
            {{-- GRAFIK BULANAN (MONTHLY) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Grafik All Material Hauling (Ore vs Others + Kumulatif) --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 print-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-[var(--primary)]">stacked_bar_chart</span>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">All Material Hauling (Kumulatif)</h4>
                        </div>
                        <span class="text-xs font-bold text-[var(--primary)]">{{ number_format($tonnage, 0) }} {{ $unitLabel }}</span>
                    </div>
                    <div class="relative" style="height: 240px;">
                        <canvas id="pdfMonthlyChart"></canvas>
                    </div>
                </div>

                {{-- Grafik Monthly Target Hauling --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 print-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-[var(--primary)]">flag</span>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Target Bulanan per Material</h4>
                        </div>
                        <span class="text-[11px] text-slate-500">Aktual vs Target</span>
                    </div>
                    @if (count($materialChart['names'] ?? []) > 0)
                        <div class="relative" style="height: {{ max(count($materialChart['names']) * 34 + 30, 240) }}px;">
                            <canvas id="pdfMonthlyMaterialChart"></canvas>
                        </div>
                    @else
                        <div class="flex items-center justify-center h-48 text-xs text-slate-400">Tidak ada target bulanan</div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- SECTION 3: TABEL DATA RINCIAN REKAPITULASI --}}
    <div class="mt-8 pt-6 border-t border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-[var(--primary)]">table_chart</span>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Rincian Catatan Hauling ({{ count($rows) }} Data)</h4>
            </div>
            <span class="text-[11px] text-slate-500">Diurutkan berdasarkan tanggal & shift</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-2 px-2.5 text-center w-10">No</th>
                        <th class="py-2 px-2.5">Tanggal</th>
                        <th class="py-2 px-2.5">Shift</th>
                        <th class="py-2 px-2.5">Unit</th>
                        <th class="py-2 px-2.5">Material</th>
                        <th class="py-2 px-2.5 text-right">HM Total</th>
                        <th class="py-2 px-2.5 text-right">Qty ({{ $unitLabel }})</th>
                        <th class="py-2 px-2.5 text-right">Input Asli</th>
                        <th class="py-2 px-2.5 text-right">Fuel (L)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($rows as $index => $r)
                        <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                            <td class="py-1.5 px-2.5 text-center text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-1.5 px-2.5 font-medium whitespace-nowrap">{{ $r->tanggal?->format('d/m/Y') }}</td>
                            <td class="py-1.5 px-2.5">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $r->shift === 'siang' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $r->shift === 'siang' ? 'Day' : 'Night' }}
                                </span>
                            </td>
                            <td class="py-1.5 px-2.5 font-mono font-bold text-slate-700">{{ $r->unit->kode ?? '-' }}</td>
                            <td class="py-1.5 px-2.5 text-slate-800">{{ $r->material->nama ?? '-' }}</td>
                            <td class="py-1.5 px-2.5 text-right font-mono">{{ number_format((float)($r->hm_total ?? 0), 1) }}</td>
                            <td class="py-1.5 px-2.5 text-right font-bold text-[var(--primary)] font-mono">
                                {{ number_format((float)($r->quantityInUnit($targetUnit)), 2) }}
                            </td>
                            <td class="py-1.5 px-2.5 text-right text-slate-500 font-mono">
                                {{ number_format((float)($r->quantity ?? 0), 1) }} {{ strtoupper($r->quantity_unit ?? 'ton') }}
                            </td>
                            <td class="py-1.5 px-2.5 text-right font-mono text-slate-700">{{ number_format((float)($r->fuel_consumption ?? 0), 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-slate-400">Tidak ada catatan data hauling pada periode yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const palette = ['#1e3a5f', '#d97706', '#059669', '#dc2626', '#7c3aed', '#0284c7', '#ca8a04', '#db2777', '#475569', '#0d9488'];

    @if ($period === 'daily')
        // 1. Material Bar Chart (Daily)
        const matCanvas = document.getElementById('pdfMaterialChart');
        if (matCanvas) {
            const mc = {!! json_encode($materialChart ?? ['names' => [], 'actualRitasi' => [], 'gap' => [], 'target' => []]) !!};
            const materialColors = mc.names.map((_, i) => palette[i % palette.length]);
            const targetValues = mc.target.map((v, i) => mc.target[i] > 0 ? v : null);

            new Chart(matCanvas, {
                type: 'bar',
                data: {
                    labels: mc.names,
                    datasets: [
                        {
                            label: 'Ritasi aktual',
                            data: mc.actualRitasi,
                            backgroundColor: materialColors,
                            borderRadius: 3,
                            stack: 'main',
                            order: 2
                        },
                        {
                            label: 'Sisa target',
                            data: mc.gap,
                            backgroundColor: 'rgba(239, 68, 68, 0.25)',
                            borderRadius: 2,
                            stack: 'main',
                            order: 3
                        },
                        {
                            label: 'Target',
                            type: 'line',
                            data: targetValues,
                            showLine: false,
                            pointStyle: 'line',
                            pointRadius: 0,
                            pointBorderWidth: 2,
                            pointBorderColor: '#000',
                            pointRotation: 90,
                            order: 1
                        }
                    ]
                },
                options: {
                    animation: false,
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { color: '#475569', font: { size: 10 } } },
                        y: { stacked: true, grid: { display: false }, ticks: { color: '#1e3a5f', font: { weight: 'bold', size: 10 } } }
                    }
                }
            });
        }

        // 2. Donut Charts (Daily)
        function renderPdfDonut(canvasId, data) {
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Maintenance', 'In Use', 'Standby'],
                    datasets: [{
                        data: [data.red, data.green, data.white],
                        backgroundColor: ['#ef4444', '#22c55e', '#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    animation: false,
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '60%',
                    plugins: { legend: { display: false } }
                }
            });
        }
        renderPdfDonut('pdfAvgCombined', {!! json_encode($timelineAvg['combined']) !!});
        renderPdfDonut('pdfAvgSiang', {!! json_encode($timelineAvg['siang']) !!});
        renderPdfDonut('pdfAvgMalam', {!! json_encode($timelineAvg['malam']) !!});

    @elseif ($period === 'weekly')
        // 1. Weekly Material Chart
        const weeklyMatCanvas = document.getElementById('pdfWeeklyMaterialChart');
        if (weeklyMatCanvas) {
            const mc = {!! json_encode($materialChart ?? ['names' => [], 'actualRitasi' => [], 'gap' => [], 'target' => []]) !!};
            const materialColors = mc.names.map((_, i) => palette[i % palette.length]);
            const targetValues = mc.target.map((v, i) => mc.target[i] > 0 ? v : null);

            new Chart(weeklyMatCanvas, {
                type: 'bar',
                data: {
                    labels: mc.names,
                    datasets: [
                        {
                            label: 'Ritasi aktual',
                            data: mc.actualRitasi,
                            backgroundColor: materialColors,
                            borderRadius: 3,
                            stack: 'main',
                            order: 2
                        },
                        {
                            label: 'Sisa target',
                            data: mc.gap,
                            backgroundColor: 'rgba(239, 68, 68, 0.25)',
                            borderRadius: 2,
                            stack: 'main',
                            order: 3
                        },
                        {
                            label: 'Target',
                            type: 'line',
                            data: targetValues,
                            showLine: false,
                            pointStyle: 'line',
                            pointRadius: 0,
                            pointBorderWidth: 2,
                            pointBorderColor: '#000',
                            pointRotation: 90,
                            order: 1
                        }
                    ]
                },
                options: {
                    animation: false,
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { color: '#475569', font: { size: 10 } } },
                        y: { stacked: true, grid: { display: false }, ticks: { color: '#1e3a5f', font: { weight: 'bold', size: 10 } } }
                    }
                }
            });
        }

        // 2. Gauges Availability & UoA (Weekly)
        function renderPdfGauge(id, pct, color) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [pct, 100 - pct],
                        backgroundColor: [color, '#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    animation: false,
                    responsive: false,
                    rotation: -90,
                    circumference: 180,
                    cutout: '76%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
        }

        @foreach ($availability as $a)
            @php
                $pct = (float) $a['pct'];
                $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
            @endphp
            renderPdfGauge('pdf_avail_{{ $a['type'] }}', {{ $pct }}, '{{ $color }}');
        @endforeach

        @foreach ($uoa as $u)
            @php
                $pct = (float) $u['pct'];
                $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
            @endphp
            renderPdfGauge('pdf_uoa_{{ $u['type'] }}', {{ $pct }}, '{{ $color }}');
        @endforeach

    @elseif ($period === 'monthly')
        // 1. Monthly Bar & Line Chart
        const mCtx = document.getElementById('pdfMonthlyChart');
        if (mCtx) {
            const labels = {!! json_encode(array_column($dailyOreOthers, 'date')) !!};
            const ore = {!! json_encode(array_column($dailyOreOthers, 'ore')) !!};
            const others = {!! json_encode(array_column($dailyOreOthers, 'others')) !!};
            const cumulative = {!! json_encode(array_column($dailyOreOthers, 'cumulative')) !!};

            new Chart(mCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Ore',
                            data: ore,
                            backgroundColor: '#1e3a5f',
                            borderRadius: 3,
                            yAxisID: 'y',
                            order: 2
                        },
                        {
                            label: 'Material Lain',
                            data: others,
                            backgroundColor: '#d97706',
                            borderRadius: 3,
                            yAxisID: 'y',
                            order: 3
                        },
                        {
                            label: 'Kumulatif ({{ $unitLabel }})',
                            type: 'line',
                            data: cumulative,
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5, 150, 105, 0.1)',
                            fill: false,
                            tension: 0.2,
                            yAxisID: 'y1',
                            order: 1
                        }
                    ]
                },
                options: {
                    animation: false,
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 9 }, color: '#64748b' } },
                        y: { position: 'left', grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 }, color: '#64748b' } },
                        y1: { position: 'right', grid: { display: false }, ticks: { font: { size: 9 }, color: '#059669' } }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 } } }
                    }
                }
            });
        }

        // 2. Monthly Material Chart
        const mmCtx = document.getElementById('pdfMonthlyMaterialChart');
        if (mmCtx) {
            const mc = {!! json_encode($materialChart ?? ['names' => [], 'actualRitasi' => [], 'gap' => [], 'target' => []]) !!};
            const materialColors = mc.names.map((_, i) => palette[i % palette.length]);
            const targetValues = mc.target.map((v, i) => mc.target[i] > 0 ? v : null);

            new Chart(mmCtx, {
                type: 'bar',
                data: {
                    labels: mc.names,
                    datasets: [
                        {
                            label: 'Ritasi aktual',
                            data: mc.actualRitasi,
                            backgroundColor: materialColors,
                            borderRadius: 3,
                            stack: 'main',
                            order: 2
                        },
                        {
                            label: 'Sisa target',
                            data: mc.gap,
                            backgroundColor: 'rgba(239, 68, 68, 0.25)',
                            borderRadius: 2,
                            stack: 'main',
                            order: 3
                        },
                        {
                            label: 'Target',
                            type: 'line',
                            data: targetValues,
                            showLine: false,
                            pointStyle: 'line',
                            pointRadius: 0,
                            pointBorderWidth: 2,
                            pointBorderColor: '#000',
                            pointRotation: 90,
                            order: 1
                        }
                    ]
                },
                options: {
                    animation: false,
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 9 }, color: '#64748b' } },
                        y: { stacked: true, grid: { display: false }, ticks: { font: { size: 9, weight: 'bold' }, color: '#1e3a5f' } }
                    }
                }
            });
        }
    @endif
});
</script>
@endpush
@endsection
