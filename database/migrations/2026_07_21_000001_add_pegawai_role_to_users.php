<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','spv','pegawai') NOT NULL DEFAULT 'spv'");

            return;
        }

        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'spv', 'pegawai'))");
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','spv') NOT NULL DEFAULT 'spv'");

            return;
        }

        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'spv'))");
    }
};
