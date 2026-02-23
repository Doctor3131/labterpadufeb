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
        Schema::table('asset_borrowings', function (Blueprint $table) {
            $table->date('replacement_deadline')->nullable()->after('damage_description');
            $table->boolean('is_replaced')->default(false)->after('replacement_deadline');
            $table->timestamp('replaced_at')->nullable()->after('is_replaced');
            $table->foreignId('replaced_by')->nullable()->constrained('users')->nullOnDelete()->after('replaced_at');
            $table->text('replacement_notes')->nullable()->after('replaced_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_borrowings', function (Blueprint $table) {
            $table->dropForeign(['replaced_by']);
            $table->dropColumn([
                'replacement_deadline',
                'is_replaced',
                'replaced_at',
                'replaced_by',
                'replacement_notes'
            ]);
        });
    }
};
