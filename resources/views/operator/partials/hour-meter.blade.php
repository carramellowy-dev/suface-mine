<h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
    <span class="material-symbols-outlined text-[var(--primary)]">timer</span>
    Hour Meter (HM)
</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div>
        <label class="form-label">HM Awal</label>
        <input type="number" name="hm_awal" class="form-input" step="0.1" min="0" value="0.0" required id="hmAwal">
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Contoh: 1250.2 (angka penunjuk meteran jam awal shift)</p>
    </div>
    <div>
        <label class="form-label">HM Akhir</label>
        <input type="number" name="hm_akhir" class="form-input" step="0.1" min="0" value="0.0" required id="hmAkhir">
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Contoh: 1258.5 (angka penunjuk meteran jam akhir shift)</p>
    </div>
    <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-4 flex flex-col justify-center">
        <span class="text-xs sm:text-sm font-bold text-slate-600">Total Durasi HM Kerja:</span>
        <span class="text-xl sm:text-2xl font-bold text-[var(--accent)] mt-0.5" id="hmTotal">0.0 Jam</span>
    </div>
</div>
