<?php

namespace App\Http\Controllers;

use App\Models\NonRitasi;
use App\Models\Pegawai;
use App\Models\Ritasi;
use Illuminate\Http\Request;

class RekapanController extends Controller
{
    private function applyFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('tanggal_start') && $request->filled('tanggal_end'), fn ($q) => $q->whereBetween('tanggal', [$request->tanggal_start, $request->tanggal_end]))
            ->when($request->filled('tanggal_start') && ! $request->filled('tanggal_end'), fn ($q) => $q->whereDate('tanggal', $request->tanggal_start))
            ->when(! $request->filled('tanggal_start') && $request->filled('tanggal_end'), fn ($q) => $q->whereDate('tanggal', '<=', $request->tanggal_end))
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift));
    }

    private function getOperationalLogs(Request $request): array
    {
        $tanggalStart = $request->input('tanggal_start');
        $tanggalEnd = $request->input('tanggal_end');
        $shift = $request->input('shift');
        $search = $request->input('search');

        $hasDateFilter = !empty($tanggalStart) || !empty($tanggalEnd);

        // Query Ritasi
        $ritasiQuery = Ritasi::with(['pegawai', 'unit', 'area', 'material'])
            ->when($tanggalStart && $tanggalEnd, fn ($q) => $q->whereBetween('tanggal', [$tanggalStart, $tanggalEnd]))
            ->when($tanggalStart && ! $tanggalEnd, fn ($q) => $q->whereDate('tanggal', $tanggalStart))
            ->when(! $tanggalStart && $tanggalEnd, fn ($q) => $q->whereDate('tanggal', '<=', $tanggalEnd))
            ->when($shift, fn ($q) => $q->where('shift', $shift))
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        // Query Non-Ritasi & General
        $nonRitasiQuery = NonRitasi::with(['pegawai', 'unit', 'area', 'supervisor', 'seniorSpv'])
            ->when($tanggalStart && $tanggalEnd, fn ($q) => $q->whereBetween('tanggal', [$tanggalStart, $tanggalEnd]))
            ->when($tanggalStart && ! $tanggalEnd, fn ($q) => $q->whereDate('tanggal', $tanggalStart))
            ->when(! $tanggalStart && $tanggalEnd, fn ($q) => $q->whereDate('tanggal', '<=', $tanggalEnd))
            ->when($shift, fn ($q) => $q->where('shift', $shift))
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $searchLower = '%' . strtolower($search) . '%';
            $ritasiQuery->where(function ($q) use ($searchLower) {
                $q->whereHas('pegawai', fn ($pq) => $pq->whereRaw('LOWER(nama) LIKE ?', [$searchLower]))
                   ->orWhereHas('unit', fn ($uq) => $uq->whereRaw('LOWER(kode) LIKE ?', [$searchLower]))
                   ->orWhereHas('area', fn ($aq) => $aq->whereRaw('LOWER(nama) LIKE ?', [$searchLower]))
                   ->orWhereRaw('LOWER(lokasi_pekerjaan) LIKE ?', [$searchLower]);
            });

            $nonRitasiQuery->where(function ($q) use ($searchLower) {
                $q->whereHas('pegawai', fn ($pq) => $pq->whereRaw('LOWER(nama) LIKE ?', [$searchLower]))
                   ->orWhereHas('unit', fn ($uq) => $uq->whereRaw('LOWER(kode) LIKE ?', [$searchLower]))
                   ->orWhereHas('area', fn ($aq) => $aq->whereRaw('LOWER(nama) LIKE ?', [$searchLower]))
                   ->orWhereRaw('LOWER(lokasi_pekerjaan) LIKE ?', [$searchLower]);
            });
        }

        // When no date filter is applied, limit query to recent 50 entries
        if (!$hasDateFilter) {
            $ritasiQuery->take(50);
            $nonRitasiQuery->take(50);
        }

        $ritasis = $ritasiQuery->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'source_type' => 'ritasi',
                'tipe_pekerjaan' => 'Ritasi',
                'badge_class' => 'bg-blue-50 text-blue-700 border border-blue-200',
                'tanggal' => $item->tanggal,
                'shift' => $item->shift,
                'shift_label' => $item->shift === 'siang' ? 'Day' : 'Night',
                'pegawai' => $item->pegawai,
                'unit_kode' => $item->unit?->kode ?? '-',
                'unit_model' => $item->unit?->model ?? '',
                'area_nama' => $item->area?->nama ?? ($item->lokasi_pekerjaan ?? '-'),
                'lokasi_pekerjaan' => $item->lokasi_pekerjaan,
                'hm_awal' => $item->hm_awal,
                'hm_akhir' => $item->hm_akhir,
                'hm_total' => $item->hm_total,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'is_overtime' => false,
                'ritasi_count' => $item->jumlah_ritasi,
                'quantity' => $item->quantity,
                'quantity_unit' => $item->quantity_unit ?? 'ton',
                'material_nama' => $item->material?->nama,
                'deskripsi' => $item->deskripsi_pekerjaan,
                'kendala' => $item->kendala,
                'created_at' => $item->created_at,
            ];
        });

        $nonRitasis = $nonRitasiQuery->get()->map(function ($item) {
            $isGeneral = is_null($item->unit_id);
            return [
                'id' => $item->id,
                'source_type' => $isGeneral ? 'general' : 'non_ritasi',
                'tipe_pekerjaan' => $isGeneral ? 'General' : 'Non-Ritasi',
                'badge_class' => $isGeneral
                    ? 'bg-amber-50 text-amber-700 border border-amber-200'
                    : 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                'tanggal' => $item->tanggal,
                'shift' => $item->shift,
                'shift_label' => $item->shift === 'siang' ? 'Day' : 'Night',
                'pegawai' => $item->pegawai,
                'unit_kode' => $item->unit?->kode ?? '-',
                'unit_model' => $item->unit?->model ?? '',
                'area_nama' => $item->area?->nama ?? ($item->lokasi_pekerjaan ?? '-'),
                'lokasi_pekerjaan' => $item->lokasi_pekerjaan,
                'hm_awal' => $item->hm_awal,
                'hm_akhir' => $item->hm_akhir,
                'hm_total' => $item->hm_total,
                'jam_mulai' => $item->jam_mulai,
                'jam_selesai' => $item->jam_selesai,
                'is_overtime' => (bool)$item->is_overtime,
                'ritasi_count' => null,
                'quantity' => null,
                'quantity_unit' => null,
                'material_nama' => null,
                'deskripsi' => $item->deskripsi_pekerjaan,
                'kendala' => $item->kendala,
                'created_at' => $item->created_at,
            ];
        });

        $rows = $ritasis->concat($nonRitasis)->sortByDesc(function ($r) {
            $t = $r['tanggal'] ? ($r['tanggal'] instanceof \Carbon\Carbon ? $r['tanggal']->format('Y-m-d') : substr((string)$r['tanggal'], 0, 10)) : '0000-00-00';
            $s = $r['shift'] === 'malam' ? '2' : '1';
            $c = $r['created_at'] ? $r['created_at']->format('H:i:s') : '00:00:00';
            return $t . '_' . $s . '_' . $c;
        })->values();

        if (!$hasDateFilter) {
            $rows = $rows->take(50);
        }

        return [$rows, $tanggalStart, $tanggalEnd, $shift, $search, $hasDateFilter];
    }

    public function index(Request $request)
    {
        [$rows, $tanggalStart, $tanggalEnd, $shift, $search, $hasDateFilter] = $this->getOperationalLogs($request);

        return view('rekapan.index', compact('rows', 'tanggalStart', 'tanggalEnd', 'shift', 'search', 'hasDateFilter'));
    }

    public function export(Request $request)
    {
        [$rows, $tanggalStart, $tanggalEnd, $shift, $search, $hasDateFilter] = $this->getOperationalLogs($request);

        $filename = "Rekapan_Aktivitas_Operator_" . date('Y-m-d_His') . ".xls";
        $sanitized = str_replace(['"', "\r", "\n"], '', $filename);

        return response()->view('rekapan.export.excel', compact('rows'))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $sanitized . '"');
    }

    public function show(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        
        $ritasiQuery = Ritasi::with(['unit', 'area', 'material'])
            ->where('pegawai_id', $id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');
            
        $nonRitasiQuery = NonRitasi::with(['unit', 'area'])
            ->where('pegawai_id', $id)
            ->whereNotNull('unit_id')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');
            
        $generalQuery = NonRitasi::with(['unit', 'area', 'supervisor', 'seniorSpv'])
            ->where('pegawai_id', $id)
            ->whereNull('unit_id')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        $ritasiQuery = $this->applyFilters($ritasiQuery, $request);
        $nonRitasiQuery = $this->applyFilters($nonRitasiQuery, $request);
        $generalQuery = $this->applyFilters($generalQuery, $request);

        $ritasis = $ritasiQuery->paginate(5, ['*'], 'ritasi_page')->withQueryString();
        $nonRitasis = $nonRitasiQuery->paginate(5, ['*'], 'non_ritasi_page')->withQueryString();
        $generals = $generalQuery->paginate(5, ['*'], 'general_page')->withQueryString();

        return view('rekapan.show', compact('pegawai', 'ritasis', 'nonRitasis', 'generals'));
    }
}
