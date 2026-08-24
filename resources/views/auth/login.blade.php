<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-[#0f1d36] to-[#08101d] px-4 py-12 relative overflow-hidden">
        {{-- Decorative background glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-900/20 rounded-full blur-3xl opacity-50 -translate-y-1/3 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl opacity-50 translate-y-1/3 -translate-x-1/3"></div>

        <div class="fade-in w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-100 p-8 flex flex-col items-center relative z-10">
            {{-- Logo --}}
            <div class="mb-6">
                <img src="{{ asset('images/company-logo.png') }}" alt="Company Logo" class="h-28 w-auto object-contain">
            </div>

            {{-- Title --}}
            <div class="text-center mb-8">
                <h1 class="font-heading text-xl font-bold text-[var(--primary)] tracking-wide uppercase">Surface Mine</h1>
                <p class="text-xs text-[var(--text-secondary)] mt-1 font-medium">Civil Department Operational Record</p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="w-full space-y-5">
                @csrf

                {{-- Username / Email --}}
                <div>
                    <label class="form-label text-xs">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <span class="material-symbols-outlined text-base">person</span>
                        </span>
                        <input type="text"
                               name="login"
                               value="{{ old('login') }}"
                               placeholder="Masukkan username"
                               class="form-input pl-10 text-xs py-2.5"
                               required
                               autofocus>
                    </div>
                    <x-input-error :messages="$errors->get('login')" class="mt-1" />
                </div>

                {{-- Password / ID Pekerja --}}
                <div>
                    <label class="form-label text-xs">ID Pekerja (Password)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <span class="material-symbols-outlined text-base">lock</span>
                        </span>
                        <input type="password"
                               name="password"
                               placeholder="Masukkan ID Pekerja"
                               class="form-input pl-10 text-xs py-2.5"
                               required
                               autocomplete="current-password">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                {{-- Error message --}}
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-600 px-3.5 py-2.5 rounded-lg text-xs flex items-start gap-2">
                        <span class="material-symbols-outlined text-sm mt-0.5 text-red-500">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Button --}}
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 text-xs py-2.5">
                        Masuk Ke Dashboard
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            </form>

            {{-- Back button to landing --}}
            <div class="mt-8 text-center w-full border-t border-slate-100 pt-4">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
