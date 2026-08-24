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
        Schema::create('pemantauan_lapangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spv_id')->constrained('users');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('alat_id')->constrained('alats');
            $table->date('tanggal');
            $table->enum('shift', ['siang', 'malam']);
            $table->text('deskripsi');
            $table->text('kendala')->nullable();
            $table->unsignedTinyInteger('progress_persen');
            $table->enum('progress_status', ['belum_mulai', 'proses', 'selesai']);
            $table->timestamps();
            
            $table->unique(['spv_id', 'area_id', 'tanggal', 'shift'], 'pemantauan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemantauan_lapangans');
    }
};
