<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: This migration previously contained BPS sub data seeding,
     * but has been moved to BpsDataSeeder to avoid duplication.
     * Run: php artisan db:seed --class=BpsDataSeeder
     */
    public function up(): void
    {
        // Data seeding moved to BpsDataSeeder
        // This migration is kept as a placeholder to maintain migration history
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse
    }
};
