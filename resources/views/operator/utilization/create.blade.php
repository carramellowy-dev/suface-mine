@extends('layouts.app', ['headerTitle' => 'Form Input Utilization'])

@section('title', 'Form Input Utilization')

@section('content')

@include('operator.partials.validation-errors')

@include('operator.partials.session-info', ['description' => 'Catat kondisi unit: pilih Breakdown jika unit rusak, atau Servis jika unit telah ditangani.'])

<form action="{{ route('pegawai.utilization.store') }}" method="POST" data-offline-form data-sync-tag="utilization-sync">
    @csrf
    <div class="card p-6">
        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">build</span>
            Data Utilization
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="form-label">Nomor Unit</label>
                <select name="unit_id" id="unit_id" class="form-input" required>
                    <option value="">Pilih Unit</option>
                    @foreach($units as $unit)
                        @php
                            $currentStatus = $latestStatus->get($unit->id, '');
                            $statusBadge = match($currentStatus) {
                                'breakdown' => ' — [BREAKDOWN / RUSAK]',
                                'servis' => ' — [SERVIS / PERBAIKAN]',
                                'ready' => ' — [READY]',
                                default => ''
                            };
                        @endphp
                        <option value="{{ $unit->id }}" data-status="{{ $currentStatus }}">{{ $unit->kode }}{{ $statusBadge }}</option>
                    @endforeach
                </select>
                <p id="statusHint" class="mt-1.5 text-sm font-semibold text-slate-500"></p>
                <p class="mt-1 text-xs sm:text-sm text-slate-500">Pilih unit alat berat yang statusnya ingin diubah.</p>
            </div>
            <div>
                <label class="form-label">Status Baru</label>
                <div class="flex gap-4 mt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="breakdown" id="radioBreakdown" required class="text-[var(--accent)] focus:ring-[var(--accent)]">
                        <span class="text-red-600 font-bold text-sm sm:text-base">Breakdown (Rusak)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="servis" id="radioServis" required class="text-[var(--accent)] focus:ring-[var(--accent)]">
                        <span class="text-amber-600 font-bold text-sm sm:text-base">Servis (Perbaikan)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="ready" id="radioReady" required class="text-[var(--accent)] focus:ring-[var(--accent)]">
                        <span class="text-green-600 font-bold text-sm sm:text-base">Ready (Siap Kerja / Cabut)</span>
                    </label>
                </div>
                <p class="mt-3 text-xs sm:text-sm text-slate-500">Status baru dari unit ini.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="form-label">Tanggal / Jam Mulai</label>
                <input type="datetime-local" name="started_at" class="form-input" id="startedAt">
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Kapan unit mulai breakdown/servis.</p>
            </div>
            <div>
                <label class="form-label">Tanggal / Jam Selesai (Ready)</label>
                <input type="datetime-local" name="ended_at" class="form-input" id="endedAt">
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Diisi saat unit selesai diperbaiki dan dicabut status breakdown/servisnya (Ready).</p>
            </div>
        </div>

        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">description</span>
            Keterangan
        </h2>
        <div class="grid grid-cols-1 gap-6 mb-6">
            <div>
                <label class="form-label">Deskripsi / Kerusakan</label>
                <textarea name="deskripsi" class="form-input" rows="3" placeholder="Contoh: Perbaikan selesai, oli dan hose hidrolik sudah diganti..."></textarea>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Tuliskan gejala kerusakan, sparepart yang diganti, atau jenis perawatan yang dilakukan.</p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="reset" class="btn-secondary">Reset</button>
            <button type="submit" class="btn-primary flex items-center gap-2">
                <span class="material-symbols-outlined">save</span>
                Simpan Data Utilization
            </button>
        </div>
    </div>
</form>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('unit_id');
    const hint = document.getElementById('statusHint');
    const radioBreakdown = document.getElementById('radioBreakdown');
    const radioServis = document.getElementById('radioServis');
    const radioReady = document.getElementById('radioReady');
    const startedAt = document.getElementById('startedAt');
    const endedAt = document.getElementById('endedAt');

    function getLocalIsoString() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        return now.toISOString().slice(0, 16);
    }

    startedAt.value = getLocalIsoString();

    const statuses = {
        'breakdown': 'Status saat ini: BREAKDOWN (Rusak) - Pilih Ready untuk mencabut breakdown saat perbaikan selesai.',
        'servis': 'Status saat ini: SERVIS (Perbaikan) - Pilih Ready saat unit sudah selesai diservis.',
        'ready': 'Status saat ini: READY (Operasional)',
    };

    select.addEventListener('change', function() {
        const s = this.options[this.selectedIndex]?.dataset.status || '';
        if (s === 'breakdown' || s === 'servis') {
            hint.textContent = statuses[s] || '';
            hint.className = 'mt-1.5 text-sm font-semibold text-amber-600';
            radioReady.checked = true;
            if (!endedAt.value) {
                endedAt.value = getLocalIsoString();
            }
        } else if (s === 'ready') {
            hint.textContent = statuses[s] || '';
            hint.className = 'mt-1.5 text-sm font-semibold text-green-600';
            radioBreakdown.checked = true;
        } else {
            hint.textContent = '';
        }
    });

    document.querySelectorAll('input[name="status"]').forEach(r => {
        r.addEventListener('change', function() {
            if (this.value === 'ready' && !endedAt.value) {
                endedAt.value = getLocalIsoString();
            }
        });
    });
});
</script>
@endpush
@endsection
