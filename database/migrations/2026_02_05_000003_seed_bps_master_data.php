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
        // Master Data with sub-data (has_sub_data = true)
        $masterDataWithSub = [
            [
                'name' => 'Potensi Desa',
                'code' => 'PODES',
                'description' => 'Data Potensi Desa',
                'has_sub_data' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'STPIM - Survei Tahunan Perusahaan Industri Manufaktur',
                'code' => 'STPIM',
                'description' => 'Survei Tahunan Perusahaan Industri Manufaktur',
                'has_sub_data' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SUSENAS - Survei Sosial Ekonomi Nasional',
                'code' => 'SUSENAS',
                'description' => 'Survei Sosial Ekonomi Nasional',
                'has_sub_data' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SAKERNAS - Survei Angkatan Kerja Nasional',
                'code' => 'SAKERNAS',
                'description' => 'Survei Angkatan Kerja Nasional',
                'has_sub_data' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Survei Komuter',
                'code' => 'KOMUTER',
                'description' => 'Survei Komuter',
                'has_sub_data' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Survei Industri Mikro dan Kecil',
                'code' => 'IMK',
                'description' => 'Survei Industri Mikro dan Kecil',
                'has_sub_data' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peta Indonesia',
                'code' => 'PETA',
                'description' => 'Peta Indonesia',
                'has_sub_data' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Master Data without sub-data (single level - variable code entered directly)
        $masterDataSingleLevel = [
            [
                'name' => 'Survei Perilaku Anti Korupsi 2024',
                'code' => 'SPAK2024',
                'description' => 'Survei Perilaku Anti Korupsi 2024',
                'has_sub_data' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Survei Usaha atau Perusahaan E-Commerce 2024',
                'code' => 'ECOM2024',
                'description' => 'Survei Usaha atau Perusahaan E-Commerce 2024',
                'has_sub_data' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Survei E-Commerce 2023',
                'code' => 'ECOM2023',
                'description' => 'Survei E-Commerce 2023',
                'has_sub_data' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('bps_master_data')->insert(array_merge($masterDataWithSub, $masterDataSingleLevel));

        // Seed sub-data
        $podesId = DB::table('bps_master_data')->where('code', 'PODES')->value('id');
        $stpimId = DB::table('bps_master_data')->where('code', 'STPIM')->value('id');
        $susenasId = DB::table('bps_master_data')->where('code', 'SUSENAS')->value('id');
        $sakernasId = DB::table('bps_master_data')->where('code', 'SAKERNAS')->value('id');
        $komuterId = DB::table('bps_master_data')->where('code', 'KOMUTER')->value('id');
        $imkId = DB::table('bps_master_data')->where('code', 'IMK')->value('id');
        $petaId = DB::table('bps_master_data')->where('code', 'PETA')->value('id');

        // Potensi Desa
        if ($podesId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $podesId, 'name' => 'Potensi Desa Tahun 2024', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $podesId, 'name' => 'Potensi Desa Tahun 2021', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // STPIM
        if ($stpimId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2019 (KBLI 2 Digit Tanpa Informasi Provinsi)', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2018', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2017 (KBLI 2 Digit Tanpa Informasi Provinsi)', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // SUSENAS
        if ($susenasId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret (Modul Konsumsi dan Pengeluaran)', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (Modul Konsumsi dan Pengeluaran Variabel Terpilih)', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (KOR)', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 September (MSBP)', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret KOR', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // SAKERNAS
        if ($sakernasId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2021 Agustus', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2022 Agustus', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2023 Agustus', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2024 Agustus', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Survei Komuter
        if ($komuterId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Jabodetabek', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Banjarbakula 2023', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Mamminasata 2023', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Patungraya Agung 2023', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Sarbagita 2023', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Survei Industri Mikro dan Kecil
        if ($imkId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro Dan Kecil 2019 KBLI 2 Digit (Nasional)', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro dan Kecil 2022 KBLI 2 Digit (Nasional)', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro dan Kecil 2023 KBLI 2 Digit (Nasional)', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // Peta Indonesia
        if ($petaId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Desa 2024', 'created_at' => now(), 'updated_at' => now()],
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Desa 2023', 'created_at' => now(), 'updated_at' => now()],
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
