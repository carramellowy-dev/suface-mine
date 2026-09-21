<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function hasIndex(string $table, string $index): bool {
        $conn = Schema::getConnection();
        $db = $conn->getDatabaseName();
        return DB::table('information_schema.statistics')
            ->where('table_schema', $db)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    public function up(): void {
        // Multi-baris per submit: 1 operator bisa input N material/area dalam 1 tanggal+shift,
        // sehingga unique (pegawai,tanggal,shift) dihapus dan diganti pengecekan di aplikasi.
        if (!Schema::hasColumn('ritasis', 'hm_mulai_kerja')) {
            Schema::table('ritasis', function (Blueprint $table) {
                $table->decimal('hm_mulai_kerja', 10, 2)->nullable()->after('hm_awal');
            });
        }
        if (!$this->hasIndex('ritasis', 'ritasi_pegawai_tgl_shift_idx')) {
            Schema::table('ritasis', function (Blueprint $table) {
                // Index pengganti agar FK pegawai_id tetap ter-index setelah unique dihapus
                $table->index(['pegawai_id', 'tanggal', 'shift'], 'ritasi_pegawai_tgl_shift_idx');
            });
        }
        if ($this->hasIndex('ritasis', 'ritasi_unique')) {
            Schema::table('ritasis', function (Blueprint $table) {
                $table->dropUnique('ritasi_unique');
            });
        }

        if (!Schema::hasColumn('non_ritasis', 'hm_mulai_kerja')) {
            Schema::table('non_ritasis', function (Blueprint $table) {
                $table->decimal('hm_mulai_kerja', 10, 2)->nullable()->after('hm_awal');
            });
        }
        if (!$this->hasIndex('non_ritasis', 'non_ritasi_pegawai_tgl_shift_idx')) {
            Schema::table('non_ritasis', function (Blueprint $table) {
                // Index pengganti agar FK pegawai_id tetap ter-index setelah unique dihapus
                $table->index(['pegawai_id', 'tanggal', 'shift'], 'non_ritasi_pegawai_tgl_shift_idx');
            });
        }
        if ($this->hasIndex('non_ritasis', 'non_ritasi_unique')) {
            Schema::table('non_ritasis', function (Blueprint $table) {
                $table->dropUnique('non_ritasi_unique');
            });
        }
    }

    public function down(): void {
        Schema::table('non_ritasis', function (Blueprint $table) {
            $table->dropColumn('hm_mulai_kerja');
        });

        Schema::table('ritasis', function (Blueprint $table) {
            $table->dropColumn('hm_mulai_kerja');
        });
    }
};
