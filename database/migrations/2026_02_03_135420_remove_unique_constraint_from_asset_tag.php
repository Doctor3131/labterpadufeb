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
        Schema::table('asset_units', function (Blueprint $table) {
            // Drop unique constraint on asset_tag
            $table->dropUnique(['asset_tag']);
            
            // Add composite unique: same asset_tag can exist if different batch or lab
            $table->unique(['asset_tag', 'batch_id', 'lab_id'], 'asset_units_composite_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_units', function (Blueprint $table) {
            // Drop composite unique
            $table->dropUnique('asset_units_composite_unique');
            
            // Restore simple unique on asset_tag
            $table->unique('asset_tag');
        });
    }
};
