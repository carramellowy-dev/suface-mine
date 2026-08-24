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
            ->when($request->filled('shift'), fn ($q) => $q->where('shift', $request->shift));
    }

    private function aggregate(Request $request): array
    {
        $ritasiAgg = $this->applyFilters(Ritasi::selectRaw('pegawai_id, COUNT(*) as total, COALESCE(SUM(hm_total), 0) as hm'), $request)
            ->groupBy('pegawai_id')->get()->keyBy('pegawai_id');

        $nonRitasiAgg = $this->applyFilters(NonRitasi::selectRaw('pegawai_id, COUNT(*) as total, COALESCE(SUM(hm_total), 0) as hm')->whereNull('jam_mulai'), $request)
            ->groupBy('pegawai_id')->get()->keyBy('pegawai_id');

        $generalAgg = $this->applyFilters(NonRitasi::selectRaw('pegawai_id, COUNT(*) as total')->whereNotNull('jam_mulai'), $request)
            ->groupBy('pegawai_id')->get()->keyBy('pegawai_id');

        return [$ritasiAgg, $nonRitasiAgg, $generalAgg];
    }

        public function index(Request $request)
    {
        [$ritasiAgg, $nonRitasiAgg, $generalAgg] = $this->aggregate($request);

        $rows = Pegawai::orderBy('nama')->get()->map(function ($p) use ($ritasiAgg, $nonRitasiAgg, $generalAgg) {
            return [
                'pegawai' => $p,
                'ritasi' => (int) ($ritasiAgg->get($p->id)->total ?? 0),
                'ritasi_hm' => (float) ($ritasiAgg->get($p->id)->hm ?? 0),
                'non_ritasi' => (int) ($nonRitasiAgg->get($p->id)->total ?? 0),
                'non_ritasi_hm' => (float) ($nonRitasiAgg->get($p->id)->hm ?? 0),
                'general' => (int) ($generalAgg->get($p->id)->total ?? 0),
            ];
        });

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $rows = $rows->filter(fn ($r) => str_contains(strtolower($r['pegawai']->nama ?? ''), $search))->values();
        }

        // No pagination — show all operators so there are no duplicate-name splits across pages
        return view('rekapan.index', compact('rows'));
    }

    public function export(Request $request)
    {
        [$ritasiAgg, $nonRitasiAgg, $generalAgg] = $this->aggregate($request);

        $rows = Pegawai::orderBy('nama')->get()->map(function ($p) use ($ritasiAgg, $nonRitasiAgg, $generalAgg) {
            return [
                'pegawai' => $p,
                'ritasi' => (int) ($ritasiAgg->get($p->id)->total ?? 0),
                'ritasi_hm' => (float) ($ritasiAgg->get($p->id)->hm ?? 0),
                'non_ritasi' => (int) ($nonRitasiAgg->get($p->id)->total ?? 0),
                'non_ritasi_hm' => (float) ($nonRitasiAgg->get($p->id)->hm ?? 0),
                'general' => (int) ($generalAgg->get($p->id)->total ?? 0),
            ];
        });

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $rows = $rows->filter(fn ($r) => str_contains(strtolower($r['pegawai']->nama ?? ''), $search))->values();
        }

        $filename = "Rekapan_Operator_" . date('Y-m-d_His') . ".xls";
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
            ->whereNull('jam_mulai')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');
            
        $generalQuery = NonRitasi::with(['unit', 'area'])
            ->where('pegawai_id', $id)
            ->whereNotNull('jam_mulai')
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        $ritasiQuery = $this->applyFilters($ritasiQuery, $request);
        $nonRitasiQuery = $this->applyFilters($nonRitasiQuery, $request);
        $generalQuery = $this->applyFilters($generalQuery, $request);

        $ritasis = $ritasiQuery->get();
        $nonRitasis = $nonRitasiQuery->get();
        $generals = $generalQuery->get();

        return view('rekapan.show', compact('pegawai', 'ritasis', 'nonRitasis', 'generals'));
    }
}
