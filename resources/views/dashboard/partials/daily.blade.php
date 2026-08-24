@php
    $kpi = $kpi ?? [];
    $pies = $pies ?? [];
    $haulingByMaterial = $haulingByMaterial ?? [];
    $timelineSiang = $timelineSiang ?? [];
    $timelineMalam = $timelineMalam ?? [];
    $timelineAvg = $timelineAvg ?? ['siang' => ['red' => 0, 'green' => 0, 'white' => 0], 'malam' => ['red' => 0, 'green' => 0, 'white' => 0], 'combined' => ['red' => 0, 'green' => 0, 'white' => 0]];
    $timelineGrouped = $timelineGrouped ?? [];
@endphp

@php
    $tonnage = (float)($kpi['tonnage'] ?? 0);
    $fuel = (float)($kpi['fuel'] ?? 0);
    $active = (int)($kpi['active_units'] ?? 0);
    $maintenance = (int)($kpi['maintenance_units'] ?? 0);
    $pa = (float)($kpi['pa'] ?? 0);
    $hasData = $tonnage > 0 || $fuel > 0;
@endphp

<div class="card p-6 mb-2">
    <div class="text-center mb-5">
        <div class="inline-flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-[var(--primary)]">scale</span>
            <p class="text-sm text-slate-500 uppercase tracking-wide font-semibold">Total Tonnage</p>
        </div>
        <p class="text-5xl font-bold text-[var(--primary)]">{{ number_format($tonnage, 0) }}<span class="text-2xl font-normal ml-2">ton</span></p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-5 border-t border-slate-100">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-50 mb-2">
                <span class="material-symbols-outlined text-amber-600 text-xl">local_gas_station</span>
            </div>
            <p class="text-xs text-slate-500 font-medium">Fuel</p>
            <p class="text-xl font-bold text-slate-700">{{ number_format($fuel, 1) }} L</p>
        </div>
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-50 mb-2">
                <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
            </div>
            <p class="text-xs text-slate-500 font-medium">Active</p>
            <p class="text-xl font-bold text-green-600">{{ $active }}</p>
        </div>
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-50 mb-2">
                <span class="material-symbols-outlined text-red-600 text-xl">build</span>
            </div>
            <p class="text-xs text-slate-500 font-medium">Maintenance</p>
            <p class="text-xl font-bold text-red-600">{{ $maintenance }}</p>
        </div>
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[var(--primary)]/10 mb-2">
                <span class="material-symbols-outlined text-[var(--primary)] text-xl">speed</span>
            </div>
            <p class="text-xs text-slate-500 font-medium">PA / UA</p>
            <p class="text-xl font-bold text-[var(--primary)]">{{ number_format($pa, 1) }}%</p>
        </div>
    </div>
</div>
<p class="text-xs text-slate-400 mb-6 px-1">Ringkasan data operasional harian. Total tonnage adalah jumlah material yang diangkut. Fuel = bahan bakar terpakai. Active = unit aktif. Maintenance = unit dalam perbaikan. PA/UA = Physical Availability / Utilization Availability.</p>

@if (!$hasData)
    <div class="card p-4 mb-6 bg-amber-50 border border-amber-200">
        <div class="flex items-center gap-2 text-amber-700">
            <span class="material-symbols-outlined text-xl">info</span>
            <p class="text-sm font-medium">Tidak ada data untuk tanggal ini. Silakan pilih tanggal lain atau input data terlebih dahulu.</p>
        </div>
    </div>
@endif

<div class="card p-4 mb-2">
    <div class="flex items-center gap-2 mb-3">
        <span class="material-symbols-outlined text-[var(--primary)] text-xl">bar_chart</span>
        <h2 class="section-title">Hauling by Material</h2>
    </div>
    @if (count($haulingByMaterial) > 0)
        <div class="relative" style="height: {{ count($haulingByMaterial) * 36 + 40 }}px;">
            <canvas id="materialChart"></canvas>
        </div>
    @else
        <div class="flex items-center justify-center h-32 text-sm text-slate-400">Belum ada data hauling hari ini</div>
    @endif
</div>
<p class="text-xs text-slate-400 mb-6 px-1">Grafik batang horizontal menunjukkan jumlah tonase tiap jenis material yang diangkut hari ini. Semakin panjang batang, semakin banyak material tersebut diangkut.</p>

<div class="card p-4 mb-2">
    <div class="flex items-center gap-2 mb-4">
        <span class="material-symbols-outlined text-[var(--primary)] text-xl">timeline</span>
        <h2 class="section-title">Timeline Pemakaian Unit</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="text-center">
            <p class="text-sm font-semibold text-slate-600 mb-2">Rata-rata per Unit</p>
            <div class="relative inline-block" style="width:160px;height:160px;">
                <canvas id="avgCombined"></canvas>
            </div>
            <p class="text-xs text-slate-500 mt-1">Gabungan (Siang + Malam)</p>
        </div>
        <div class="text-center">
            <p class="text-sm font-semibold text-slate-600 mb-2">Shift Siang</p>
            <div class="relative inline-block" style="width:160px;height:160px;">
                <canvas id="avgSiang"></canvas>
            </div>
            <p class="text-xs text-slate-500 mt-1">Total jam rata-rata/unit</p>
        </div>
        <div class="text-center">
            <p class="text-sm font-semibold text-slate-600 mb-2">Shift Malam</p>
            <div class="relative inline-block" style="width:160px;height:160px;">
                <canvas id="avgMalam"></canvas>
            </div>
            <p class="text-xs text-slate-500 mt-1">Total jam rata-rata/unit</p>
        </div>
    </div>

    <div class="flex gap-2 mb-4">
        <button onclick="showShift('siang')" id="tabSiang" class="px-4 py-2 rounded-lg text-sm font-semibold transition bg-[var(--primary)] text-white">Shift Siang</button>
        <button onclick="showShift('malam')" id="tabMalam" class="px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-200 text-slate-600">Shift Malam</button>
    </div>

    @forelse ($timelineGrouped as $tipe => $group)
        @php
            $avg = $group['avg'];
            $total = ($avg['red'] + $avg['green'] + $avg['white']) ?: 1.0;
            $pRed = ($avg['red'] / $total) * 100;
            $pAct = (($avg['red'] + $avg['green']) / $total) * 100;
        @endphp
        <div class="border border-slate-200 rounded-lg mb-2">
            <button onclick="toggleTipe('{{ $tipe }}')" class="w-full flex items-center justify-between px-4 py-3 hover:bg-slate-50 transition rounded-lg">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-slate-400 transition-transform" id="chevron-{{ $tipe }}">expand_more</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ $tipe }}</span>
                    <span class="text-xs text-slate-500">({{ $group['count'] }} unit)</span>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>{{ number_format($avg['red'], 1) }}j</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span>{{ number_format($avg['green'], 1) }}j</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-200"></span>{{ number_format($avg['white'], 1) }}j</span>
                    <span class="text-slate-400 ml-1">avg/unit</span>
                </div>
            </button>
            <div id="tipe-{{ $tipe }}" class="hidden px-4 pb-3 space-y-2">
                @foreach ($group['items'] as $item)
                    @php
                        $si = $item['siang'];
                        $mi = $item['malam'];
                        $r = $si['red'] + $mi['red'];
                        $g = $si['green'] + $mi['green'];
                        $w = $si['white'] + $mi['white'];
                        $t = ($r + $g + $w) ?: 1.0;
                        $pr = ($r / $t) * 100;
                        $pa = (($r + $g) / $t) * 100;
                    @endphp
                    <div class="shift-row" data-si='{"r":{{ $si["red"] }},"g":{{ $si["green"] }},"w":{{ $si["white"] }}}' data-ma='{"r":{{ $mi["red"] }},"g":{{ $mi["green"] }},"w":{{ $mi["white"] }}}'>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-slate-700 unit-kode">{{ $item['kode'] }}</span>
                            <span class="text-slate-500 shift-stats"></span>
                        </div>
                        <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-3 rounded-full shift-bar" style="width:100%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-500">Belum ada data unit.</p>
    @endforelse

    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500"></span> Maintenance</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500"></span> In Use</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-slate-200"></span> Standby</span>
    </div>
</div>
<p class="text-xs text-slate-400 mb-6 px-1">Diagram donut menunjukkan rata-rata jam pemakaian per unit: merah = maintenance (perbaikan), hijau = in use (sedang dipakai), abu = standby (tidak dipakai). Klik tipe unit untuk melihat detail masing-masing unit.</p>

<div class="card overflow-hidden mb-2">
    <div class="p-4 border-b flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[var(--primary)] text-xl">table_view</span>
            <h2 class="section-title">Hauling Records</h2>
        </div>
        <span class="text-xs text-slate-500">{{ $hauling->total() }} record{{ $hauling->total() != 1 ? 's' : '' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">TANGGAL</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">SHIFT</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">UNIT</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-slate-600">MATERIAL</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">HM</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">TON</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold text-slate-600">FUEL</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($hauling as $r)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $r->tanggal?->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $r->shift === 'siang' ? 'Day' : 'Night' }}</td>
                        <td class="px-4 py-2 text-sm font-mono">{{ $r->unit->kode ?? '-' }}</td>
                        <td class="px-4 py-2 text-sm">{{ $r->material->nama ?? '-' }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ number_format((float)($r->hm_total ?? 0), 1) }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ number_format((float)($r->quantity_tonnes ?? 0), 2) }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ number_format((float)($r->fuel_consumption ?? 0), 1) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t">{{ $hauling->links() }}</div>
</div>
<p class="text-xs text-slate-400 mb-6 px-1">Daftar seluruh catatan hauling (pengangkutan material) hari ini. Setiap baris menunjukkan tanggal, shift, unit, jenis material, HM (hour meter), tonase, dan konsumsi bahan bakar.</p>

@push('scripts')
<script>
function showShift(shift) {
    document.querySelectorAll('.shift-row').forEach(row => {
        const d = JSON.parse(row.getAttribute(shift === 'siang' ? 'data-si' : 'data-ma'));
        const total = (d.r + d.g + d.w) || 1;
        const pRed = (d.r / total) * 100;
        const pAct = ((d.r + d.g) / total) * 100;
        row.querySelector('.shift-stats').innerHTML =
            '<span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>' + d.r.toFixed(1) + 'j</span>' +
            '<span class="inline-flex items-center gap-1 ml-1.5"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>' + d.g.toFixed(1) + 'j</span>' +
            '<span class="inline-flex items-center gap-1 ml-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-200"></span>' + d.w.toFixed(1) + 'j</span>';
        row.querySelector('.shift-bar').style.background =
            'linear-gradient(to right, #ef4444 0%, #ef4444 ' + pRed + '%, #22c55e ' + pRed + '%, #22c55e ' + pAct + '%, #e2e8f0 ' + pAct + '%, #e2e8f0 100%)';
    });
    document.querySelectorAll('[id^="tipe-"]').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[id^="chevron-"]').forEach(el => el.style.transform = '');
    document.getElementById('tabSiang').className = shift === 'siang' ? 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-[var(--primary)] text-white' : 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-200 text-slate-600';
    document.getElementById('tabMalam').className = shift === 'malam' ? 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-[var(--primary)] text-white' : 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-slate-200 text-slate-600';
}

function toggleTipe(tipe) {
    const el = document.getElementById('tipe-' + tipe);
    const chevron = document.getElementById('chevron-' + tipe);
    const isHidden = el.classList.contains('hidden');
    el.classList.toggle('hidden');
    chevron.style.transform = isHidden ? 'rotate(180deg)' : '';
}

function renderDonut(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    const total = data.red + data.green + data.white;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Maintenance', 'In Use', 'Standby'],
            datasets: [{ data: [data.red, data.green, data.white], backgroundColor: ['#ef4444', '#22c55e', '#e2e8f0'], borderWidth: 0, borderRadius: 3 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(c) {
                            const v = c.raw;
                            const pct = total > 0 ? ((v / total) * 100).toFixed(1) : 0;
                            return c.label + ': ' + v.toFixed(1) + 'j (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const materialCtx = document.getElementById('materialChart');
    if (materialCtx) {
        const materialPalette = ['#1e3a5f', '#d97706', '#059669', '#dc2626', '#7c3aed', '#0284c7', '#ca8a04', '#db2777', '#475569', '#0d9488'];
        const mc = {!! json_encode($materialChart ?? ['names' => [], 'tonnage' => [], 'target' => [], 'gap' => []]) !!};
        const materialColors = mc.names.map((_, i) => materialPalette[i % materialPalette.length]);
        const targetReached = mc.names.map((_, i) => mc.gap[i] <= 0);
        // Only show a target marker for materials that actually have a target.
        const targetValues = mc.target.map((v, i) => mc.target[i] > 0 ? v : null);

        new Chart(materialCtx, {
            type: 'bar',
            data: {
                labels: mc.names,
                datasets: [
                    {
                        label: 'Ritasi aktual',
                        data: mc.actualRitasi,
                        backgroundColor: materialColors,
                        borderRadius: 4,
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
                        label: 'Target (ritasi)',
                        type: 'line',
                        data: targetValues,
                        showLine: false,
                        pointStyle: 'line',
                        pointRadius: 0, // No dot, just line
                        pointBorderWidth: 2, // Line thickness
                        pointBorderColor: '#000',
                        pointRotation: 90, // Rotate horizontal line to vertical
                        order: 1
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { stacked: true, grid: { color: '#e2e8f0' }, ticks: { color: '#475569' } },
                    y: { stacked: true, grid: { display: false }, ticks: { color: '#1e3a5f', font: { weight: 'bold' } } }
                }
            }
        });
    }

    renderDonut('avgCombined', {!! json_encode($timelineAvg['combined']) !!});
    renderDonut('avgSiang', {!! json_encode($timelineAvg['siang']) !!});
    renderDonut('avgMalam', {!! json_encode($timelineAvg['malam']) !!});
    showShift('siang');
});
</script>
@endpush
