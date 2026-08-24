@extends('layouts.app', ['headerTitle' => 'Manajemen SPV'])

@section('title', 'Manajemen SPV')

@section('content')

<div class="card !p-0 overflow-hidden">
    <div class="p-4 border-b border-[var(--border)] flex items-center justify-between">
        <h2 class="section-title mb-0 flex items-center gap-2">
            <span class="material-symbols-outlined text-[var(--primary)]">badge</span>
            Daftar SPV
        </h2>
        <a href="{{ route('admin.spv.create') }}" class="btn-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add</span>
            Tambah SPV
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NO</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NAMA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">USERNAME</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">AREA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @forelse($spvs as $index => $spv)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-[var(--text)]">{{ ($spvs->currentPage() - 1) * $spvs->perPage() + $index + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-[var(--text)]">{{ $spv->name }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">{{ $spv->username }}</td>
                        <td class="px-4 py-3 text-sm text-[var(--text-muted)]">
                            @forelse($spv->areas as $area)
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1">{{ $area->nama }}</span>
                            @empty
                                <span class="text-slate-400">-</span>
                            @endforelse
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.spv.edit', $spv) }}" class="text-[var(--primary)] hover:opacity-75">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <button class="text-red-600 hover:text-red-700" onclick="deleteSpv({{ $spv->id }})">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-[var(--text-muted)]">Tidak ada data SPV</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-[var(--border)]">
        {{ $spvs->withQueryString()->links() }}
    </div>
</div>

<script>
function deleteSpv(id) {
    if (confirm('Yakin ingin menghapus SPV ini?')) {
        fetch(`/admin/spv/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => location.reload());
    }
}
</script>
@endsection