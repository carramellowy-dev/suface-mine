<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('material_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tipe', ['masuk', 'keluar', 'transfer', 'opname', 'koreksi']);
            $table->decimal('jumlah', 12, 2);
            $table->decimal('stok_sebelum', 12, 2);
            $table->decimal('stok_sesudah', 12, 2);
            $table->string('referensi')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('material_movements');
    }
};
