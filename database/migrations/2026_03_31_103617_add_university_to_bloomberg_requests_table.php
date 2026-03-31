<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'university' column for 'dosen_non_undip' (labeled "Non Undip") applicant type.
     * These applicants need to specify their university instead of NIP.
     */
    public function up(): void
    {
        Schema::table('bloomberg_requests', function (Blueprint $table) {
            $table->string('university')->nullable()->after('study_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bloomberg_requests', function (Blueprint $table) {
            $table->dropColumn('university');
        });
    }
};
