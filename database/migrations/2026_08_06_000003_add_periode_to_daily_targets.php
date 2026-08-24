<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One target per material per period (harian/mingguan/bulanan).
        // The material_id unique index is also used by the FK, so drop the FK first.
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropUnique(['material_id']);
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->enum('periode', ['harian', 'mingguan', 'bulanan'])->default('harian')->after('material_id');
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->unique(['material_id', 'periode']);
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropUnique(['material_id', 'periode']);
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropColumn('periode');
        });
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->unique('material_id');
            $table->foreign('material_id')->references('id')->on('materials')->cascadeOnDelete();
        });
    }
};
