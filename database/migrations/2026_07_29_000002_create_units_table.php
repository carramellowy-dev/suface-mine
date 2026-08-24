<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('tipe');
            $table->string('merk')->nullable();
            $table->string('model')->nullable();
            $table->integer('tahun')->nullable();
            $table->decimal('kapasitas', 10, 2)->nullable();
            $table->decimal('fuel_consumption_rate', 8, 2)->nullable();
            $table->enum('status', ['active', 'maintenance', 'breakdown', 'standby'])->default('active');
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('units');
    }
};
