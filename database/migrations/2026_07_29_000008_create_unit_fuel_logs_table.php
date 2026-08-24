<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('unit_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('fuel_amount', 10, 2);
            $table->decimal('odometer', 10, 2)->nullable();
            $table->string('driver_name')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('unit_fuel_logs');
    }
};
