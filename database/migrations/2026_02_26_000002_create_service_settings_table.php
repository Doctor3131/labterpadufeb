<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_settings', function (Blueprint $table) {
            $table->id();
            $table->string('service_type', 50); // 'bloomberg', 'refinitiv', etc.
            $table->string('key', 100);
            $table->string('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['service_type', 'key']);
        });

        // Seed default Bloomberg settings
        DB::table('service_settings')->insert([
            [
                'service_type' => 'bloomberg',
                'key' => 'capacity_per_session',
                'value' => '12',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_type' => 'bloomberg',
                'key' => 'walk_in_enabled',
                'value' => '0',
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_settings');
    }
};
