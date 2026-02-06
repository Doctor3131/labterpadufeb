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
        Schema::create('asset_borrowing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_borrowing_id')->constrained('asset_borrowings')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('asset_unit_id')->nullable()->constrained('asset_units')->nullOnDelete(); // Untuk STRUCTURED_TAG & SEAT_NUMBER
            $table->foreignId('inventory_balance_id')->nullable()->constrained('inventory_balances')->nullOnDelete(); // Untuk AGGREGATE
            $table->integer('quantity')->default(1); // Untuk AGGREGATE
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('asset_borrowing_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_borrowing_items');
    }
};
