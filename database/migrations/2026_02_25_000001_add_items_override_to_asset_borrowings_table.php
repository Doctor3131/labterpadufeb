<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_borrowings', function (Blueprint $table) {
            // JSON column to store admin-edited item rows for the PDF
            // Format: [{ name, brand_type, quantity, condition_good, condition_adequate, condition_complete, remarks }]
            $table->json('items_override')->nullable()->after('generated_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('asset_borrowings', function (Blueprint $table) {
            $table->dropColumn('items_override');
        });
    }
};
