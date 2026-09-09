<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('pemantauan_fotos');
        Schema::dropIfExists('pemantauan_lapangans');
        Schema::dropIfExists('absensi_pegawais');
        Schema::dropIfExists('alats');
        Schema::dropIfExists('material_movements');
        Schema::dropIfExists('unit_fuel_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables are dead legacy schema, no need to recreate on rollback
    }
};
