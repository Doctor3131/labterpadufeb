<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_borrowings', function (Blueprint $table) {
            $table->string('tracking_token', 32)->nullable()->unique()->after('status');
        });

        // Isi token untuk semua record yang sudah ada
        \DB::table('asset_borrowings')->whereNull('tracking_token')->orderBy('id')->each(function ($row) {
            \DB::table('asset_borrowings')->where('id', $row->id)->update([
                'tracking_token' => Str::random(32),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_borrowings', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn('tracking_token');
        });
    }
};
