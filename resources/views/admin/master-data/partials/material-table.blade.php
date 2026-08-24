<div class="card">
    {{-- Search and Filter --}}
    <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
                    <span class="material-symbols-outlined text-lg">search</span>
                </span>
                <form method="GET" action="{{ route('admin.master-data.index') }}">
                    <input type="hidden" name="tab" value="material">
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NAMA MATERIAL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">KODE MATERIAL</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">SATUAN</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">STATUS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse($materials as $index => $material)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-[var(--text)]">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-[var(--text)]">{{ $material->nama }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-[var(--text-muted)]">{{ $material->kode }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">{{ $material->satuan }}</td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $material->status_badge }}">
                                <span class="w-1.5 h-1.5 rounded-full
                                    {{ match($material->status) {
                                        'active' => 'bg-green-500',
                                        'low_stock' => 'bg-yellow-500',
                                        'inactive' => 'bg-gray-400',
                                        'restricted' => 'bg-red-500',
                                        default => 'bg-gray-400',
                                    } }}"></span>
                                {{ str_replace('_', ' ', ucfirst($material->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button class="text-[var(--primary)] hover:opacity-75" onclick="editMaterial({{ $material->id }})">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <button class="text-red-600 hover:text-red-700" onclick="deleteMaterial({{ $material->id }})">
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
            Showing {{ $materials->firstItem() }} to {{ $materials->lastItem() }} of {{ $materials->total() }} entries
        </p>
        {{ $materials->withQueryString()->links() }}
    </div>
</div>

{{-- Action buttons --}}
<div class="flex gap-3 mt-4">
    <button class="btn-secondary flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">download</span>
        Export Excel
    </button>
    <button class="btn-primary flex items-center gap-2" onclick="openModal('materialModal')">
        <span class="material-symbols-outlined text-lg">add</span>
        Tambah Data
    </button>
</div>

{{-- Modal Add Material --}}
<div id="materialModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Tambah Material</h3>
            <button onclick="closeModal('materialModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.material.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nama Material</label>
                    <input type="text" name="nama" class="form-input" placeholder="Bauxite Ore" required>
                </div>
                <div>
                    <label class="form-label">Kode Material</label>
                    <input type="text" name="kode" class="form-input" placeholder="MAT-BX-001" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" class="form-input" placeholder="Tonnes (t)" required>
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-input" required>
                        <option value="ore">Ore</option>
                        <option value="waste">Waste</option>
                        <option value="fuel">Fuel</option>
                        <option value="lubricant">Lubricant</option>
                        <option value="explosive">Explosive</option>
                        <option value="spare_part">Spare Part</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-input" step="0.01" min="0" value="0" required>
                </div>
                <div>
                    <label class="form-label">Stok Minimal</label>
                    <input type="number" name="stok_minimal" class="form-input" step="0.01" min="0" value="0" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Harga Satuan</label>
                    <input type="number" name="harga_satuan" class="form-input" step="0.01" min="0" placeholder="0">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input" required>
                        <option value="active">Active</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="inactive">Inactive</option>
                        <option value="restricted">Restricted</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-input" rows="2" placeholder="Keterangan opsional..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('materialModal')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Material --}}
<div id="materialEditModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Edit Material</h3>
            <button onclick="closeModal('materialEditModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editMaterialForm" method="POST" class="p-4 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nama Material</label>
                    <input type="text" name="nama" id="edit_mat_nama" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Kode Material</label>
                    <input type="text" name="kode" id="edit_mat_kode" class="form-input" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" id="edit_mat_satuan" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select name="kategori" id="edit_mat_kategori" class="form-input" required>
                        <option value="ore">Ore</option>
                        <option value="waste">Waste</option>
                        <option value="fuel">Fuel</option>
                        <option value="lubricant">Lubricant</option>
                        <option value="explosive">Explosive</option>
                        <option value="spare_part">Spare Part</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" id="edit_mat_stok" class="form-input" step="0.01" min="0" required>
                </div>
                <div>
                    <label class="form-label">Stok Minimal</label>
                    <input type="number" name="stok_minimal" id="edit_mat_stok_minimal" class="form-input" step="0.01" min="0" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Harga Satuan</label>
                    <input type="number" name="harga_satuan" id="edit_mat_harga" class="form-input" step="0.01" min="0">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_mat_status" class="form-input" required>
                        <option value="active">Active</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="inactive">Inactive</option>
                        <option value="restricted">Restricted</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" id="edit_mat_keterangan" class="form-input" rows="2"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('materialEditModal')" class="btn-secondary">Batal</button>
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
function editMaterial(id) {
    fetch(`/admin/material/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('editMaterialForm').action = `/admin/material/${id}`;
            document.getElementById('edit_mat_nama').value = data.nama;
            document.getElementById('edit_mat_kode').value = data.kode;
            document.getElementById('edit_mat_satuan').value = data.satuan;
            document.getElementById('edit_mat_kategori').value = data.kategori;
            document.getElementById('edit_mat_stok').value = data.stok;
            document.getElementById('edit_mat_stok_minimal').value = data.stok_minimal;
            document.getElementById('edit_mat_harga').value = data.harga_satuan || '';
            document.getElementById('edit_mat_status').value = data.status;
            document.getElementById('edit_mat_keterangan').value = data.keterangan || '';
            openModal('materialEditModal');
        })
        .catch(() => {
            alert('Gagal memuat data material');
        });
}
function deleteMaterial(id) {
    if (confirm('Yakin ingin menghapus material ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/material/${id}`;
        form.innerHTML = `
            @csrf
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
