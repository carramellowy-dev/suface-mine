@extends('layouts.app')

@section('title', 'Export PA/UA')

@push('styles')
<style>
@page { size: A4; margin: 1cm; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #cbd5e1; padding: 4px 6px; }
.num { text-align: right; }
.h { background: #f1f5f9; }
.h th { color: #1e3a5f; }
h1 { color: #1e3a5f; }
@media print { .no-print { display: none !important; } }
</style>
@endpush

@section('content')
<div class="p-4 no-print">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Laporan PA/UA - {{ ucfirst($meta['period'] ?? 'daily') }}</h1>
        <button onclick="window.print()" class="btn-secondary flex items-center gap-2 no-print">
            <span class="material-symbols-outlined">print</span> Cetak
        </button>
    </div>
</div>
<div class="p-4 print-area">
    <p class="text-sm text-slate-600 mb-4">{{ $meta['start'] ?? '' }} s/d {{ $meta['end'] ?? '' }}</p>
    <table class="w-full mb-4">
        <tr><td class="num"><b>Petrol (L)</b></td><td class="num">{{ number_format((float)($kpi['fuel'] ?? 0), 2) }}</td>
            <td class="num"><b>Tonnage (ton)</b></td><td class="num">{{ number_format((float)($kpi['tonnage'] ?? 0), 2) }}</td></tr>
        <tr><td class="num"><b>PA %</b></td><td class="num">{{ number_format((float)($kpi['pa'] ?? 0), 2) }}</td>
            <td class="num"><b>UA %</b></td><td class="num">{{ number_format((float)($kpi['ua'] ?? 0), 2) }}</td></tr>
        <tr><td class="num"><b>Active Units</b></td><td class="num">{{ (int)($kpi['active_units'] ?? 0) }}</td>
            <td class="num"><b>Maintenance Units</b></td><td class="num">{{ (int)($kpi['maintenance_units'] ?? 0) }}</td></tr>
    </table>
    <table border="1" class="w-full text-sm">
        <thead class="h">
            <tr><th>Tanggal</th><th>Shift</th><th>Unit</th><th>Material</th>
                <th class="num">HM Total</th><th class="num">Ton</th>
                <th class="num">Quantity</th><th>Satuan</th><th class="num">Fuel (L)</th></tr>
        </thead>
        @forelse ($rows as $r)
            <tr><td>{{ $r->tanggal?->format('d M Y') }}</td><td>{{ $r->shift === 'siang' ? 'Day' : 'Night' }}</td>
                <td>{{ $r->unit->kode ?? '-' }}</td><td>{{ $r->material->nama ?? '-' }}</td>
                <td class="num">{{ number_format((float)($r->hm_total ?? 0), 2) }}</td><td class="num">{{ number_format((float)($r->quantity_tonnes ?? 0), 2) }}</td>
                <td class="num">{{ number_format((float)($r->quantity ?? 0), 2) }}</td><td>{{ $r->quantity_unit ?? 'ton' }}</td>
                <td class="num">{{ number_format((float)($r->fuel_consumption ?? 0), 2) }}</td></tr>
        @empty
            <tr><td colspan="9" class="p-4 text-center">Tidak ada data</td></tr>
        @endforelse
    </table>
</div>
@endsection
