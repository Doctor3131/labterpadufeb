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

        // Clear existing data
        DB::table('bps_request_variables')->delete();
        DB::table('bps_request_data')->delete();
        DB::table('bps_sub_data')->delete();
        DB::table('bps_master_data')->delete();

        // ============================================================
        // MASTER DATA WITH SUB-DATA (Multi Level)
        // ============================================================
        $masterDataWithSub = [
            [
                'name' => 'Potensi Desa',
                'code' => 'PODES',
                'description' => 'Data Potensi Desa - survei yang mengumpulkan data tentang ketersediaan fasilitas, infrastruktur, dan potensi ekonomi desa',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'STPIM - Survei Tahunan Perusahaan Industri Manufaktur',
                'code' => 'STPIM',
                'description' => 'Survei Tahunan Perusahaan Industri Manufaktur - mengumpulkan data produksi, tenaga kerja, dan keuangan perusahaan industri besar dan sedang',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'SUSENAS - Survei Sosial Ekonomi Nasional',
                'code' => 'SUSENAS',
                'description' => 'Survei Sosial Ekonomi Nasional - survei rumah tangga yang mengumpulkan data tentang kondisi sosial ekonomi masyarakat',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'SAKERNAS - Survei Angkatan Kerja Nasional',
                'code' => 'SAKERNAS',
                'description' => 'Survei Angkatan Kerja Nasional - survei untuk mengumpulkan data ketenagakerjaan di Indonesia',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Survei Komuter',
                'code' => 'KOMUTER',
                'description' => 'Survei Komuter - mengumpulkan data tentang pola perjalanan komuter di wilayah metropolitan',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Survei Industri Mikro dan Kecil',
                'code' => 'IMK',
                'description' => 'Survei Industri Mikro dan Kecil - mengumpulkan data tentang karakteristik usaha industri mikro dan kecil',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Peta Indonesia',
                'code' => 'PETA',
                'description' => 'Peta Indonesia - data shapefile dan geospasial wilayah administrasi Indonesia',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Lain-lain',
                'code' => 'LAINNYA',
                'description' => 'Kategori lain-lain untuk data BPS yang tidak termasuk dalam kategori utama',
                'is_active' => true,
                'has_sub_data' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // ============================================================
        // MASTER DATA WITHOUT SUB-DATA (Single Level)
        // User directly inputs variable codes without selecting sub-data
        // ============================================================
        $masterDataSingleLevel = [
            [
                'name' => 'Survei Perilaku Anti Korupsi 2024',
                'code' => 'SPAK2024',
                'description' => 'Survei Perilaku Anti Korupsi 2024 - mengukur persepsi dan perilaku masyarakat terhadap korupsi',
                'is_active' => true,
                'has_sub_data' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Survei Usaha atau Perusahaan E-Commerce 2024',
                'code' => 'ECOM2024',
                'description' => 'Survei Usaha atau Perusahaan E-Commerce 2024 - data tentang usaha e-commerce di Indonesia',
                'is_active' => true,
                'has_sub_data' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Survei E-Commerce 2023',
                'code' => 'ECOM2023',
                'description' => 'Survei E-Commerce 2023 - data tentang usaha e-commerce di Indonesia tahun 2023',
                'is_active' => true,
                'has_sub_data' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert all master data
        DB::table('bps_master_data')->insert(array_merge($masterDataWithSub, $masterDataSingleLevel));

        // ============================================================
        // SUB DATA FOR EACH MASTER
        // ============================================================
        
        // Get master IDs
        $podesId = DB::table('bps_master_data')->where('code', 'PODES')->value('id');
        $stpimId = DB::table('bps_master_data')->where('code', 'STPIM')->value('id');
        $susenasId = DB::table('bps_master_data')->where('code', 'SUSENAS')->value('id');
        $sakernasId = DB::table('bps_master_data')->where('code', 'SAKERNAS')->value('id');
        $komuterId = DB::table('bps_master_data')->where('code', 'KOMUTER')->value('id');
        $imkId = DB::table('bps_master_data')->where('code', 'IMK')->value('id');
        $petaId = DB::table('bps_master_data')->where('code', 'PETA')->value('id');
        $lainnyaId = DB::table('bps_master_data')->where('code', 'LAINNYA')->value('id');

        // PODES Sub Data
        if ($podesId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $podesId, 'name' => 'Potensi Desa 2024', 'description' => 'Data Potensi Desa tahun 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $podesId, 'name' => 'Potensi Desa 2021', 'description' => 'Data Potensi Desa tahun 2021', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $podesId, 'name' => 'Potensi Desa 2018', 'description' => 'Data Potensi Desa tahun 2018', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $podesId, 'name' => 'Potensi Desa 2014', 'description' => 'Data Potensi Desa tahun 2014', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $podesId, 'name' => 'Potensi Desa 2011', 'description' => 'Data Potensi Desa tahun 2011', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // STPIM Sub Data
        if ($stpimId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2021', 'description' => 'STPIM tahun 2021', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2019 (KBLI 2 Digit Tanpa Informasi Provinsi)', 'description' => 'STPIM tahun 2019 KBLI 2 Digit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2018', 'description' => 'STPIM tahun 2018', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $stpimId, 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2017 (KBLI 2 Digit Tanpa Informasi Provinsi)', 'description' => 'STPIM tahun 2017 KBLI 2 Digit', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // SUSENAS Sub Data
        if ($susenasId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (Modul Konsumsi dan Pengeluaran Variabel Terpilih)', 'description' => 'SUSENAS Maret 2024 Modul Konsumsi', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (KOR)', 'description' => 'SUSENAS Maret 2024 KOR', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2024 September (MSBP)', 'description' => 'SUSENAS September 2024 MSBP', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret (Modul Konsumsi dan Pengeluaran)', 'description' => 'SUSENAS Maret 2023 Modul Konsumsi', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret (KOR)', 'description' => 'SUSENAS Maret 2023 KOR', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2022 Maret (KOR)', 'description' => 'SUSENAS Maret 2022 KOR', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $susenasId, 'name' => 'Survei Sosial Ekonomi Nasional 2021 Maret (KOR)', 'description' => 'SUSENAS Maret 2021 KOR', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // SAKERNAS Sub Data
        if ($sakernasId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2024 Agustus', 'description' => 'SAKERNAS Agustus 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2024 Februari', 'description' => 'SAKERNAS Februari 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2023 Agustus', 'description' => 'SAKERNAS Agustus 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2023 Februari', 'description' => 'SAKERNAS Februari 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2022 Agustus', 'description' => 'SAKERNAS Agustus 2022', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $sakernasId, 'name' => 'Survei Angkatan Kerja Nasional 2021 Agustus', 'description' => 'SAKERNAS Agustus 2021', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Survei Komuter Sub Data
        if ($komuterId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Jabodetabek 2023', 'description' => 'Komuter Jabodetabek 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Jabodetabek 2019', 'description' => 'Komuter Jabodetabek 2019', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Banjarbakula 2023', 'description' => 'Komuter Banjarbakula 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Mamminasata 2023', 'description' => 'Komuter Mamminasata 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Patungraya Agung 2023', 'description' => 'Komuter Patungraya Agung 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $komuterId, 'name' => 'Survei Komuter Sarbagita 2023', 'description' => 'Komuter Sarbagita 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // IMK Sub Data
        if ($imkId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro dan Kecil 2023 KBLI 2 Digit (Nasional)', 'description' => 'IMK 2023 Nasional', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro dan Kecil 2022 KBLI 2 Digit (Nasional)', 'description' => 'IMK 2022 Nasional', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $imkId, 'name' => 'Survei Industri Mikro dan Kecil 2019 KBLI 2 Digit (Nasional)', 'description' => 'IMK 2019 Nasional', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Peta Indonesia Sub Data
        if ($petaId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Desa 2024', 'description' => 'Shapefile peta Indonesia per desa 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Desa 2023', 'description' => 'Shapefile peta Indonesia per desa 2023', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Kecamatan 2024', 'description' => 'Shapefile peta Indonesia per kecamatan 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Kabupaten/Kota 2024', 'description' => 'Shapefile peta Indonesia per kabupaten/kota 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $petaId, 'name' => 'Peta Indonesia per Provinsi 2024', 'description' => 'Shapefile peta Indonesia per provinsi 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // Lain-lain Sub Data (untuk data yang belum dikategorikan)
        if ($lainnyaId) {
            DB::table('bps_sub_data')->insert([
                ['master_id' => $lainnyaId, 'name' => 'Survei Pertanian Antar Sensus 2024', 'description' => 'SUTAS 2024', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['master_id' => $lainnyaId, 'name' => 'Sensus Penduduk 2020', 'description' => 'Sensus Penduduk 2020', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        $this->command->info('BPS Master Data and Sub Data seeded successfully!');
        $this->command->info('Master Data: ' . DB::table('bps_master_data')->count() . ' records');
        $this->command->info('Sub Data: ' . DB::table('bps_sub_data')->count() . ' records');
    }
}
