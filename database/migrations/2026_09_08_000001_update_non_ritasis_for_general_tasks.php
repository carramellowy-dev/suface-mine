<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->change();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('senior_spv_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn('supervisor_id');
            $table->dropForeign(['senior_spv_id']);
            $table->dropColumn('senior_spv_id');
            $table->foreignId('unit_id')->nullable(false)->change();
        });
    }
};
