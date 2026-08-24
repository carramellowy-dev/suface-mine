@extends('layouts.app', ['headerTitle' => 'Manajemen Operator'])

@section('title', 'Manajemen Operator')

@section('content')

<div class="card !p-0 overflow-hidden">
    <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
        <h2 class="section-title mb-0 flex items-center gap-2">
            <span class="material-symbols-outlined text-[var(--primary)]">engineering</span>
            Daftar Operator
        </h2>
        <a href="{{ route('admin.pegawai.create') }}" class="btn-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add</span>
            Tambah Operator
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NO</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NAMA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse($pegawais as $index => $pegawai)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-[var(--text)]">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-[var(--text)]">{{ $pegawai->nama }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.pegawai.edit', $pegawai) }}" class="text-[var(--primary)] hover:opacity-75">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <button class="text-red-600 hover:text-red-700" onclick="deletePegawai({{ $pegawai->id }})">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-[var(--text-muted)]">Tidak ada data operator</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-[var(--border)]">
        {{ $pegawais->withQueryString()->links() }}
    </div>
</div>

<script>
function deletePegawai(id) {
    if (confirm('Yakin ingin menghapus operator ini?')) {
        fetch(`/admin/pegawai/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => location.reload());
    }
}
</script>
@endsection