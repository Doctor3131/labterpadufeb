<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add komting_phone column for storing coordinator/komting phone number
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('komting_phone')->nullable()->after('komting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('komting_phone');
        });
    }
};
