@extends('layouts.public')

@section('title', 'Sesi Berakhir')

@section('content')
<div class="min-h-screen flex items-center justify-center px-6">
    <div class="card max-w-md w-full text-center space-y-4">
        <p class="text-5xl font-bold text-[var(--primary)]">419</p>
        <h1 class="font-heading text-xl font-bold">Sesi berakhir</h1>
        <p class="text-sm text-slate-500">Halaman sudah kedaluwarsa (mis. tombol ditekan dua kali atau halaman terbuka terlalu lama). Data Anda tidak hilang — silakan muat ulang lalu kirim kembali.</p>
        <div class="flex justify-center gap-3 pt-2">
            <button onclick="window.location.reload()" class="btn-primary">Muat Ulang Halaman</button>
            <a href="{{ route('login') }}" class="btn-secondary">Ke Login</a>
        </div>
    </div>
</div>
@endsection
