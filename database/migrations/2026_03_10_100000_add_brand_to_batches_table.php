<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('item_id');
        });

        // Backfill brand from items into their batches
        if (DB::getDriverName() === 'mysql') {
            DB::statement('UPDATE batches b JOIN items i ON b.item_id = i.id SET b.brand = i.brand WHERE i.brand IS NOT NULL');
        } else {
            DB::statement('UPDATE batches SET brand = (SELECT brand FROM items WHERE items.id = batches.item_id) WHERE EXISTS (SELECT 1 FROM items WHERE items.id = batches.item_id AND items.brand IS NOT NULL)');
        }
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
