<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('recurrence_days')->nullable();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->json('recurrence_days')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('recurrence_days');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('recurrence_days');
        });
    }
};
