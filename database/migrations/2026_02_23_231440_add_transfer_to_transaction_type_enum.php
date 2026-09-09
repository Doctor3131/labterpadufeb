<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE inventory_transactions MODIFY COLUMN type ENUM('RECEIPT', 'CONDITION_CHANGE', 'ADJUSTMENT', 'TRANSFER') NOT NULL");
        } else {
            Schema::table('inventory_transactions', function (Blueprint $table) {
                $table->string('type')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE inventory_transactions MODIFY COLUMN type ENUM('RECEIPT', 'CONDITION_CHANGE', 'ADJUSTMENT') NOT NULL");
        }
    }
};
