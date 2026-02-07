<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BpsDataSeeder extends Seeder
{
    /**
     * Run the database seeds for BPS Master Data and Sub Data.
     * 
     * Structure:
     * - Master Data with has_sub_data = true: User selects sub-data first, then inputs variable codes
     * - Master Data with has_sub_data = false: User directly inputs variable codes (single level)
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Clear existing data (in correct order due to FK constraints)
        DB::table('bps_request_variables')->delete();
        DB::table('bps_request_data')->delete();
        DB::table('bps_sub_data')->delete();
        DB::table('bps_master_data')->delete();

        // ============================================================
        // MASTER DATA
        // ============================================================
        $masterData = [
            // With sub-data
            ['name' => 'Peta Indonesia', 'code' => 'PETA', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Survei Industri Mikro dan Kecil', 'code' => 'IMK', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Survei Komuter', 'code' => 'KOMUTER', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'SAKERNAS - Survei Angkatan Kerja Nasional', 'code' => 'SAKERNAS', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'SUSENAS - Survei Sosial Ekonomi Nasional', 'code' => 'SUSENAS', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'STPIM - Survei Tahunan Perusahaan Industri Manufaktur', 'code' => 'STPIM', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Potensi Desa', 'code' => 'PODES', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Lain-lain', 'code' => 'LAINNYA', 'has_sub_data' => true, 'created_at' => $now, 'updated_at' => $now],
            
            // Without sub-data (single level)
            ['name' => 'Survei Perilaku Anti Korupsi 2024', 'code' => 'SPAK2024', 'has_sub_data' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Survei Usaha atau Perusahaan E-Commerce 2024', 'code' => 'ECOM2024', 'has_sub_data' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Survei E-Commerce 2023', 'code' => 'ECOM2023', 'has_sub_data' => false, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('bps_master_data')->insert($masterData);

        // ============================================================
        // SUB DATA
        // ============================================================
        
        // Helper to get master ID
        $getMasterId = fn($code) => DB::table('bps_master_data')->where('code', $code)->value('id');

        // Peta Indonesia
        $petaId = $getMasterId('PETA');
        if ($petaId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Desa 2024', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Desa 2023', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Survei Industri Mikro dan Kecil
        $imkId = $getMasterId('IMK');
        if ($imkId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro Dan Kecil 2019 KBLI 2 Digit (Nasional)', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro dan Kecil 2022 KBLI 2 Digit (Nasional)', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro dan Kecil 2023 KBLI 2 Digit (Nasional)', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Survei Komuter
        $komuterId = $getMasterId('KOMUTER');
        if ($komuterId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Banjarbakula 2023', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Mamminasata 2023', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Patungraya Agung 2023', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Sarbagita 2023', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Jabodetabek', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // SAKERNAS
        $sakernasId = $getMasterId('SAKERNAS');
        if ($sakernasId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2021 Agustus', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2022 Agustus', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2023 Agustus', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2024 Agustus', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // SUSENAS
        $susenasId = $getMasterId('SUSENAS');
        if ($susenasId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret (Modul Konsumsi dan Pengeluaran)', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (Modul Konsumsi dan Pengeluaran Variabel Terpilih)', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (KOR)', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 September (MSBP)', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret KOR', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // STPIM
        $stpimId = $getMasterId('STPIM');
        if ($stpimId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2019 (KBLI 2 Digit Tanpa Informasi Provinsi)', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2018', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2017 (KBLI 2 Digit Tanpa Informasi Provinsi)', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Potensi Desa
        $podesId = $getMasterId('PODES');
        if ($podesId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $podesId, 'name' => 'Potensi Desa Tahun 2024', 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $podesId, 'name' => 'Potensi Desa Tahun 2021', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Lain-lain (placeholder for uncategorized data)
        $lainnyaId = $getMasterId('LAINNYA');
        if ($lainnyaId) {
            // Add sub-data here as needed
        }

        $this->command->info('BPS Master Data and Sub Data seeded successfully!');
        $this->command->info('Master Data: ' . DB::table('bps_master_data')->count() . ' records');
        $this->command->info('Sub Data: ' . DB::table('bps_sub_data')->count() . ' records');
    }
}
