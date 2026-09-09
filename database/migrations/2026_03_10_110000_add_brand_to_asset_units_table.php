<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_units', function (Blueprint $table) {
            $table->string('brand', 100)->nullable()->after('batch_id');
        });

        // Backfill from batches.brand
        if (DB::getDriverName() === 'mysql') {
            DB::statement('UPDATE asset_units au
                JOIN batches b ON b.id = au.batch_id
                SET au.brand = b.brand
                WHERE b.brand IS NOT NULL');
        } else {
            DB::statement('UPDATE asset_units SET brand = (SELECT brand FROM batches WHERE batches.id = asset_units.batch_id) WHERE EXISTS (SELECT 1 FROM batches WHERE batches.id = asset_units.batch_id AND batches.brand IS NOT NULL)');
        }
    }

    public function down(): void
    {
        Schema::table('asset_units', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
