@php
    $kpi = $kpi ?? [];
    $haulingByMaterial = $haulingByMaterial ?? [];
    $availability = $availability ?? [];
    $uoa = $uoa ?? [];
    $typeLabels = ['excavator' => 'Excavator', 'dump_truck' => 'Dump Truck', 'bulldozer' => 'Bulldozer', 'loader' => 'Loader', 'motor_grader' => 'Motor Grader'];
    $typeShort = ['excavator' => 'Exc', 'dump_truck' => 'DT', 'bulldozer' => 'Dozer', 'loader' => 'LV', 'motor_grader' => 'MG'];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-2">
    <div class="card p-4">
        <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-[var(--primary)] text-xl">bar_chart</span>
            <span class="text-sm text-slate-500">Weekly All Hauling</span>
            <span class="text-2xl font-bold text-[var(--primary)]">{{ number_format((float)($kpi['tonnage'] ?? 0), 0) }}</span>
        </div>
        @if (count($haulingByMaterial) > 0)
            <div class="relative" style="height: {{ count($haulingByMaterial) * 36 + 40 }}px;">
                <canvas id="weeklyMaterialChart"></canvas>
            </div>
        @else
            <div class="flex items-center justify-center h-32 text-sm text-slate-400">Belum ada data hauling minggu ini</div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-[var(--primary)] text-xl">speed</span>
                <h3 class="font-heading font-bold text-[var(--primary)]">Availability</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">Persentase waktu unit siap digunakan dari total waktu operasional</p>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                @foreach ($availability as $a)
                    @php
                        $label = $typeShort[$a['type']] ?? $a['type'];
                        $pct = (float) $a['pct'];
                        $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <div class="flex flex-col items-center">
                        <canvas id="avail_{{ $a['type'] }}" width="90" height="55"></canvas>
                        <p class="text-xs font-bold text-[var(--primary)]">{{ $label }}</p>
                        <p class="text-xs font-semibold" style="color:{{ $color }}">{{ $a['pct'] }}%</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-[var(--primary)] text-xl">trending_up</span>
                <h3 class="font-heading font-bold text-[var(--primary)]">UoA</h3>
            </div>
            <p class="text-xs text-slate-500 mb-4">Unit Operating Availability &mdash; waktu unit benar-benar bekerja dari waktu tersedia</p>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                @foreach ($uoa as $u)
                    @php
                        $label = $typeShort[$u['type']] ?? $u['type'];
                        $pct = (float) $u['pct'];
                        $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <div class="flex flex-col items-center">
                        <canvas id="uoa_{{ $u['type'] }}" width="90" height="55"></canvas>
                        <p class="text-xs font-bold text-[var(--primary)]">{{ $label }}</p>
                        <p class="text-xs font-semibold" style="color:{{ $color }}">{{ $u['pct'] }}%</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<p class="text-xs text-slate-400 mb-6 px-1">Grafik batang = total tonase mingguan per material. Gauge = persentase Availability (ketersediaan unit) dan UoA (unit benar-benar bekerja) per tipe unit. Warna hijau &ge;80% (baik), kuning 50&ndash;79% (kurang), merah &lt;50% (rendah).</p>

<div class="card p-4 mb-6">
    <div class="flex flex-wrap items-center justify-center gap-6 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-green-500"></span> Baik (&ge;80%)</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Kurang (50&ndash;79%)</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500"></span> Rendah (&lt;50%)</span>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const matCtx = document.getElementById('weeklyMaterialChart');
    if (matCtx) {
        const materialPalette = ['#1e3a5f', '#d97706', '#059669', '#dc2626', '#7c3aed', '#0284c7', '#ca8a04', '#db2777', '#475569', '#0d9488'];
        const mc = {!! json_encode($materialChart ?? ['names' => [], 'tonnage' => [], 'target' => [], 'gap' => []]) !!};
        const materialColors = mc.names.map((_, i) => materialPalette[i % materialPalette.length]);
        const targetReached = mc.names.map((_, i) => mc.gap[i] <= 0);
        const targetValues = mc.target.map((v, i) => mc.target[i] > 0 ? v : null);

        new Chart(matCtx, {
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

    function renderGauge(id, pct, color) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [pct, 100 - pct],
                    backgroundColor: [color, '#e2e8f0'],
                    borderWidth: 0,
                    borderRadius: pct > 0 ? 6 : 0
                }]
            },
            options: {
                responsive: false,
                rotation: -90,
                circumference: 180,
                cutout: '78%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                animation: {
                    animateRotate: true,
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            }
        });
    }

    @foreach ($availability as $a)
        @php
            $pct = (float) $a['pct'];
            $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
        @endphp
        renderGauge('avail_{{ $a['type'] }}', {{ $pct }}, '{{ $color }}');
    @endforeach

    @foreach ($uoa as $u)
        @php
            $pct = (float) $u['pct'];
            $color = $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
        @endphp
        renderGauge('uoa_{{ $u['type'] }}', {{ $pct }}, '{{ $color }}');
    @endforeach
});
</script>
@endpush
