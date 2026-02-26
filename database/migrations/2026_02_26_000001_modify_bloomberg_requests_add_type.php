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
        Schema::table('bloomberg_requests', function (Blueprint $table) {
            // Add type column: reservasi or walk_in
            $table->enum('type', ['reservasi', 'walk_in'])->default('reservasi')->after('token');

            // Remove attendance-related columns
            $table->dropForeign(['handled_by']);
            $table->dropIndex(['attendance_status']);
            $table->dropColumn(['attendance_status', 'attendance_marked_at', 'handled_by']);

            // Add composite index for capacity queries
            $table->index(['usage_date', 'session', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bloomberg_requests', function (Blueprint $table) {
            $table->dropIndex(['usage_date', 'session', 'type']);
            $table->dropColumn('type');

            $table->enum('attendance_status', ['pending', 'hadir', 'tidak_hadir'])->default('pending');
            $table->timestamp('attendance_marked_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->index('attendance_status');
        });
    }
};
