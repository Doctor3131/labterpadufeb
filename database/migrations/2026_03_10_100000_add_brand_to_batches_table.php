<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('item_id');
        });

        // Backfill brand from items into their batches
        DB::statement('UPDATE batches b JOIN items i ON b.item_id = i.id SET b.brand = i.brand WHERE i.brand IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
