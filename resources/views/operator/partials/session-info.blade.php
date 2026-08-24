@props(['description' => 'Silakan isi data operasional harian.'])
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 shadow-sm">
    <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-amber-600 mt-0.5 text-xl">info</span>
        <div>
            <p class="font-heading font-bold text-amber-900 text-sm sm:text-base">Panduan Pengisian Form</p>
            <p class="text-xs sm:text-sm text-amber-800/90 mt-1 leading-relaxed">{{ $description }}</p>
        </div>
    </div>
</div>
