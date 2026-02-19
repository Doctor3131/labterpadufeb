<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add non-perkuliahan specific fields to schedules table.
     * These fields allow admin to create/edit non-perkuliahan schedules
     * directly without requiring a linked booking record.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('activity_type')->nullable()->after('type');
            $table->string('position')->nullable()->after('lecturer');
            $table->text('equipment_needs')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['activity_type', 'position', 'equipment_needs']);
        });
    }
};
