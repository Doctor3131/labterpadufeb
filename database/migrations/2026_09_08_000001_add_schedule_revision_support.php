<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->uuid('series_uuid')->nullable()->after('id')->index();
            $table->foreignId('parent_schedule_id')
                ->nullable()
                ->after('series_uuid')
                ->constrained('schedules')
                ->nullOnDelete();
            $table->unsignedInteger('revision_number')->default(1)->after('parent_schedule_id');
            $table->index(['series_uuid', 'start_date', 'end_date'], 'schedules_series_dates_index');
        });

        DB::table('schedules')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($schedules) {
                foreach ($schedules as $schedule) {
                    DB::table('schedules')
                        ->where('id', $schedule->id)
                        ->update(['series_uuid' => (string) Str::uuid()]);
                }
            });

        Schema::table('schedule_occurrences', function (Blueprint $table) {
            $table->date('override_date')->nullable()->after('occurrence_date')->index();
            $table->text('change_reason')->nullable()->after('end_time');
            $table->foreignId('changed_by')
                ->nullable()
                ->after('change_reason')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::create('schedule_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('schedule_occurrence_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('series_uuid')->nullable()->index();
            $table->string('action', 50);
            $table->string('scope', 20);
            $table->date('effective_date')->nullable();
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['series_uuid', 'effective_date'], 'schedule_change_logs_series_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_change_logs');

        Schema::table('schedule_occurrences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('changed_by');
            $table->dropColumn(['override_date', 'change_reason']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('schedules_series_dates_index');
            $table->dropConstrainedForeignId('parent_schedule_id');
            $table->dropColumn(['series_uuid', 'revision_number']);
        });
    }
};
