<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bps_requests', function (Blueprint $table) {
            $table->dropColumn('tracking_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bps_requests', function (Blueprint $table) {
            $table->string('tracking_token', 32)->unique()->after('handled_by');
        });
    }
};
