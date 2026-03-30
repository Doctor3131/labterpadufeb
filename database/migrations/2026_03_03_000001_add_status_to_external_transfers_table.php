<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_transfers', function (Blueprint $table) {
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam')->after('quantity');
            $table->date('returned_date')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('external_transfers', function (Blueprint $table) {
            $table->dropColumn(['status', 'returned_date']);
        });
    }
};
