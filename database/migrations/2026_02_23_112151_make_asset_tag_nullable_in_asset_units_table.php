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
            // Make asset_tag nullable so it can be left empty when "Kosongkan" is chosen
            $table->string('asset_tag')->nullable()->change();
        });

        // Drop the composite unique constraint that would prevent multiple null asset_tags
        try {
            Schema::table('asset_units', function (Blueprint $table) {
                $table->dropUnique('asset_units_composite_unique');
            });
        } catch (\Exception $e) {
            // Constraint may not exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_units', function (Blueprint $table) {
            $table->string('asset_tag')->nullable(false)->change();
        });

        try {
            Schema::table('asset_units', function (Blueprint $table) {
                $table->unique(['asset_tag', 'batch_id', 'lab_id'], 'asset_units_composite_unique');
            });
        } catch (\Exception $e) {
            // Ignore
        }
    }
};
