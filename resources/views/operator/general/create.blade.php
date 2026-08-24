@extends('layouts.app', ['headerTitle' => 'Form Pekerjaan General'])

@section('title', 'Form Pekerjaan General')

@section('content')

@include('operator.partials.validation-errors')

@include('operator.partials.session-info', ['description' => 'Silakan isi data pekerjaan general harian. Pastikan durasi Jam Kerja sesuai (6 - 11 Jam).'])

<form action="{{ route('pegawai.general.store') }}" method="POST" data-offline-form data-sync-tag="general-sync">
    @csrf
    
    <div class="card p-6">
        {{-- Data Dasar --}}
        @include('operator.partials.data-dasar', ['units' => $units, 'latestStatus' => $latestStatus, 'showSupervisor' => true])
        
        {{-- Jam Kerja --}}
        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">schedule</span>
            Jam Kerja
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="form-label">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="jam_mulai" class="form-input" required>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Jam mulai pengerjaan general.</p>
            </div>
            <div>
                <label class="form-label">Jam Akhir <span class="text-red-500">*</span></label>
                <input type="time" name="jam_selesai" class="form-input" required>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Jam selesai pengerjaan general.</p>
            </div>
            <div class="col-span-2">
                <div class="flex items-center gap-3 p-4 bg-amber-50/30 border border-amber-100 rounded-xl">
                    <span class="material-symbols-outlined text-amber-600">timer</span>
                    <div class="flex-1">
                        <p class="font-bold text-slate-800" style="color: var(--text);">Status Overtime</p>
                        <p class="text-xs sm:text-sm text-slate-500">Aktifkan jika pekerjaan melewati batas jam reguler</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_overtime" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--accent)]"></div>
                    </label>
                </div>
            </div>
        </div>
        
        {{-- Detail Pekerjaan --}}
        <h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
            <span class="material-symbols-outlined text-[var(--primary)]">work</span>
            Detail Pekerjaan
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="form-label">Lokasi Pekerjaan (Pit / Disposal)</label>
                <input type="text" name="lokasi_pekerjaan" class="form-input" placeholder="Contoh: Pit 1 North">
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Lokasi spesifik pengerjaan shift ini.</p>
            </div>
            <div class="col-span-2">
                <label class="form-label">Deskripsi Pekerjaan / Kendala (Opsional)</label>
                <textarea name="deskripsi_pekerjaan" class="form-input" rows="3" placeholder="Tambahkan catatan khusus bila ada kendala operasional..."></textarea>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Tuliskan detail pekerjaan general atau kendala operasional.</p>
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
@include('operator.partials.unit-status-script', ['withHmCalculator' => false])
@endpush
@endsection
