@php
    $kpi = $kpi ?? [];
    $dailyOreOthers = $dailyOreOthers ?? [];
    $materialChart = $materialChart ?? ['names' => [], 'tonnage' => [], 'target' => [], 'gap' => []];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-2">
    <div class="card p-6">
        <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-[var(--primary)] text-xl">bar_chart</span>
            <span class="text-sm text-slate-500">All Material Hauling</span>
            <span class="text-3xl font-bold text-[var(--primary)]">{{ number_format((float)($kpi['tonnage'] ?? 0), 0) }}</span>
        </div>
        <div class="relative" style="height: 400px;">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <div class="card p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-[var(--primary)] text-xl">bar_chart</span>
            <span class="text-sm text-slate-500">Monthly Target Hauling</span>
            <span class="text-2xl font-bold text-[var(--primary)]">{{ number_format((float)($kpi['tonnage'] ?? 0), 0) }} ton</span>
        </div>
        @if (count($materialChart['names']) > 0)
            <div class="relative" style="height: {{ count($materialChart['names']) * 36 + 40 }}px;">
                <canvas id="monthlyMaterialChart"></canvas>
            </div>
        @else
            <div class="flex items-center justify-center h-32 text-sm text-slate-400">Belum ada data target bulanan</div>
        @endif
    </div>
</div>

<p class="text-xs text-slate-400 mb-6 px-1">Grafik kombinasi batang + garis. Batang gelap = tonase ore harian, batang terang = tonase lainnya. Garis = total kumulatif dari awal bulan. Kiri = tonase harian, kanan = tonase kumulatif.</p>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;

    const labels = {!! json_encode(array_column($dailyOreOthers, 'date')) !!};
    const ore = {!! json_encode(array_column($dailyOreOthers, 'ore')) !!};
    const others = {!! json_encode(array_column($dailyOreOthers, 'others')) !!};
    const cumulative = {!! json_encode(array_column($dailyOreOthers, 'cumulative')) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ore',
                    data: ore,
                    backgroundColor: '#1e3a5f',
                    borderRadius: 4,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Others',
                    data: others,
                    backgroundColor: '#93c5fd',
                    borderRadius: 4,
                    yAxisID: 'y',
                    order: 3
                },
                {
                    label: 'Cumulative',
                    data: cumulative,
                    type: 'line',
                    borderColor: '#1e3a5f',
                    backgroundColor: 'transparent',
                    pointBackgroundColor: '#1e3a5f',
                    pointRadius: 4,
                    borderWidth: 2,
                    tension: 0.3,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#475569', usePointStyle: true, padding: 16 }
                }
            },
            scales: {
                x: {
                    grid: { color: '#e2e8f0' },
                    ticks: { color: '#475569' }
                },
                y: {
                    type: 'linear',
                    position: 'left',
                    grid: { color: '#e2e8f0' },
                    ticks: { color: '#475569' },
                    title: { display: true, text: 'Daily', color: '#475569' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { color: '#1e3a5f' },
                    title: { display: true, text: 'Cumulative', color: '#1e3a5f' }
                }
            }
        }
    });

    // Monthly Material Target Chart
    const matCtx = document.getElementById('monthlyMaterialChart');
    if (matCtx) {
        const materialPalette = ['#1e3a5f', '#d97706', '#059669', '#dc2626', '#7c3aed', '#0284c7', '#ca8a04', '#db2777', '#475569', '#0d9488'];
        const mc = {!! json_encode($materialChart) !!};
        const materialColors = mc.names.map((_, i) => materialPalette[i % materialPalette.length]);
        const targetReached = mc.names.map((_, i) => mc.gap[i] <= 0);
        const targetValues = mc.target.map((v, i) => mc.target[i] > 0 ? v : null);

        new Chart(matCtx, {
            type: 'bar',
            data: {
                labels: mc.names,
                datasets: [
                    {
                        label: 'Tonase (ton)',
                        data: mc.tonnage,
                        backgroundColor: materialColors,
                        borderRadius: 4
                    },
                    {
                        label: 'Sisa target (ritasi)',
                        type: 'bar',
                        data: mc.gap,
                        backgroundColor: 'rgba(239, 68, 68, 0.30)',
                        borderRadius: 2,
                        xAxisID: 'x1'
                    },
                    {
                        label: 'Target (ritasi)',
                        type: 'line',
                        data: targetValues,
                        xAxisID: 'x1',
                        showLine: false,
                        pointStyle: 'line',
                        pointRadius: 7,
                        pointBorderWidth: 3,
                        pointBackgroundColor: 'transparent',
                        pointBorderColor: mc.names.map((_, i) => targetReached[i] ? '#10b981' : '#f59e0b')
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#e2e8f0' }, ticks: { color: '#475569' }, title: { display: true, text: 'Tonase (ton)' } },
                    x1: { position: 'top', grid: { drawOnChartArea: false }, ticks: { color: '#94a3b8' }, title: { display: true, text: 'Ritasi (target)' } },
                    y: { grid: { display: false }, ticks: { color: '#1e3a5f', font: { weight: 'bold' } } }
                }
            }
        });
    }
});
</script>
@endpush
