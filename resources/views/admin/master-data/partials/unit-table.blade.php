<div class="card">
    {{-- Search and Filter --}}
    <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
                    <span class="material-symbols-outlined text-lg">search</span>
                </span>
                <form method="GET" action="{{ route('admin.master-data.index') }}">
                    <input type="hidden" name="tab" value="unit">
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">UNIT ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">MODEL/TIPE</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">TAHUN</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">STATUS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse($units as $index => $unit)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-[var(--text)]">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-[var(--text)]">{{ $unit->kode }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">{{ $unit->nama }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">{{ $unit->tahun ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $unit->real_status_badge }}">
                                <span class="w-1.5 h-1.5 rounded-full
                                    {{ match($unit->real_status) {
                                        'active' => 'bg-green-500',
                                        'servis' => 'bg-yellow-500',
                                        'breakdown' => 'bg-red-500',
                                        'standby' => 'bg-gray-400',
                                        default => 'bg-gray-400',
                                    } }}"></span>
                                {{ ucfirst($unit->real_status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button class="text-[var(--primary)] hover:opacity-75" onclick="editUnit({{ $unit->id }})">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <button class="text-red-600 hover:text-red-700" onclick="deleteUnit({{ $unit->id }})">
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
            Showing {{ $units->firstItem() }} to {{ $units->lastItem() }} of {{ $units->total() }} entries
        </p>
        {{ $units->withQueryString()->links() }}
    </div>
</div>

{{-- Action buttons --}}
<div class="flex gap-3 mt-4">
    <button class="btn-secondary flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">download</span>
        Export Excel
    </button>
    <button class="btn-primary flex items-center gap-2" onclick="openModal('unitModal')">
        <span class="material-symbols-outlined text-lg">add</span>
        Tambah Data
    </button>
</div>

{{-- Modal Add Unit --}}
<div id="unitModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Tambah Unit</h3>
            <button onclick="closeModal('unitModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.unit.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Kode Unit</label>
                    <input type="text" name="kode" class="form-input" placeholder="EXC-001" required>
                </div>
                <div>
                    <label class="form-label">Tipe</label>
                    <select name="tipe" class="form-input" required>
                        <option value="excavator">Excavator</option>
                        <option value="dump_truck">Dump Truck</option>
                        <option value="bulldozer">Bulldozer</option>
                        <option value="motor_grader">Motor Grader</option>
                        <option value="loader">Loader</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Nama/Model</label>
                <input type="text" name="nama" class="form-input" placeholder="Excavator PC2000-8" required>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Merk</label>
                    <input type="text" name="merk" class="form-input" placeholder="Komatsu">
                </div>
                <div>
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-input" placeholder="PC2000-8">
                </div>
                <div>
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-input" placeholder="2021" min="1900" max="{{ date('Y') + 1 }}">
                </div>
            </div>
            {{-- Status is always active on master data, handled by backend --}}
            <div>
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-input" rows="2" placeholder="Keterangan opsional..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('unitModal')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Unit --}}
<div id="unitEditModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Edit Unit</h3>
            <button onclick="closeModal('unitEditModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editUnitForm" method="POST" class="p-4 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Kode Unit</label>
                    <input type="text" name="kode" id="edit_kode" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Tipe</label>
                    <select name="tipe" id="edit_tipe" class="form-input" required>
                        <option value="excavator">Excavator</option>
                        <option value="dump_truck">Dump Truck</option>
                        <option value="bulldozer">Bulldozer</option>
                        <option value="motor_grader">Motor Grader</option>
                        <option value="loader">Loader</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Nama/Model</label>
                <input type="text" name="nama" id="edit_nama" class="form-input" required>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Merk</label>
                    <input type="text" name="merk" id="edit_merk" class="form-input">
                </div>
                <div>
                    <label class="form-label">Model</label>
                    <input type="text" name="model" id="edit_model" class="form-input">
                </div>
                <div>
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" id="edit_tahun" class="form-input">
                </div>
            </div>
            {{-- Status is always active on master data, handled by backend --}}
            <div>
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" id="edit_keterangan" class="form-input" rows="2"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('unitEditModal')" class="btn-secondary">Batal</button>
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
function editUnit(id) {
    fetch(`/admin/unit/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('editUnitForm').action = `/admin/unit/${id}`;
            document.getElementById('edit_kode').value = data.kode;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_tipe').value = data.tipe;
            document.getElementById('edit_merk').value = data.merk || '';
            document.getElementById('edit_model').value = data.model || '';
            document.getElementById('edit_tahun').value = data.tahun || '';
            document.getElementById('edit_keterangan').value = data.keterangan || '';
            openModal('unitEditModal');
        })
        .catch(() => {
            alert('Gagal memuat data unit');
        });
}
function deleteUnit(id) {
    if (confirm('Yakin ingin menghapus unit ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/unit/${id}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
