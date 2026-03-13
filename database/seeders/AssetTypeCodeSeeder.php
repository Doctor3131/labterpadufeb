<?php

namespace Database\Seeders;

use App\Models\AssetTypeCode;
use Illuminate\Database\Seeder;

class AssetTypeCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya tipe aset dengan STRUCTURED_TAG yang perlu kode tipe
        // Barang lain (Mouse, Keyboard, Router, dll) tidak pakai kode tipe
        $codes = [
            ['code' => 'H3', 'name' => 'PC AIO', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'I2', 'name' => 'TV', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'BRK', 'name' => 'Bracket', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'J1', 'name' => 'Speaker', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'O1', 'name' => 'Laptop', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'L1', 'name' => 'Printer', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'P', 'name' => 'Samsung Tab', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
        ];

        foreach ($codes as $code) {
            AssetTypeCode::updateOrCreate(
                ['code' => $code['code']],
                $code
            );
        }
    }
}
