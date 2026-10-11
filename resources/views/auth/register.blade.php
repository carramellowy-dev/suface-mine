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
                <h1 class="font-heading text-xl font-bold text-[var(--primary)] tracking-wide uppercase">Daftar Akun</h1>
                <p class="text-xs text-[var(--text-secondary)] mt-1 font-medium">Buat akun operator baru</p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('register') }}" class="w-full space-y-5">
                @csrf

                {{-- Nama Lengkap --}}
                <div>
                    <label class="form-label text-xs">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <span class="material-symbols-outlined text-base">person</span>
                        </span>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Masukkan nama lengkap"
                               class="form-input pl-10 text-xs py-2.5"
                               required
                               autofocus>
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                {{-- Username --}}
                <div>
                    <label class="form-label text-xs">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <span class="material-symbols-outlined text-base">badge</span>
                        </span>
                        <input type="text"
                               name="username"
                               value="{{ old('username') }}"
                               placeholder="Masukkan username"
                               class="form-input pl-10 text-xs py-2.5"
                               required>
                    </div>
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>

                {{-- Password --}}
                <div>
                    <label class="form-label text-xs">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <span class="material-symbols-outlined text-base">lock</span>
                        </span>
                        <input type="password"
                               name="password"
                               placeholder="Minimal 8 karakter"
                               class="form-input pl-10 text-xs py-2.5"
                               required
                               autocomplete="new-password">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label class="form-label text-xs">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <span class="material-symbols-outlined text-base">lock</span>
                        </span>
                        <input type="password"
                               name="password_confirmation"
                               placeholder="Ulangi password"
                               class="form-input pl-10 text-xs py-2.5"
                               required
                               autocomplete="new-password">
                    </div>
                </div>

                {{-- Button --}}
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 text-xs py-2.5">
                        Daftar
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            </form>

            {{-- Back to login --}}
            <div class="mt-8 text-center w-full border-t border-slate-100 pt-4">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Sudah punya akun? Masuk
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
