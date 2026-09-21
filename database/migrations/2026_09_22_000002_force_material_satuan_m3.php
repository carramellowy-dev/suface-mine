<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Satuan utama paten: meter kubik (M3) untuk semua material.
        // to_ton_factor TIDAK diubah agar konversi data lama (ton/bcm) tetap benar.
        DB::table('materials')->update(['satuan' => 'M3', 'unit_default' => 'm3']);
    }

    public function down(): void {
        // Tidak dapat mengembalikan nilai satuan semula secara otomatis.
    }
};
