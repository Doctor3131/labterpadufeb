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
        Schema::table('batches', function (Blueprint $table) {
            // MySQL uses the unique index to satisfy the FK on item_id.
            // We must add a plain index on item_id before we can drop the unique index.
            $table->index('item_id', 'batches_item_id_index');
        });

        Schema::table('batches', function (Blueprint $table) {
            // Now safe to drop the old unique constraint
            $table->dropUnique('batches_unique_procurement');

            // New constraint includes brand so batches with different brands
            // for the same item/source/arrival are kept separate
            $table->unique(['item_id', 'proc_source_code', 'arrival_mmyy', 'brand'], 'batches_unique_procurement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->index('item_id', 'batches_item_id_index');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropUnique('batches_unique_procurement');
            $table->unique(['item_id', 'proc_source_code', 'arrival_mmyy'], 'batches_unique_procurement');
            $table->dropIndex('batches_item_id_index');
        });
    }
};
