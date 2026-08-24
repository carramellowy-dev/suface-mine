<div class="card">
    <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">
                    <span class="material-symbols-outlined text-lg">search</span>
                </span>
                <form method="GET" action="{{ route('admin.master-data.index') }}">
                    <input type="hidden" name="tab" value="user">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau username..." class="form-input pl-10 w-64">
                </form>
            </div>
        </div>
        <button class="btn-primary flex items-center gap-2" onclick="openModal('addUserModal')">
            <span class="material-symbols-outlined text-lg">add</span>
            Tambah User
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NO</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NAMA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">USERNAME</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">ROLE</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">OPERATOR</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse($users as $index => $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-[var(--text)]">{{ $users->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-[var(--text)]">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">{{ $user->username }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text)]">
                            <span class="badge {{ $user->role === 'admin' ? 'badge-active' : ($user->role === 'spv' ? 'badge-maintenance' : 'badge-inactive') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">
                            {{ $user->pegawai?->nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button class="text-[var(--primary)] hover:opacity-75" onclick="editUser({{ $user->id }})">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <form id="delete-user-{{ $user->id }}" action="{{ route('admin.user.destroy', $user->id) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                </form>
                                <button class="text-red-600 hover:text-red-700" onclick="deleteUser({{ $user->id }})">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-[var(--text-muted)]">Tidak ada data user</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-[var(--border)] flex items-center justify-between">
        <p class="text-sm text-[var(--text-muted)]">
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries
        </p>
        {{ $users->withQueryString()->links() }}
    </div>
</div>

{{-- Add User Modal --}}
<div id="addUserModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Tambah User</h3>
            <button onclick="closeModal('addUserModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.user.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-input" required placeholder="Nama lengkap">
            </div>
            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required placeholder="username">
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required minlength="6" placeholder="Minimal 6 karakter">
            </div>
            <div>
                <label class="form-label">Role</label>
                <select name="role" class="form-input" required>
                    <option value="">Pilih Role</option>
                    <option value="admin">Admin</option>
                    <option value="spv">Supervisor</option>
                    <option value="pegawai">Operator</option>
                </select>
            </div>
            <div>
                <label class="form-label">Operator (opsional)</label>
                <select name="pegawai_id" class="form-input">
                    <option value="">-- Tidak ada --</option>
                    @foreach(\App\Models\Pegawai::orderBy('nama')->get() as $peg)
                        <option value="{{ $peg->id }}">{{ $peg->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('addUserModal')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit User Modal --}}
<div id="editUserModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-lg mx-4">
        <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
            <h3 class="font-heading font-bold text-[var(--primary)]">Edit User</h3>
            <button onclick="closeModal('editUserModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editUserForm" method="POST" class="p-4 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" id="edit_user_name" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" id="edit_user_username" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Password (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" id="edit_user_password" class="form-input" minlength="6" placeholder="Kosongkan jika tidak diubah">
            </div>
            <div>
                <label class="form-label">Role</label>
                <select name="role" id="edit_user_role" class="form-input" required>
                    <option value="admin">Admin</option>
                    <option value="spv">Supervisor</option>
                    <option value="pegawai">Operator</option>
                </select>
            </div>
            <div>
                <label class="form-label">Operator (opsional)</label>
                <select name="pegawai_id" id="edit_user_pegawai" class="form-input">
                    <option value="">-- Tidak ada --</option>
                    @foreach(\App\Models\Pegawai::orderBy('nama')->get() as $peg)
                        <option value="{{ $peg->id }}">{{ $peg->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-[var(--border)]">
                <button type="button" onclick="closeModal('editUserModal')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
function editUser(id) {
    fetch(`/admin/user/${id}/edit`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('editUserForm').action = `/admin/user/${id}`;
            document.getElementById('edit_user_name').value = data.name;
            document.getElementById('edit_user_username').value = data.username;
            document.getElementById('edit_user_role').value = data.role;
            document.getElementById('edit_user_pegawai').value = data.pegawai_id || '';
            document.getElementById('edit_user_password').value = '';
            openModal('editUserModal');
        })
        .catch(() => {
            alert('Gagal memuat data user');
        });
}
function deleteUser(id) {
    if (confirm('Yakin ingin menghapus user ini?')) {
        document.getElementById('delete-user-' + id).submit();
    }
}
</script>