<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ritasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('shift', ['siang', 'malam']);
            $table->decimal('hm_awal', 10, 2);
            $table->decimal('hm_akhir', 10, 2);
            $table->decimal('hm_total', 10, 2);
            $table->integer('jumlah_ritasi')->default(0);
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('quantity_unit')->default('ton');
            $table->string('lokasi_pekerjaan')->nullable();
            $table->text('deskripsi_pekerjaan')->nullable();
            $table->text('kendala')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'tanggal', 'shift'], 'ritasi_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('ritasis');
    }
};
