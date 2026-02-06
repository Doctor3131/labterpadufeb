<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds has_sub_data field to support single-level master data
     * (master data that doesn't need sub-data, variable code entered directly)
     */
    public function up(): void
    {
        Schema::table('bps_master_data', function (Blueprint $table) {
            $table->boolean('has_sub_data')->default(true)->after('is_active');
        });

        // Add nullable master_id to bps_request_variables for single-level master data
        Schema::table('bps_request_variables', function (Blueprint $table) {
            $table->foreignId('master_id')->nullable()->after('request_id')
                ->constrained('bps_master_data')->onDelete('cascade');
            
            // Make sub_data_id nullable for single-level requests
            $table->dropForeign(['sub_data_id']);
            $table->unsignedBigInteger('sub_data_id')->nullable()->change();
            $table->foreign('sub_data_id')->references('id')->on('bps_sub_data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bps_request_variables', function (Blueprint $table) {
            $table->dropForeign(['master_id']);
            $table->dropColumn('master_id');
        });

        Schema::table('bps_master_data', function (Blueprint $table) {
            $table->dropColumn('has_sub_data');
        });
    }
};
