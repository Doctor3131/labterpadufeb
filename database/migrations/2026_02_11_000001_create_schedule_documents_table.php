<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create schedule_documents table for optional document generation fields.
     * One-to-one relationship with schedules table.
     */
    public function up(): void
    {
        Schema::create('schedule_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('study_program')->nullable();
            $table->string('nim', 50)->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('lecturer_nip', 50)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('software_needs')->nullable();
            $table->string('ktm_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_documents');
    }
};
