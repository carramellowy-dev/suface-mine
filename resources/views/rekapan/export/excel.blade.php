<table border="1" style="border-collapse: collapse; width: 100%; font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px;">
    <thead>
        <tr>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">No</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Nama Operator</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Jumlah Ritasi</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">HM Ritasi</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Jumlah Non-Ritasi</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">HM Non-Ritasi</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Jumlah General</th>
            <th style="background: #1e3a5f; color: #ffffff; padding: 8px 12px; text-align: left;">Total HM</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($rows as $r)
            <tr>
                <td style="padding: 6px 12px;">{{ $no++ }}</td>
                <td style="padding: 6px 12px;">{{ $r['pegawai']->nama }}</td>
                <td style="padding: 6px 12px;">{{ $r['ritasi'] }}</td>
                <td style="padding: 6px 12px;">{{ $r['ritasi_hm'] }}</td>
                <td style="padding: 6px 12px;">{{ $r['non_ritasi'] }}</td>
                <td style="padding: 6px 12px;">{{ $r['non_ritasi_hm'] }}</td>
                <td style="padding: 6px 12px;">{{ $r['general'] }}</td>
                <td style="padding: 6px 12px;">{{ number_format($r['ritasi_hm'] + $r['non_ritasi_hm'], 1) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
