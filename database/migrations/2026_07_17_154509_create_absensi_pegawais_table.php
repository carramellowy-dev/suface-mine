<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi_pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('alat_id')->constrained('alats');
            $table->date('tanggal');
            $table->enum('shift', ['siang', 'malam']);
            $table->enum('tipe_pekerjaan', ['unit_non_ritasi', 'unit_ritasi', 'pekerjaan_general']);
            $table->decimal('hm_awal', 10, 2);
            $table->decimal('hm_akhir', 10, 2);
            $table->decimal('hm_total', 10, 2);
            $table->text('deskripsi_pekerjaan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_pegawais');
    }
};
