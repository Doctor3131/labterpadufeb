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
        Schema::table('asset_units', function (Blueprint $table) {
            $table->string('university_asset_code')->nullable()->after('asset_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_units', function (Blueprint $table) {
            $table->dropColumn('university_asset_code');
        });
    }
};
