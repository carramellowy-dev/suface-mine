<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('email_verification_tokens');

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT users_email_unique');
            DB::statement('ALTER TABLE users DROP COLUMN email');
            DB::statement('ALTER TABLE users DROP COLUMN email_verified_at');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_email_unique');
                $table->dropColumn(['email', 'email_verified_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique()->after('username');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }
};
