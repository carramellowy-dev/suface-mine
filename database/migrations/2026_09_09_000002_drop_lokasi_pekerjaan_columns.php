<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('ritasis', 'lokasi_pekerjaan')) {
            Schema::table('ritasis', function (Blueprint $table) {
                $table->dropColumn('lokasi_pekerjaan');
            });
        }

        if (Schema::hasColumn('non_ritasis', 'lokasi_pekerjaan')) {
            Schema::table('non_ritasis', function (Blueprint $table) {
                $table->dropColumn('lokasi_pekerjaan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ritasis', function (Blueprint $table) {
            $table->string('lokasi_pekerjaan')->nullable();
        });

        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->string('lokasi_pekerjaan')->nullable();
        });
    }
};
