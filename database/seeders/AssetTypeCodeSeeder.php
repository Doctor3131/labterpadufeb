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
        $codes = [
            ['code' => 'H3', 'name' => 'PC AIO', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => false],
            ['code' => 'O1', 'name' => 'Laptop', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'I2', 'name' => 'TV', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => false],
            ['code' => 'BRK', 'name' => 'Bracket', 'default_tracking_mode' => 'AGGREGATE', 'is_borrowable' => false],
            ['code' => 'J1', 'name' => 'Speaker', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => false],
            ['code' => 'L1', 'name' => 'Printer', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'P', 'name' => 'Samsung Tab', 'default_tracking_mode' => 'STRUCTURED_TAG', 'is_borrowable' => true],
            ['code' => 'MOU', 'name' => 'Mouse', 'default_tracking_mode' => 'SEAT_NUMBER', 'is_borrowable' => false],
            ['code' => 'KEY', 'name' => 'Keyboard', 'default_tracking_mode' => 'SEAT_NUMBER', 'is_borrowable' => false],
            ['code' => 'DSK', 'name' => 'Meja', 'default_tracking_mode' => 'SEAT_NUMBER', 'is_borrowable' => false],
            ['code' => 'CHR', 'name' => 'Kursi', 'default_tracking_mode' => 'SEAT_NUMBER', 'is_borrowable' => false],
            ['code' => 'RTR', 'name' => 'Router', 'default_tracking_mode' => 'AGGREGATE', 'is_borrowable' => false],
            ['code' => 'SWT', 'name' => 'Switch', 'default_tracking_mode' => 'AGGREGATE', 'is_borrowable' => false],
            ['code' => 'AC', 'name' => 'AC', 'default_tracking_mode' => 'AGGREGATE', 'is_borrowable' => false],
            ['code' => 'WB', 'name' => 'Papan Tulis', 'default_tracking_mode' => 'AGGREGATE', 'is_borrowable' => false],
            ['code' => 'PJ', 'name' => 'Proyektor', 'default_tracking_mode' => 'AGGREGATE', 'is_borrowable' => true],
        ];

        foreach ($codes as $code) {
            AssetTypeCode::updateOrCreate(
                ['code' => $code['code']],
                $code
            );
        }
    }
}
