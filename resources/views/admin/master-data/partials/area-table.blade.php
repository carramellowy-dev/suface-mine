<div class="card">
    {{-- Search and Filter --}}
    <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
                    <span class="material-symbols-outlined text-lg">search</span>
                </span>
                <form method="GET" action="{{ route('admin.master-data.index') }}">
                    <input type="hidden" name="tab" value="area">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari data..."
                           class="form-input pl-10 w-64">
                </form>
            </div>
        </div>
        <button class="btn-secondary flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">filter_list</span>
            Filter
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NO</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NAMA AREA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">KODE AREA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">PENANGGUNG JAWAB</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">STATUS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse($areas as $index => $area)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-[var(--text)]">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-[var(--text)]">{{ $area->nama }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">{{ $area->kode }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">
                            {{ $area->spvs->first()->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $status = $area->status ?? 'active';
                                $statusConfig = match($status) {
                                    'active' => ['label' => 'Aktif', 'class' => 'badge-active', 'dot' => 'bg-green-500'],
                                    'cuti' => ['label' => 'Cuti', 'class' => 'badge-maintenance', 'dot' => 'bg-yellow-500'],
                                    'non-aktif' => ['label' => 'Non-Aktif', 'class' => 'badge-inactive', 'dot' => 'bg-gray-400'],
                                    default => ['label' => 'Aktif', 'class' => 'badge-active', 'dot' => 'bg-green-500'],
                                };
                            @endphp
                            <span class="badge {{ $statusConfig['class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button class="text-[var(--primary)] hover:opacity-75" onclick="editArea({{ $area->id }})">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <button class="text-red-600 hover:text-red-700" onclick="deleteArea({{ $area->id }})">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-[var(--text-muted)]">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="p-4 border-t border-[var(--border)] flex items-center justify-between">
        <p class="text-sm text-[var(--text-muted)]">
            Showing {{ $areas->firstItem() }} to {{ $areas->lastItem() }} of {{ $areas->total() }} entries
        </p>
        {{ $areas->withQueryString()->links() }}
    </div>
</div>

{{-- Action buttons --}}
<div class="flex gap-3 mt-4">
    <button class="btn-secondary flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">download</span>
        Export Excel
    </button>
    <button class="btn-primary flex items-center gap-2" onclick="openModal('areaModal')">
        <span class="material-symbols-outlined text-lg">add</span>
        Tambah Data
    </button>
</div>

{{-- Modal Add Area --}}
<div id="areaModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Tambah Area</h3>
            <button onclick="closeModal('areaModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.area.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="form-label">Nama Area</label>
                <input type="text" name="nama" class="form-input" placeholder="PIT Alpha" required>
            </div>
            <div>
                <label class="form-label">Kode Area</label>
                <input type="text" name="kode" class="form-input" placeholder="PA-001" required>
            </div>
            <div>
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-input" rows="2" placeholder="Keterangan opsional..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('areaModal')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Area --}}
<div id="areaEditModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Edit Area</h3>
            <button onclick="closeModal('areaEditModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editAreaForm" method="POST" class="p-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Nama Area</label>
                <input type="text" name="nama" id="edit_area_nama" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Kode Area</label>
                <input type="text" name="kode" id="edit_area_kode" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" id="edit_area_keterangan" class="form-input" rows="2"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('areaEditModal')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(type) {
    document.getElementById(type).classList.remove('hidden');
}
function closeModal(type) {
    document.getElementById(type).classList.add('hidden');
}
function editArea(id) {
    fetch(`/admin/area/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('editAreaForm').action = `/admin/area/${id}`;
            document.getElementById('edit_area_nama').value = data.nama;
            document.getElementById('edit_area_kode').value = data.kode;
            document.getElementById('edit_area_keterangan').value = data.keterangan || '';
            openModal('areaEditModal');
        })
        .catch(() => {
            alert('Gagal memuat data area');
        });
}
function deleteArea(id) {
    if (confirm('Yakin ingin menghapus area ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/area/${id}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
