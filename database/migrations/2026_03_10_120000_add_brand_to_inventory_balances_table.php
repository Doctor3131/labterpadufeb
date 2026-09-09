<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->string('brand', 100)->nullable()->after('batch_id');
        });

        // Backfill from batches.brand
        if (DB::getDriverName() === 'mysql') {
            DB::statement('UPDATE inventory_balances ib
                JOIN batches b ON b.id = ib.batch_id
                SET ib.brand = b.brand
                WHERE b.brand IS NOT NULL');
        } else {
            DB::statement('UPDATE inventory_balances SET brand = (SELECT brand FROM batches WHERE batches.id = inventory_balances.batch_id) WHERE EXISTS (SELECT 1 FROM batches WHERE batches.id = inventory_balances.batch_id AND batches.brand IS NOT NULL)');
        }
    }

    public function down(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
