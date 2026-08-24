<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('material_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('consumption_rate', 10, 4)->nullable();
            $table->timestamps();
            $table->unique(['material_id', 'unit_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('material_unit');
    }
};
