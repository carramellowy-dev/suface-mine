@extends('layouts.app', ['headerTitle' => 'Form Input Unit Non Ritasi'])

@section('title', 'Form Input Unit Non Ritasi')

@section('content')

@include('operator.partials.validation-errors')

@include('operator.partials.session-info', ['description' => 'Silakan isi data ritasi operasional harian. Pastikan durasi HM sesuai (6 - 11 Jam).'])

<form action="{{ route('pegawai.non-ritasi.store') }}" method="POST" data-offline-form data-sync-tag="non-ritasi-sync">
    @csrf
    
    <div class="card p-6">
        {{-- Data Dasar --}}
        @include('operator.partials.data-dasar', ['units' => $units, 'latestStatus' => $latestStatus])
        
        {{-- Hour Meter --}}
        @include('operator.partials.hour-meter')
        
        {{-- Fuel Consumption --}}
        @include('operator.partials.fuel-consumption')
        
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
            <div class="col-span-2">
                <label class="form-label">Deskripsi Pekerjaan / Kendala (Opsional)</label>
                <textarea name="deskripsi_pekerjaan" class="form-input" rows="3" placeholder="Tambahkan catatan khusus bila ada kendala operasional..."></textarea>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Tuliskan detail pekerjaan non-ritasi (seperti standby, cleaning, loading) atau kendala operasional.</p>
            </div>
        </div>
        
        <input type="hidden" name="area_id" value="{{ $areas[array_key_first($areas)] ?? 1 }}">
        
        {{-- Buttons --}}
        <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="reset" class="btn-secondary">Reset</button>
            <button type="submit" class="btn-primary flex items-center gap-2">
                <span class="material-symbols-outlined">save</span>
                Simpan Data Ritasi
            </button>
        </div>
    </div>
</form>

@push('scripts')
@include('operator.partials.unit-status-script', ['withHmCalculator' => true])
@endpush
@endsection
