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
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('lab_id')->constrained('labs')->cascadeOnDelete();
            $table->enum('condition', ['BAIK', 'RUSAK', 'HILANG', 'MAINTENANCE']);
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            
            // Unique: satu kombinasi batch+lab+condition hanya bisa ada satu row
            $table->unique(['batch_id', 'lab_id', 'condition'], 'inventory_balances_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};
