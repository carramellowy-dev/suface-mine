<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Target is set once per material (applies for days/weeks/months), not per date.
        // The composite unique index is also used by the material_id FK, so drop the FK first.
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropUnique(['material_id', 'tanggal']);
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->unique('material_id');
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropUnique(['material_id']);
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->date('tanggal')->nullable();
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->unique(['material_id', 'tanggal']);
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete();
        });
    }
};
