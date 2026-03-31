<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'blocked_session' column to allow blocking specific sessions instead of entire days.
     * null = block all sessions, 'sesi_1' = block sesi 1 only, 'sesi_2' = block sesi 2 only.
     * Existing records (blocked_session = null) will be treated as "block all sessions" (backward compatible).
     */
    public function up(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->string('blocked_session', 20)->nullable()->after('reason');
        });

        // Drop old unique constraint and add new one that includes blocked_session
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->dropUnique(['service_type', 'blocked_date']);
            $table->unique(['service_type', 'blocked_date', 'blocked_session'], 'blocked_dates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->dropUnique('blocked_dates_unique');
            $table->unique(['service_type', 'blocked_date']);
            $table->dropColumn('blocked_session');
        });
    }
};
