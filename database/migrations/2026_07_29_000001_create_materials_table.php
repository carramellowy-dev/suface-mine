<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('satuan');
            $table->enum('kategori', ['ore', 'waste', 'fuel', 'lubricant', 'explosive', 'spare_part', 'other'])->default('other');
            $table->decimal('stok', 12, 2)->default(0);
            $table->decimal('stok_minimal', 12, 2)->default(0);
            $table->decimal('harga_satuan', 15, 2)->nullable();
            $table->enum('status', ['active', 'low_stock', 'inactive', 'restricted'])->default('active');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('unit_default')->default('ton');
            $table->decimal('to_ton_factor', 8, 4)->default(1);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('materials');
    }
};
