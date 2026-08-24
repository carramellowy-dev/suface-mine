<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ritasis', function (Blueprint $table) {
            $table->decimal('fuel_consumption', 8, 2)->nullable()->after('hm_total');
        });

        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->decimal('fuel_consumption', 8, 2)->nullable()->after('hm_total');
        });
    }

    public function down(): void
    {
        Schema::table('ritasis', function (Blueprint $table) {
            $table->dropColumn('fuel_consumption');
        });

        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->dropColumn('fuel_consumption');
        });
    }
};
