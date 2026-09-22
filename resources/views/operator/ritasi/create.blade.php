@extends('layouts.app', ['headerTitle' => 'Form Input Unit Ritasi'])

@section('title', 'Form Input Unit Ritasi')

@section('content')

<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-slate-500">Form pelaporan ritasi pengangkutan material (Dump Truck).</p>
    <a href="{{ route('pegawai.ritasi.riwayat') }}" class="btn-secondary flex items-center gap-1.5 text-xs sm:text-sm py-1.5 px-3">
        <span class="material-symbols-outlined text-base">history</span>
        Lihat Riwayat
    </a>
</div>

@include('operator.partials.validation-errors')

@include('operator.partials.session-info', ['description' => 'Silakan isi data ritasi operasional harian. Pastikan durasi HM sesuai (6 - 11 Jam).'])

<form action="{{ route('pegawai.ritasi.store') }}" method="POST" data-offline-form data-sync-tag="ritasi-sync">
    @csrf
    
    <div class="card p-6">
        {{-- Data Dasar --}}
        @include('operator.partials.data-dasar', [
            'units' => $units,
            'latestStatus' => $latestStatus,
            'unitLabel' => 'Nomor Unit Dump Truck (Hauling)',
            'unitPlaceholder' => 'Pilih Unit Dump Truck'
        ])
        
        {{-- Hour Meter --}}
        @include('operator.partials.hour-meter')

        {{-- Fuel Consumption --}}
        @include('operator.partials.fuel-consumption')

        {{-- Rincian Muatan per Material (dinamis, 1 submit = N baris) --}}
        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">scale</span>
            Rincian Muatan per Material
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mb-4">Jika dalam 1 shift membawa ore/material berbeda, klik <strong>tanda (+) Tambah Baris</strong> untuk menambah kolom. Muatan dihitung otomatis dari beban unit yang tersimpan pada data unit.</p>
        {{-- Beban unit terisi otomatis dari data unit, tidak perlu ditampilkan/diisi operator --}}
        <input type="hidden" name="kapasitas_unit" id="kapasitasInput" value="">
        <input type="hidden" name="quantity_unit" value="m3">

        <div id="ritasiRows" class="space-y-3 mb-4">
            @php
                $oldMat = old('material_id', [null]);
                $oldHm = old('hm_mulai_kerja', []);
                $oldRit = old('jumlah_ritasi', []);
                $oldArea = old('area_id', []);
            @endphp
            @foreach($oldMat as $i => $v)
            <div class="ritasi-row grid grid-cols-2 md:grid-cols-12 gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                <div class="col-span-1 md:col-span-3">
                    <label class="form-label">HM Mulai Kerja <span class="text-red-500">*</span></label>
                    <input type="number" name="hm_mulai_kerja[]" class="form-input hm-mulai" step="0.1" min="0" value="{{ $oldHm[$i] ?? '' }}" required placeholder="1250.2">
                </div>
                <div class="col-span-1 md:col-span-4">
                    <label class="form-label">Material <span class="text-red-500">*</span></label>
                    <select name="material_id[]" class="form-input" required>
                        <option value="">Pilih Material</option>
                        @foreach($materials as $id => $nama)
                            <option value="{{ $id }}" {{ (string)($v ?? '') === (string)$id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="form-label">Ritasi (Trip) <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_ritasi[]" class="form-input row-ritasi" min="0" value="{{ $oldRit[$i] ?? 0 }}" required placeholder="15">
                    <p class="row-qty-auto mt-1 text-xs font-semibold text-[var(--primary)]">≈ 0.00 M3</p>
                    <input type="hidden" name="quantity[]" class="row-qty" value="0">
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="form-label">Area <span class="text-red-500">*</span></label>
                    <select name="area_id[]" class="form-input" required>
                        <option value="">Pilih Area</option>
                        @foreach($areas as $id => $nama)
                            <option value="{{ $id }}" {{ (string)($oldArea[$i] ?? '') === (string)$id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2 md:col-span-1 flex items-end">
                    <button type="button" class="btn-secondary text-xs px-2 py-1.5 remove-row" title="Hapus baris">Hapus</button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-3 mb-6">
            <button type="button" id="addRitasiRow" class="btn-secondary flex items-center gap-1.5 text-sm py-1.5 px-3">
                <span class="material-symbols-outlined text-base">add</span>
                Tambah Baris (+)
            </button>
            <div class="ml-auto flex items-center gap-4 text-sm">
                <span class="text-slate-500">Total Ritasi: <strong id="grandRitasi" class="text-[var(--primary)]">0</strong></span>
                <span class="text-slate-500">Total Muatan: <strong id="grandQty" class="text-[var(--primary)]">0.00 M3</strong></span>
            </div>
        </div>

        <template id="ritasiRowTemplate">
            <div class="ritasi-row grid grid-cols-2 md:grid-cols-12 gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                <div class="col-span-1 md:col-span-3">
                    <label class="form-label">HM Mulai Kerja <span class="text-red-500">*</span></label>
                    <input type="number" name="hm_mulai_kerja[]" class="form-input hm-mulai" step="0.1" min="0" value="" required placeholder="1250.2">
                </div>
                <div class="col-span-1 md:col-span-4">
                    <label class="form-label">Material <span class="text-red-500">*</span></label>
                    <select name="material_id[]" class="form-input" required>
                        <option value="">Pilih Material</option>
                        @foreach($materials as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="form-label">Ritasi (Trip) <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_ritasi[]" class="form-input row-ritasi" min="0" value="0" required placeholder="15">
                    <p class="row-qty-auto mt-1 text-xs font-semibold text-[var(--primary)]">≈ 0.00 M3</p>
                    <input type="hidden" name="quantity[]" class="row-qty" value="0">
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="form-label">Area <span class="text-red-500">*</span></label>
                    <select name="area_id[]" class="form-input" required>
                        <option value="">Pilih Area</option>
                        @foreach($areas as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2 md:col-span-1 flex items-end">
                    <button type="button" class="btn-secondary text-xs px-2 py-1.5 remove-row" title="Hapus baris">Hapus</button>
                </div>
            </div>
        </template>

        {{-- Detail Pekerjaan --}}
        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">work</span>
            Detail Pekerjaan
        </h2>
        <div class="grid grid-cols-1 gap-6 mb-6">
            <div>
                <label class="form-label">Deskripsi Pekerjaan / Kendala (Opsional)</label>
                <textarea name="deskripsi_pekerjaan" class="form-input" rows="3" placeholder="Tambahkan catatan khusus bila ada kendala operasional (antrean crusher, jalan licin, dll)..."></textarea>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Tuliskan jika ada kendala mesin, cuaca/hujan, antrean, atau catatan penting lainnya.</p>
            </div>
        </div>

        <p id="hmAkhirWarning" class="hidden mb-4 text-sm font-semibold text-red-600">Harap diisi HM akhir terlebih dahulu.</p>

        {{-- Buttons --}}
        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="reset" class="btn-secondary">Reset</button>
            <button type="submit" id="btnKirimRitasi" class="btn-primary flex items-center gap-2">
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
    const rowsWrap = document.getElementById('ritasiRows');
    const template = document.getElementById('ritasiRowTemplate');
    const grandRitasi = document.getElementById('grandRitasi');
    const grandQty = document.getElementById('grandQty');
    const hmAwal = document.getElementById('hmAwal');
    const hmAkhir = document.getElementById('hmAkhir');
    const warning = document.getElementById('hmAkhirWarning');
    const unitSelect = document.getElementById('unitSelect');
    const kapasitasInput = document.getElementById('kapasitasInput');
    const kapasitasMap = {!! json_encode($unitKapasitas ?? []) !!};

    function unitKapasitas() {
        if (kapasitasInput && kapasitasInput.value !== '') {
            return parseFloat(kapasitasInput.value) || 0;
        }
        if (typeof kapasitasMap !== 'undefined' && unitSelect && unitSelect.value) {
            return parseFloat(kapasitasMap[unitSelect.value]) || 0;
        }
        return 0;
    }

    function updateKapasitasInfo() {
        if (!unitSelect || !unitSelect.value) {
            if (kapasitasInput) kapasitasInput.value = '';
            return;
        }
        const k = parseFloat(kapasitasMap[unitSelect.value]) || 0;
        // Beban unit terisi otomatis dari data unit (hidden, tidak ditampilkan)
        if (kapasitasInput) kapasitasInput.value = k > 0 ? k : '';
        recalc();
    }

    function recalc() {
        const k = unitKapasitas();
        let r = 0, q = 0;
        rowsWrap.querySelectorAll('.ritasi-row').forEach(function(row) {
            const trips = parseInt(row.querySelector('.row-ritasi').value) || 0;
            const qty = trips * k;
            r += trips;
            q += qty;
            row.querySelector('.row-qty').value = qty.toFixed(2);
            row.querySelector('.row-qty-auto').textContent = '≈ ' + qty.toFixed(2) + ' M3';
        });
        grandRitasi.textContent = r;
        grandQty.textContent = q.toFixed(2) + ' M3';
    }

    if (unitSelect) unitSelect.addEventListener('change', updateKapasitasInfo);
    if (kapasitasInput) kapasitasInput.addEventListener('input', recalc);
    updateKapasitasInfo();

    document.getElementById('addRitasiRow').addEventListener('click', function() {
        rowsWrap.appendChild(template.content.cloneNode(true));
        recalc();
    });

    rowsWrap.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            if (rowsWrap.querySelectorAll('.ritasi-row').length > 1) {
                e.target.closest('.ritasi-row').remove();
                recalc();
            }
        }
    });

    rowsWrap.addEventListener('input', recalc);
    recalc();

    document.querySelector('form[data-sync-tag="ritasi-sync"]').addEventListener('submit', function(e) {
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
        const lastStart = prev;
        if (akhir < lastStart) {
            e.preventDefault();
            alert('HM akhir tidak boleh lebih kecil dari HM mulai kerja terakhir');
        }
    });
});
</script>
@endpush
@endsection
