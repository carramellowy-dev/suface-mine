<h2 class="section-title mb-4 flex items-center gap-2 pb-3 border-b">
    <span class="material-symbols-outlined text-[var(--primary)]">local_gas_station</span>
    Konsumsi Fuel
</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div>
        <label class="form-label">Konsumsi Fuel (Liter)</label>
        <input type="number" 
               name="fuel_consumption" 
               step="0.01" 
               min="0"
               class="form-input" 
               placeholder="Contoh: 150.5"
               value="{{ old('fuel_consumption') }}">
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500">Jumlah bahan bakar solar yang diisi ke tangki unit dalam satuan Liter. Kosongkan jika tidak mengisi BBM.</p>
    </div>
</div>
