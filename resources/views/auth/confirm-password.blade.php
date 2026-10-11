<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-[#0f1d36] to-[#08101d] px-4 py-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-900/20 rounded-full blur-3xl opacity-50 -translate-y-1/3 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl opacity-50 translate-y-1/3 -translate-x-1/3"></div>

        <div class="fade-in w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-100 p-8 flex flex-col items-center relative z-10">
            <div class="mb-6">
                <img src="{{ asset('images/company-logo.png') }}" alt="Company Logo" class="h-28 w-auto object-contain">
            </div>

            <div class="text-center mb-8">
                <h1 class="font-heading text-xl font-bold text-[var(--primary)] tracking-wide uppercase">Konfirmasi Sandi</h1>
                <p class="text-xs text-[var(--text-secondary)] mt-1 font-medium">Area aman — masukkan sandi untuk melanjutkan</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="w-full space-y-5">
                @csrf

                <div>
                    <label class="form-label text-xs">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <span class="material-symbols-outlined text-base">lock</span>
                        </span>
                        <input type="password"
                               name="password"
                               placeholder="Masukkan password"
                               class="form-input pl-10 text-xs py-2.5"
                               required
                               autofocus
                               autocomplete="current-password">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 text-xs py-2.5">
                        Konfirmasi
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
