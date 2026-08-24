@props(['role' => 'admin'])

<aside class="sidebar">
    {{-- Logo Header - Height matched with topbar (h-20) --}}
    <div class="h-20 px-5 flex items-center border-b border-white/10">
        <a href="{{ route($role === 'pegawai' ? 'pegawai.dashboard' : "$role.rekapan.index") }}" class="flex items-center gap-3 group">
            <img src="{{ asset('images/company-logo.png') }}" alt="Logo" class="h-9 w-auto object-contain transition-transform group-hover:scale-105">
            <div class="flex flex-col">
                <span class="text-base font-bold text-white leading-tight tracking-wide group-hover:text-[var(--accent)] transition-colors">Surface Mine</span>
                <span class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase">Mining Operations</span>
            </div>
        </a>
    </div>

    {{-- Navigation Links --}}
    <nav class="py-5 px-3 flex-1 overflow-y-auto space-y-1">
        <div class="px-3 mb-2">
            <span class="text-[10px] font-bold text-slate-400/80 uppercase tracking-widest">Main Menu</span>
        </div>

        @if($role === 'admin')
            <a href="{{ route("$role.dashboard.index") }}"
               class="sidebar-nav-item {{ request()->routeIs("$role.dashboard.*") ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.rekapan.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.rekapan.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">receipt_long</span>
                <span>Rekapan Operator</span>
            </a>
            <a href="{{ route('admin.utilization.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.utilization.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">build</span>
                <span>Utilization</span>
            </a>
            <a href="{{ route('admin.master-data.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.master-data.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">storage</span>
                <span>Master Data</span>
            </a>
         @elseif($role === 'spv')
            <a href="{{ route("$role.dashboard.index") }}"
               class="sidebar-nav-item {{ request()->routeIs("$role.dashboard.*") ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('spv.rekapan.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('spv.rekapan.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">receipt_long</span>
                <span>Rekapan Operator</span>
            </a>
            <a href="{{ route('spv.utilization.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('spv.utilization.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">build</span>
                <span>Utilization</span>
            </a>
         @elseif($role === 'pegawai')
            <a href="{{ route('pegawai.ritasi.create') }}"
               class="sidebar-nav-item {{ request()->routeIs('pegawai.ritasi.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">local_shipping</span>
                <span>Unit Ritasi</span>
            </a>
            <a href="{{ route('pegawai.non-ritasi.create') }}"
               class="sidebar-nav-item {{ request()->routeIs('pegawai.non-ritasi.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">construction</span>
                <span>Unit Non Ritasi</span>
            </a>
            <a href="{{ route('pegawai.general.create') }}"
               class="sidebar-nav-item {{ request()->routeIs('pegawai.general.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">engineering</span>
                <span>Pekerjaan General</span>
            </a>
            <a href="{{ route('pegawai.utilization.create') }}"
               class="sidebar-nav-item {{ request()->routeIs('pegawai.utilization.*') ? 'active' : '' }}">
                <span class="material-symbols-outlined text-xl">build</span>
                <span>Utilization</span>
            </a>
        @endif
    </nav>

    {{-- Footer / Logout --}}
    <div class="p-4 border-t border-white/10 mt-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/30 hover:border-red-500 font-semibold text-sm rounded-lg transition-all duration-200">
                <span class="material-symbols-outlined text-lg">logout</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
