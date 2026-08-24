<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->integer('target_ritasi')->default(0);
            $table->timestamps();

            $table->unique(['material_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_targets');
    }
};
