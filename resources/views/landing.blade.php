@extends('layouts.public')

@section('title', 'Surface Mine Production Operational Record')

@section('content')
{{-- Hero Section --}}
<div class="relative overflow-hidden bg-gradient-to-br from-[#0f1d36] to-[#08101d] min-h-screen flex items-center">
    {{-- Decorative Background Glows --}}
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-900 rounded-full blur-3xl opacity-30 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-10 w-96 h-96 bg-amber-500 rounded-full blur-3xl opacity-10 translate-y-1/4"></div>

    <div class="max-w-7xl mx-auto px-6 w-full py-16 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center relative z-10">
        {{-- Left side - Content --}}
        <div class="fade-in space-y-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 text-slate-200 border border-slate-700/50 text-xs font-semibold tracking-wide uppercase">
                <span class="material-symbols-outlined text-sm text-amber-500">verified</span>
                Civil Department System
            </div>
            
            <h1 class="font-heading text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight tracking-tight text-white">
                Surface Mine <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-200">
                    Production Record
                </span>
            </h1>
            
            <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-lg">
                Pencatatan operational tambang harian yang tersentralisasi untuk Civil Department. Memantau ritasi, utilitas unit, dan kendala operasional secara real-time.
            </p>
            
            <div class="flex flex-wrap gap-4 pt-2">
                <a href="{{ route('login') }}" class="btn-primary inline-flex items-center justify-center px-8 py-3.5 text-base shadow-lg shadow-amber-500/20">
                    LOGIN
                </a>
            </div>
            
            {{-- Quick Stats Row --}}
            <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-800 max-w-md">
                <div>
                    <p class="text-2xl font-bold text-white">99.8%</p>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Sync Online</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">Real-time</p>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Reporting</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">PWA</p>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Offline First</p>
                </div>
            </div>
        </div>

        {{-- Right side - Image & Visual Elements --}}
        <div class="relative flex justify-center items-center lg:h-[500px]">
            {{-- Main Hero Image Container --}}
            <div class="relative w-full max-w-md lg:max-w-none h-[380px] sm:h-[420px] rounded-2xl overflow-hidden shadow-2xl border-4 border-slate-800/80 bg-slate-900">
                <img src="{{ asset('images/worker-hero.jpg') }}" alt="Mining Worker" class="w-full h-full object-cover object-center">
                {{-- Gradient Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-[#08101d]/85 via-transparent to-transparent"></div>
            </div>

            {{-- Floating Glass Cards --}}
            <div class="absolute -left-6 top-1/4 backdrop-blur-md bg-[#0f1d36]/80 border border-slate-700/50 p-4 rounded-xl shadow-lg flex items-center gap-3 animate-[pulse_3s_infinite] hidden sm:flex">
                <div class="h-10 w-10 rounded-lg bg-green-500/20 flex items-center justify-center text-green-400">
                    <span class="material-symbols-outlined">wifi</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-white">Status Sistem</p>
                    <p class="text-2xs text-green-400 font-medium flex items-center gap-1">
                        Terhubung & Aktif
                    </p>
                </div>
            </div>

            <div class="absolute -right-4 bottom-1/4 backdrop-blur-md bg-[#0f1d36]/85 border border-slate-700/50 p-4 rounded-xl shadow-lg flex items-center gap-3 hidden sm:flex">
                <div class="h-10 w-10 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400">
                    <span class="material-symbols-outlined">electric_bolt</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-white">Offline-Ready</p>
                    <p class="text-2xs text-slate-400 font-medium">Input data tanpa sinyal</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Features Grid Section --}}
<div class="bg-[#08101d] py-24 border-t border-slate-900">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-xl mx-auto mb-16 space-y-4">
            <h2 class="font-heading text-3xl font-bold text-white">Efisiensi Operasional Terjamin</h2>
            <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                Platform dirancang khusus untuk mempercepat pencatatan dan pelaporan produksi tambang dengan arsitektur modern.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Feature Card 1 --}}
            <div class="bg-[#0f1d36]/20 hover:bg-[#0f1d36]/50 rounded-xl border border-slate-900 hover:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="h-12 w-12 rounded-lg bg-blue-500/10 group-hover:bg-blue-600 text-blue-400 group-hover:text-white flex items-center justify-center transition-all duration-300 mb-6">
                    <span class="material-symbols-outlined text-2xl">monitoring</span>
                </div>
                <h3 class="text-base font-bold text-white mb-2">Real-time Monitoring</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Lihat ritasi harian, progres pencapaian target, dan performa operator secara langsung dari dashboard terintegrasi.
                </p>
            </div>

            {{-- Feature Card 2 --}}
            <div class="bg-[#0f1d36]/20 hover:bg-[#0f1d36]/50 rounded-xl border border-slate-900 hover:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="h-12 w-12 rounded-lg bg-amber-500/10 group-hover:bg-amber-500 text-amber-400 group-hover:text-white flex items-center justify-center transition-all duration-300 mb-6">
                    <span class="material-symbols-outlined text-2xl">cloud_off</span>
                </div>
                <h3 class="text-base font-bold text-white mb-2">Offline-First Sync</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Simpan data di IndexedDB lokal saat di area tambang tanpa sinyal. Otomatis sinkronisasi saat kembali online.
                </p>
            </div>

            {{-- Feature Card 3 --}}
            <div class="bg-[#0f1d36]/20 hover:bg-[#0f1d36]/50 rounded-xl border border-slate-900 hover:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="h-12 w-12 rounded-lg bg-purple-500/10 group-hover:bg-purple-600 text-purple-400 group-hover:text-white flex items-center justify-center transition-all duration-300 mb-6">
                    <span class="material-symbols-outlined text-2xl">engineering</span>
                </div>
                <h3 class="text-base font-bold text-white mb-2">Utilization Tracking</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Catat breakdown, servis, dan status standby unit DT (Dump Truck) guna meminimalkan downtime alat berat.
                </p>
            </div>

            {{-- Feature Card 4 --}}
            <div class="bg-[#0f1d36]/20 hover:bg-[#0f1d36]/50 rounded-xl border border-slate-900 hover:border-slate-800 p-6 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="h-12 w-12 rounded-lg bg-emerald-500/10 group-hover:bg-emerald-600 text-emerald-400 group-hover:text-white flex items-center justify-center transition-all duration-300 mb-6">
                    <span class="material-symbols-outlined text-2xl">description</span>
                </div>
                <h3 class="text-base font-bold text-white mb-2">Export Data Laporan</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Ekspor seluruh data harian, mingguan, maupun bulanan ke dalam format Excel dan PDF siap cetak kapan saja.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
