@extends('layouts.app', ['headerTitle' => 'Riwayat Non-Ritasi'])

@section('title', 'Riwayat Non-Ritasi')

@section('content')

<div class="card p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
        <div>
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Shift</label>
            <select name="shift" class="form-input">
                <option value="">Semua Shift</option>
                <option value="siang" {{ request('shift') === 'siang' ? 'selected' : '' }}>Day</option>
                <option value="malam" {{ request('shift') === 'malam' ? 'selected' : '' }}>Night</option>
            </select>
        </div>
        <button type="submit" class="btn-primary flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-lg">filter_alt</span>
            Filter
        </button>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Shift</th>
                    <th class="px-4 py-3 text-left">Unit</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                    <th class="px-4 py-3 text-left">HM Awal</th>
                    <th class="px-4 py-3 text-left">HM Akhir</th>
                    <th class="px-4 py-3 text-left">Total</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($nonRitasis as $nr)
                <tr>
                    <td class="px-4 py-3">{{ $nr->tanggal }}</td>
                    <td class="px-4 py-3">{{ $nr->shift === 'siang' ? 'Day' : 'Night' }}</td>
                    <td class="px-4 py-3">{{ $nr->unit->kode ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $nr->lokasi_pekerjaan ?? '-' }}</td>
                    <td class="px-4 py-3">{{ number_format($nr->hm_awal, 1) }}</td>
                    <td class="px-4 py-3">{{ number_format($nr->hm_akhir, 1) }}</td>
                    <td class="px-4 py-3">{{ number_format($nr->hm_total, 1) }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $nr->status_badge }}">{{ ucfirst(str_replace('_', ' ', $nr->status)) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">
        {{ $nonRitasis->withQueryString()->links() }}
    </div>
</div>
@endsection
