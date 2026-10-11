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

                {{-- Lupa sandi --}}
                <div class="text-center pt-1">
                    <button type="button" id="btn-forgot-password"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-[var(--primary-light)] hover:text-[var(--accent)] hover:underline transition-colors">
                        <span class="material-symbols-outlined text-sm ms-symbols-new">lock_open</span>
                        Lupa sandi?
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

    {{-- Popup Lupa Sandi : Hubungi Admin via WhatsApp --}}
    <div id="forgot-password-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        {{-- Backdrop --}}
        <div id="forgot-password-backdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

        {{-- Card --}}
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-100 p-6 fade-in">
            <button type="button" id="btn-forgot-close"
                class="absolute top-3 right-3 w-8 h-8 inline-flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                aria-label="Tutup">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>

            <div class="flex items-start gap-3 mb-4">
                <div class="w-11 h-11 shrink-0 rounded-xl bg-green-50 border border-green-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-green-600 ms-symbols-new">support_agent</span>
                </div>
                <div>
                    <h2 class="font-heading text-base font-bold text-[var(--primary)]">Lupa Sandi?</h2>
                    <p class="text-xs text-[var(--text-secondary)] mt-0.5 leading-relaxed">
                        Hubungi admin untuk reset password. Isi ID &amp; nama, lalu kirim via WhatsApp.
                    </p>
                </div>
            </div>

            {{-- Nomor admin --}}
            <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 mb-4">
                <span class="material-symbols-outlined text-lg text-slate-500 ms-symbols-new">call</span>
                <div class="flex-1">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Admin Surface Mine</p>
                    <p class="text-sm font-bold text-[var(--primary)]">+62 822-9176-3778</p>
                </div>
            </div>

            {{-- Form identitas untuk template WA --}}
            <div class="space-y-3">
                <div>
                    <label for="fp-username" class="form-label text-xs">ID Pekerja / Username</label>
                    <input type="text" id="fp-username" value="{{ old('login') }}"
                        placeholder="cth: 123456 / budi.santoso"
                        class="form-input text-xs py-2.5" autocomplete="username">
                </div>
                <div>
                    <label for="fp-nama" class="form-label text-xs">Nama Lengkap</label>
                    <input type="text" id="fp-nama"
                        placeholder="cth: Budi Santoso"
                        class="form-input text-xs py-2.5" autocomplete="name">
                </div>

                {{-- Preview template --}}
                <div>
                    <p class="form-label text-xs">Pratinjau pesan:</p>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5">
                        <p id="fp-preview" class="text-xs text-slate-600 whitespace-pre-line leading-relaxed"></p>
                    </div>
                </div>
                <p id="fp-error" class="hidden text-xs text-red-600 font-medium"></p>
            </div>

            {{-- Aksi --}}
            <div class="mt-5 space-y-2.5">
                <a id="fp-wa-link" href="https://wa.me/6282291763778" target="_blank" rel="noopener"
                    class="w-full inline-flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#1ebe5b] text-white font-bold px-6 py-2.5 rounded-lg text-xs transition-all shadow-[0_2px_4px_rgba(37,211,102,0.25)] hover:-translate-y-px">
                    <span class="material-symbols-outlined text-base ms-symbols-new">chat</span>
                    Hubungi Admin via WhatsApp
                </a>
                <div class="flex gap-2.5">
                    <button type="button" id="btn-fp-copy"
                        class="btn-secondary flex-1 inline-flex items-center justify-center gap-1.5 text-xs py-2.5">
                        <span class="material-symbols-outlined text-sm ms-symbols-new">content_copy</span>
                        Salin Pesan
                    </button>
                    <button type="button" id="btn-fp-cancel"
                        class="btn-secondary flex-1 inline-flex items-center justify-center gap-1.5 text-xs py-2.5">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var ADMIN_WA = '6282291763778';
        var modal = document.getElementById('forgot-password-modal');
        var btnOpen = document.getElementById('btn-forgot-password');
        var btnClose = document.getElementById('btn-forgot-close');
        var btnCancel = document.getElementById('btn-fp-cancel');
        var backdrop = document.getElementById('forgot-password-backdrop');
        var inputUser = document.getElementById('fp-username');
        var inputNama = document.getElementById('fp-nama');
        var waLink = document.getElementById('fp-wa-link');
        var preview = document.getElementById('fp-preview');
        var errBox = document.getElementById('fp-error');
        var btnCopy = document.getElementById('btn-fp-copy');
        var loginInput = document.querySelector('input[name="login"]');

        function buildMessage() {
            var id = (inputUser.value || '').trim() || '-';
            var nama = (inputNama.value || '').trim() || '-';
            return 'Halo Admin Surface Mine,\n'
                + 'Saya lupa sandi / tidak bisa login. Mohon bantuan reset password.\n\n'
                + 'ID Pekerja / Username: ' + id + '\n'
                + 'Nama: ' + nama + '\n\n'
                + 'Terima kasih.';
        }

        function refresh() {
            var msg = buildMessage();
            preview.textContent = msg;
            waLink.href = 'https://wa.me/' + ADMIN_WA + '?text=' + encodeURIComponent(msg);
            errBox.classList.add('hidden');
        }

        function openModal() {
            if (loginInput && loginInput.value && !inputUser.value) {
                inputUser.value = loginInput.value;
            }
            refresh();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { inputUser.focus(); }, 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            if (btnOpen) btnOpen.focus();
        }

        btnOpen.addEventListener('click', openModal);
        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
        inputUser.addEventListener('input', refresh);
        inputNama.addEventListener('input', refresh);

        waLink.addEventListener('click', function (e) {
            var id = (inputUser.value || '').trim();
            var nama = (inputNama.value || '').trim();
            if (!id || !nama) {
                e.preventDefault();
                errBox.textContent = 'Mohon isi ID Pekerja / Username dan Nama Lengkap dulu agar admin mudah memproses reset password.';
                errBox.classList.remove('hidden');
                (!id ? inputUser : inputNama).focus();
            }
        });

        btnCopy.addEventListener('click', function () {
            var msg = buildMessage();
            function done() {
                btnCopy.innerHTML = '<span class="material-symbols-outlined text-sm ms-symbols-new">check</span> Tersalin!';
                setTimeout(function () {
                    btnCopy.innerHTML = '<span class="material-symbols-outlined text-sm ms-symbols-new">content_copy</span> Salin Pesan';
                }, 1800);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(msg).then(done).catch(function () { fallbackCopy(msg); done(); });
            } else {
                fallbackCopy(msg); done();
            }
        });

        function fallbackCopy(text) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch (err) {}
            document.body.removeChild(ta);
        }

        refresh();
    })();
    </script>
</x-guest-layout>
