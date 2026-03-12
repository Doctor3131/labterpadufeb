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
        Schema::table('labs', function (Blueprint $table) {
            if (Schema::hasColumn('labs', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('labs', 'location')) {
                $table->dropColumn('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak dapat di-rollback karena data code dan location sudah dihapus permanen
    }
};
