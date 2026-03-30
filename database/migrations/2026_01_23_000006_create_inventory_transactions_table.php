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
        // Header transaksi
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'RECEIPT',           // Penerimaan barang baru
                'CONDITION_CHANGE',  // Perubahan kondisi
                'ADJUSTMENT',        // Koreksi/adjustment manual
            ]);
            $table->foreignId('lab_id')->nullable()->constrained('labs')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['lab_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        // Detail line transaksi
        Schema::create('transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('inventory_transactions')->cascadeOnDelete();
            
            // Referensi ke unit (untuk STRUCTURED_TAG/SEAT_NUMBER) atau balance (untuk AGGREGATE)
            $table->foreignId('asset_unit_id')->nullable()->constrained('asset_units')->nullOnDelete();
            $table->foreignId('inventory_balance_id')->nullable()->constrained('inventory_balances')->nullOnDelete();
            
            // Perubahan kondisi
            $table->enum('from_condition', ['BAIK', 'RUSAK', 'HILANG', 'MAINTENANCE'])->nullable();
            $table->enum('to_condition', ['BAIK', 'RUSAK', 'HILANG', 'MAINTENANCE'])->nullable();
            
            // Quantity (untuk aggregate transactions)
            $table->integer('quantity')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_lines');
        Schema::dropIfExists('inventory_transactions');
    }
};
