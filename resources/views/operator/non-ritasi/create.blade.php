@extends('layouts.app', ['headerTitle' => 'Form Input Unit Non Ritasi'])

@section('title', 'Form Input Unit Non Ritasi')

@section('content')

<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-slate-500">Form pelaporan operasional unit alat berat non-ritasi (Excavator, Dozer, Grader, Loader).</p>
    <a href="{{ route('pegawai.non-ritasi.riwayat') }}" class="btn-secondary flex items-center gap-1.5 text-xs sm:text-sm py-1.5 px-3">
        <span class="material-symbols-outlined text-base">history</span>
        Lihat Riwayat
    </a>
</div>

@include('operator.partials.validation-errors')

@include('operator.partials.session-info', ['description' => 'Silakan isi data operasional alat berat non-ritasi harian. Pastikan durasi HM sesuai (6 - 11 Jam).'])

<form action="{{ route('pegawai.non-ritasi.store') }}" method="POST" data-offline-form data-sync-tag="non-ritasi-sync">
    @csrf
    
    <div class="card p-6">
        {{-- Data Dasar --}}
        @include('operator.partials.data-dasar', ['units' => $units, 'latestStatus' => $latestStatus, 'unitLabel' => 'Nomor Unit (Excavator / Dozer / Grader / Loader)'])
        
        {{-- Hour Meter --}}
        @include('operator.partials.hour-meter')
        
        {{-- Fuel Consumption --}}
        @include('operator.partials.fuel-consumption')
        
        {{-- Rincian Area Kerja (dinamis, 1 submit = N baris) --}}
        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">work</span>
            Rincian Area Kerja
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mb-4">Jika dalam 1 shift mengerjakan beberapa area, klik <strong>tanda (+) Tambah Baris</strong> untuk menambah kolom.</p>

        <div id="nonRitasiRows" class="space-y-3 mb-4">
            @php
                $oldArea = old('area_id', [null]);
                $oldHm = old('hm_mulai_kerja', []);
            @endphp
            @foreach($oldArea as $i => $v)
            <div class="nonritasi-row grid grid-cols-1 md:grid-cols-12 gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                <div class="md:col-span-4">
                    <label class="form-label">HM Mulai Kerja <span class="text-red-500">*</span></label>
                    <input type="number" name="hm_mulai_kerja[]" class="form-input hm-mulai" step="0.1" min="0" value="{{ $oldHm[$i] ?? '' }}" required placeholder="1250.2">
                </div>
                <div class="md:col-span-7">
                    <label class="form-label">Area Kerja <span class="text-red-500">*</span></label>
                    <select name="area_id[]" class="form-input" required>
                        <option value="">Pilih Area Kerja</option>
                        @foreach($areas as $id => $nama)
                            <option value="{{ $id }}" {{ (string)($v ?? '') === (string)$id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="btn-secondary text-xs px-2 py-1.5 remove-row" title="Hapus baris">Hapus</button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-3 mb-6">
            <button type="button" id="addNonRitasiRow" class="btn-secondary flex items-center gap-1.5 text-sm py-1.5 px-3">
                <span class="material-symbols-outlined text-base">add</span>
                Tambah Baris (+)
            </button>
        </div>

        <template id="nonRitasiRowTemplate">
            <div class="nonritasi-row grid grid-cols-1 md:grid-cols-12 gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                <div class="md:col-span-4">
                    <label class="form-label">HM Mulai Kerja <span class="text-red-500">*</span></label>
                    <input type="number" name="hm_mulai_kerja[]" class="form-input hm-mulai" step="0.1" min="0" value="" required placeholder="1250.2">
                </div>
                <div class="md:col-span-7">
                    <label class="form-label">Area Kerja <span class="text-red-500">*</span></label>
                    <select name="area_id[]" class="form-input" required>
                        <option value="">Pilih Area Kerja</option>
                        @foreach($areas as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="btn-secondary text-xs px-2 py-1.5 remove-row" title="Hapus baris">Hapus</button>
                </div>
            </div>
        </template>

        {{-- Detail Pekerjaan --}}
        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">work</span>
            Detail Pekerjaan
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="form-label">Lokasi Pekerjaan (Pit / Disposal)</label>
                <input type="text" name="lokasi_pekerjaan" class="form-input" placeholder="Contoh: Pit 1 North">
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Lokasi spesifik pengerjaan non-ritasi shift ini.</p>
            </div>
            <div>
                <label class="form-label">Deskripsi Pekerjaan / Kendala (Opsional)</label>
                <textarea name="deskripsi_pekerjaan" class="form-input" rows="3" placeholder="Tambahkan catatan khusus bila ada kendala operasional..."></textarea>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Tuliskan detail pekerjaan non-ritasi (seperti standby, cleaning, loading) atau kendala operasional.</p>
            </div>
        </div>

        <p id="hmAkhirWarning" class="hidden mb-4 text-sm font-semibold text-red-600">Harap diisi HM akhir terlebih dahulu.</p>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="reset" class="btn-secondary">Reset</button>
            <button type="submit" id="btnKirimNonRitasi" class="btn-primary flex items-center gap-2">
                <span class="material-symbols-outlined">save</span>
                Kirim
            </button>
        </div>
    </div>
</form>

@push('scripts')
@include('operator.partials.unit-status-script', ['withHmCalculator' => true])
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rowsWrap = document.getElementById('nonRitasiRows');
    const template = document.getElementById('nonRitasiRowTemplate');
    const hmAwal = document.getElementById('hmAwal');
    const hmAkhir = document.getElementById('hmAkhir');
    const warning = document.getElementById('hmAkhirWarning');

    document.getElementById('addNonRitasiRow').addEventListener('click', function() {
        rowsWrap.appendChild(template.content.cloneNode(true));
    });

    rowsWrap.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            if (rowsWrap.querySelectorAll('.nonritasi-row').length > 1) {
                e.target.closest('.nonritasi-row').remove();
            }
        }
    });

    document.querySelector('form[data-sync-tag="non-ritasi-sync"]').addEventListener('submit', function(e) {
        const awal = parseFloat(hmAwal.value);
        const akhir = parseFloat(hmAkhir.value);
        if (isNaN(akhir) || hmAkhir.value === '' || akhir <= 0) {
            e.preventDefault();
            warning.classList.remove('hidden');
            hmAkhir.focus();
            alert('Harap diisi HM akhir');
            return;
        }
        warning.classList.add('hidden');
        if (!isNaN(awal) && akhir < awal) {
            e.preventDefault();
            alert('HM akhir tidak boleh lebih kecil dari HM awal');
            return;
        }
        let prev = isNaN(awal) ? 0 : awal;
        let ok = true;
        rowsWrap.querySelectorAll('.hm-mulai').forEach(function(input) {
            const v = parseFloat(input.value);
            if (isNaN(v) || v < prev) ok = false;
            if (!isNaN(v) && v > prev) prev = v;
        });
        if (!ok) {
            e.preventDefault();
            alert('HM mulai kerja tiap baris harus berurutan dan tidak kurang dari HM awal');
            return;
        }
        if (akhir < prev) {
            e.preventDefault();
            alert('HM akhir tidak boleh lebih kecil dari HM mulai kerja terakhir');
        }
    });
});
</script>
@endpush
@endsection
