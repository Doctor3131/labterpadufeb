<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates tables for BPS Data Request System:
     * - bps_master_data: Main categories (SUSENAS, SAKERNAS, etc.)
     * - bps_sub_data: Specific datasets under each category
     * - bps_requests: User data requests
     * - bps_request_data: Many-to-many relation between requests and sub_data
     * - bps_request_variables: Variable codes per dataset in a request
     */
    public function up(): void
    {
        // Master Data Categories (e.g., SUSENAS, SAKERNAS, STPIM)
        Schema::create('bps_master_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('has_sub_data')->default(true);
            $table->timestamps();
        });

        // Sub Data (specific datasets under each master)
        Schema::create('bps_sub_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained('bps_master_data')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // BPS Data Requests
        Schema::create('bps_requests', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique(); // For secure URL access
            
            // Applicant Type
            $table->enum('applicant_type', ['mahasiswa', 'dosen']);
            
            // Applicant Info
            $table->string('name');
            $table->string('email');
            $table->string('nim')->nullable();
            $table->string('nip')->nullable();
            $table->string('phone');
            $table->string('study_program')->nullable();
            
            // Purpose
            $table->enum('purpose', [
                'Skripsi',
                'Thesis', 
                'Disertasi',
                'Lomba',
                'Tugas Mata Kuliah',
                'Penelitian/Project Dengan Dosen',
                'Riset',
                'Lainnya'
            ]);
            $table->string('purpose_other')->nullable();
            
            // Collaboration with lecturer
            $table->boolean('has_lecturer_collaboration')->default(false);
            $table->string('collaborating_lecturer_name')->nullable();
            
            // Documents
            $table->string('ktm_path')->nullable();
            $table->string('statement_letter_path');
            
            // Agreement
            $table->boolean('agreement_accepted')->default(false);
            
            // Status
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Admin notes
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
        });

        // Many-to-many: Requests <-> Sub Data
        Schema::create('bps_request_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('bps_requests')->onDelete('cascade');
            $table->foreignId('sub_data_id')->constrained('bps_sub_data')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['request_id', 'sub_data_id']);
        });

        // Variable codes per request-dataset combination
        Schema::create('bps_request_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('bps_requests')->onDelete('cascade');
            $table->foreignId('master_id')->nullable()->constrained('bps_master_data')->onDelete('cascade');
            $table->foreignId('sub_data_id')->nullable()->constrained('bps_sub_data')->onDelete('cascade');
            $table->text('variables');
            $table->timestamps();
            
            $table->unique(['request_id', 'sub_data_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bps_request_variables');
        Schema::dropIfExists('bps_request_data');
        Schema::dropIfExists('bps_requests');
        Schema::dropIfExists('bps_sub_data');
        Schema::dropIfExists('bps_master_data');
    }
};
