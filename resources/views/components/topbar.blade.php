@props(['role' => 'admin', 'headerTitle' => '', 'headerDate' => ''])

<header class="topbar flex items-center justify-between">
    <div class="flex items-center gap-2">
        <button id="sidebar-toggle" class="md:hidden p-2 rounded-lg hover:bg-slate-100 transition-colors duration-200">
            <span class="material-symbols-outlined text-slate-600">menu</span>
        </button>
        @if($headerTitle)
            <h1 class="text-lg sm:text-xl font-heading font-bold text-[var(--primary)] hidden sm:block">{{ $headerTitle }}</h1>
        @endif
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        @if($headerDate)
            <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200">
                <span class="material-symbols-outlined text-slate-500 text-base">calendar_today</span>
                <span class="text-sm font-medium text-slate-700">{{ $headerDate }}</span>
            </div>
        @endif
        {{-- User Badge --}}
        <div class="flex items-center gap-2 px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200">
            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#2d5a8a] to-[#15294a] flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xs">person</span>
            </div>
            <span class="text-sm font-medium text-slate-700 hidden sm:inline">{{ Auth::user()->name ?? ucfirst($role) }}</span>
        </div>
    </div>
</header>

<script>
    document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        if (sidebar && toggle && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
</script>
