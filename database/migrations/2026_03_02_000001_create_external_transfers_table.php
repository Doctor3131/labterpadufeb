<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Lab;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the Eksternal lab/room if it doesn't exist
        if (!Lab::where('name', 'Eksternal')->exists()) {
            Lab::create([
                'name' => 'Eksternal',
                'description' => 'Ruangan eksternal untuk barang yang ditransfer keluar dari Gudang',
                'capacity' => 0,
                'status' => 'available',
            ]);
        }

        Schema::create('external_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('recipient');
            $table->date('transfer_date');
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('source_lab_id')->constrained('labs');
            $table->foreignId('target_lab_id')->constrained('labs');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tracking_mode');
            $table->string('condition')->nullable();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_transfers');
    }
};
