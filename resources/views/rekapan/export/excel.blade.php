<table border="1" style="border-collapse: collapse; width: 100%; font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px;">
    <thead>
        <tr>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">No</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Tanggal</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Shift</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Nama Operator</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">NIK</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Pekerjaan</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Unit</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Area / Lokasi</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: right;">HM Awal</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: right;">HM Akhir</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: right;">HM Total / Jam</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Output / Produksi</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Deskripsi Pekerjaan</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @forelse($rows as $r)
            <tr>
                <td style="padding: 6px 12px;">{{ $no++ }}</td>
                <td style="padding: 6px 12px;">{{ $r['tanggal'] ? (\Illuminate\Support\Carbon::parse($r['tanggal'])->format('d M Y')) : '-' }}</td>
                <td style="padding: 6px 12px;">{{ $r['shift_label'] }}</td>
                <td style="padding: 6px 12px;">{{ $r['pegawai']?->nama ?? '-' }}</td>
                <td style="padding: 6px 12px;">{{ $r['pegawai']?->nik ?? '-' }}</td>
                <td style="padding: 6px 12px;">{{ $r['tipe_pekerjaan'] }}</td>
                <td style="padding: 6px 12px;">{{ $r['unit_kode'] }}</td>
                <td style="padding: 6px 12px;">{{ $r['area_nama'] }}</td>
                <td style="padding: 6px 12px; text-align: right;">{{ $r['hm_awal'] !== null ? number_format((float)$r['hm_awal'], 1) : '-' }}</td>
                <td style="padding: 6px 12px; text-align: right;">{{ $r['hm_akhir'] !== null ? number_format((float)$r['hm_akhir'], 1) : '-' }}</td>
                <td style="padding: 6px 12px; text-align: right;">
                    @if($r['source_type'] === 'general')
                        {{ $r['jam_mulai'] }} - {{ $r['jam_selesai'] }} {{ $r['is_overtime'] ? '(Overtime)' : '' }}
                    @else
                        {{ number_format((float)$r['hm_total'], 1) }} Jam
                    @endif
                </td>
                <td style="padding: 6px 12px;">
                    @if($r['source_type'] === 'ritasi')
                        {{ $r['ritasi_count'] }} Rit @if($r['quantity']) ({{ number_format((float)$r['quantity'], 0) }} {{ $r['quantity_unit'] }}) @endif
                    @else
                        -
                    @endif
                </td>
                <td style="padding: 6px 12px;">{{ $r['deskripsi'] ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" style="padding: 12px; text-align: center; color: #888888;">Tidak ada data aktivitas operator pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
