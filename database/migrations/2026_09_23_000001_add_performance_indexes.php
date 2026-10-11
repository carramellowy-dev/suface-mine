<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ritasis', function (Blueprint $table) {
            $table->index('tanggal', 'ritasis_tanggal_idx');
            $table->index(['unit_id', 'tanggal'], 'ritasis_unit_tanggal_idx');
            $table->index(['tanggal', 'shift'], 'ritasis_tanggal_shift_idx');
        });
        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->index('tanggal', 'non_ritasis_tanggal_idx');
            $table->index(['unit_id', 'tanggal'], 'non_ritasis_unit_tanggal_idx');
            $table->index(['tanggal', 'shift'], 'non_ritasis_tanggal_shift_idx');
        });
        Schema::table('unit_utilizations', function (Blueprint $table) {
            $table->index('unit_id', 'unit_utilizations_unit_idx');
            $table->index('started_at', 'unit_utilizations_started_idx');
            $table->index(['status', 'started_at'], 'unit_utilizations_status_started_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ritasis', function (Blueprint $table) {
            $table->dropIndex('ritasis_tanggal_idx');
            $table->dropIndex('ritasis_unit_tanggal_idx');
            $table->dropIndex('ritasis_tanggal_shift_idx');
        });
        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->dropIndex('non_ritasis_tanggal_idx');
            $table->dropIndex('non_ritasis_unit_tanggal_idx');
            $table->dropIndex('non_ritasis_tanggal_shift_idx');
        });
        Schema::table('unit_utilizations', function (Blueprint $table) {
            $table->dropIndex('unit_utilizations_unit_idx');
            $table->dropIndex('unit_utilizations_started_idx');
            $table->dropIndex('unit_utilizations_status_started_idx');
        });
    }
};
