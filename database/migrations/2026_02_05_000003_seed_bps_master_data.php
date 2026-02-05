<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Seeds initial BPS master data categories
     */
    public function up(): void
    {
        $masterData = [
            [
                'name' => 'POTENSI DESA',
                'code' => 'PODES',
                'description' => 'Data Potensi Desa',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'STPIM - Survei Tahunan Perusahaan Industri Manufaktur',
                'code' => 'STPIM',
                'description' => 'Survei Tahunan Perusahaan Industri Manufaktur',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SUSENAS - Survei Sosial Ekonomi Nasional',
                'code' => 'SUSENAS',
                'description' => 'Survei Sosial Ekonomi Nasional',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SAKERNAS - Survei Angkatan Kerja Nasional',
                'code' => 'SAKERNAS',
                'description' => 'Survei Angkatan Kerja Nasional',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Survei Komuter',
                'code' => 'KOMUTER',
                'description' => 'Survei Komuter',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Survei Industri Mikro dan Kecil',
                'code' => 'IMK',
                'description' => 'Survei Industri Mikro dan Kecil',
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peta Indonesia',
                'code' => 'PETA',
                'description' => 'Peta Indonesia',
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lain-lain',
                'code' => 'LAINNYA',
                'description' => 'Data lainnya',
                'sort_order' => 99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('bps_master_data')->insert($masterData);

        // Seed some sample sub-data for STPIM and SUSENAS
        $stpimId = DB::table('bps_master_data')->where('code', 'STPIM')->value('id');
        $susenasId = DB::table('bps_master_data')->where('code', 'SUSENAS')->value('id');

        if ($stpimId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2017', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2018', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2019', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2021', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if ($susenasId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret KOR', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 September (MSBP)', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (KOR)', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (Modul Konsumsi dan Pengeluaran Variabel Terpilih)', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('bps_sub_data')->truncate();
        DB::table('bps_master_data')->truncate();
    }
};
