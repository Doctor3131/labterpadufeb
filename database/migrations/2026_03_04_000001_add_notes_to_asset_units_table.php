<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_units', function (Blueprint $table) {
            $table->string('notes')->nullable()->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('asset_units', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
