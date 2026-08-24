<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alats', function (Blueprint $table) {
            $table->string('jenis')->nullable()->after('nama');
        });

        DB::statement("UPDATE alats SET jenis = 'lainnya' WHERE jenis IS NULL");

        Schema::table('alats', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }

    public function down(): void
    {
        Schema::table('alats', function (Blueprint $table) {
            $table->string('kode')->nullable()->after('id');
        });

        DB::statement("UPDATE alats SET kode = 'NA' WHERE kode IS NULL");

        Schema::table('alats', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
