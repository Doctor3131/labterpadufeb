<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per-occurrence overrides for a recurring schedule. Mirrors a Google
     * Calendar series exception: a single instance can be cancelled entirely,
     * or moved to a different lab / time on that specific date.
     *
     * type = 'cancelled' => the occurrence does not take place.
     * type = 'moved'     => the occurrence takes place at the override lab/time.
     */
    public function up(): void
    {
        Schema::create('schedule_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->date('occurrence_date');
            $table->enum('type', ['cancelled', 'moved']);
            $table->foreignId('lab_id')->nullable()->constrained()->onDelete('cascade');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'occurrence_date']);
            $table->index('occurrence_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_occurrences');
    }
};
