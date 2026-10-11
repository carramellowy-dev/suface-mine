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
            @if(request()->filled('tanggal_start') || request()->filled('tanggal_end'))
                <a href="{{ route("$role.utilization.index") }}" class="btn-secondary">Reset</a>
            @endif
        </form>
    </div>
</div>

@php
    // Kartu = rekap riwayat yang tampil (distinct unit per status, ikut filter tanggal).
    // Fallback ke hitungan status-terkini bila variabel rekap tidak tersedia.
    $breakdownCount = $recapBreakdown ?? $latestStatus->filter(fn ($t) => $t === 'breakdown')->count();
    $servisCount = $recapServis ?? $latestStatus->filter(fn ($t) => $t === 'servis')->count();
    $readyCount = $recapReady ?? max(0, ($totalUnits ?? 0) - $breakdownCount - $servisCount);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="stat-card border-l-4 border-red-500">
        <span class="material-symbols-outlined text-red-600">report</span>
        <div class="min-w-0">
            <p class="text-2xl font-bold" id="statBreakdown">{{ $breakdownCount }}</p>
            <p class="text-sm text-slate-500">Unit Breakdown</p>
        </div>
    </div>
    <div class="stat-card border-l-4 border-amber-500">
        <span class="material-symbols-outlined text-amber-600">build</span>
        <div class="min-w-0">
            <p class="text-2xl font-bold" id="statServis">{{ $servisCount }}</p>
            <p class="text-sm text-slate-500">Unit Servis</p>
        </div>
    </div>
    <div class="stat-card border-l-4 border-green-500">
        <span class="material-symbols-outlined text-green-600">check_circle</span>
        <div class="min-w-0">
            <p class="text-2xl font-bold" id="statReady">{{ $readyCount }}</p>
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
            <tbody class="divide-y" id="utilRows">
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
    <div class="p-4 border-t" id="utilPager">
        {{ $utilizations->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh real-time setiap 20 detik: kartu + baris riwayat + pagination
// diambil dari SATU respons halaman yang sama sehingga selalu sinkron satu sama
// lain, mengikuti filter & halaman yang sedang aktif. Tanpa mengubah fitur apa pun.
(function () {
    const cards = {
        breakdown: document.getElementById('statBreakdown'),
        servis: document.getElementById('statServis'),
        ready: document.getElementById('statReady'),
    };
    const rows = document.getElementById('utilRows');
    const pager = document.getElementById('utilPager');
    if (!cards.breakdown || !cards.servis || !cards.ready || !rows || !pager) return;
    let timer = null;
    async function refreshLive() {
        if (document.hidden) return;
        try {
            const res = await fetch(window.location.href, { headers: { Accept: 'text/html' } });
            if (!res.ok) return;
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const nCards = {
                breakdown: doc.getElementById('statBreakdown'),
                servis: doc.getElementById('statServis'),
                ready: doc.getElementById('statReady'),
            };
            const nRows = doc.getElementById('utilRows');
            const nPager = doc.getElementById('utilPager');
            // Tukar hanya bila struktur dikenali (abaikan bila sesi berakhir/login).
            if (!nCards.breakdown || !nCards.servis || !nCards.ready || !nRows || !nPager) return;
            cards.breakdown.textContent = nCards.breakdown.textContent;
            cards.servis.textContent = nCards.servis.textContent;
            cards.ready.textContent = nCards.ready.textContent;
            rows.innerHTML = nRows.innerHTML;
            pager.innerHTML = nPager.innerHTML;
        } catch { /* abaikan: coba lagi periode berikutnya */ }
    }
    timer = setInterval(refreshLive, 20000);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && timer) { clearInterval(timer); timer = null; }
        else if (!document.hidden && !timer) { refreshLive(); timer = setInterval(refreshLive, 20000); }
    });
})();
</script>
@endpush
