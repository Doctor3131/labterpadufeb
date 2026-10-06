<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('refinitiv_attendance_events')) {
            Schema::create('refinitiv_attendance_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('refinitiv_request_id')->constrained('refinitiv_requests')->restrictOnDelete();
                $table->string('from_status', 20)->nullable();
                $table->string('to_status', 20);
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->timestamp('recorded_at');
            });
        }

        if (! Schema::hasIndex('refinitiv_attendance_events', 'ref_attendance_request_recorded_idx')) {
            Schema::table('refinitiv_attendance_events', function (Blueprint $table) {
                $table->index(
                    ['refinitiv_request_id', 'recorded_at'],
                    'ref_attendance_request_recorded_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('refinitiv_attendance_events');
    }
};
