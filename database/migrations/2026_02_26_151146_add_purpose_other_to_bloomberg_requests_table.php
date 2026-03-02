<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bloomberg_requests', function (Blueprint $table) {
            $table->string('purpose_other')->nullable()->after('purpose');
        });
    }

    public function down(): void
    {
        Schema::table('bloomberg_requests', function (Blueprint $table) {
            $table->dropColumn('purpose_other');
        });
    }
};
