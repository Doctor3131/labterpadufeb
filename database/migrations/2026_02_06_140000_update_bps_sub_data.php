<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing sub data
        DB::table('bps_sub_data')->truncate();

        // Get master IDs
        $masters = DB::table('bps_master_data')->pluck('id', 'code')->toArray();

        $subData = [
            // PODES (ID: 1)
            ['master_id' => $masters['PODES'], 'name' => 'Potensi Desa Tahun 2021'],
            ['master_id' => $masters['PODES'], 'name' => 'Potensi Desa Tahun 2024'],

            // STPIM (ID: 2)
            ['master_id' => $masters['STPIM'], 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2017 (KBLI 2 Digit Tanpa Informasi Provinsi)'],
            ['master_id' => $masters['STPIM'], 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2018'],
            ['master_id' => $masters['STPIM'], 'name' => 'Survei Tahunan Perusahaan Industri Manufaktur 2019 (KBLI 2 Digit Tanpa Informasi Provinsi)'],

            // SUSENAS (ID: 3)
            ['master_id' => $masters['SUSENAS'], 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret KOR'],
            ['master_id' => $masters['SUSENAS'], 'name' => 'Survei Sosial Ekonomi Nasional 2023 Maret (Modul Konsumsi dan Pengeluaran)'],
            ['master_id' => $masters['SUSENAS'], 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (KOR)'],
            ['master_id' => $masters['SUSENAS'], 'name' => 'Survei Sosial Ekonomi Nasional 2024 Maret (Modul Konsumsi dan Pengeluaran Variabel Terpilih)'],
            ['master_id' => $masters['SUSENAS'], 'name' => 'Survei Sosial Ekonomi Nasional 2024 September (MSBP)'],

            // SAKERNAS (ID: 4)
            ['master_id' => $masters['SAKERNAS'], 'name' => 'Survei Angkatan Kerja Nasional 2021 Agustus'],
            ['master_id' => $masters['SAKERNAS'], 'name' => 'Survei Angkatan Kerja Nasional 2022 Agustus'],
            ['master_id' => $masters['SAKERNAS'], 'name' => 'Survei Angkatan Kerja Nasional 2023 Agustus'],
            ['master_id' => $masters['SAKERNAS'], 'name' => 'Survei Angkatan Kerja Nasional 2024 Agustus'],

            // KOMUTER (ID: 5)
            ['master_id' => $masters['KOMUTER'], 'name' => 'Survei Komuter Jabodetabek'],
            ['master_id' => $masters['KOMUTER'], 'name' => 'Survei Komuter Banjarbakula 2023'],
            ['master_id' => $masters['KOMUTER'], 'name' => 'Survei Komuter Mamminasata 2023'],
            ['master_id' => $masters['KOMUTER'], 'name' => 'Survei Komuter Patungraya Agung 2023'],
            ['master_id' => $masters['KOMUTER'], 'name' => 'Survei Komuter Sarbagita 2023'],

            // IMK (ID: 6)
            ['master_id' => $masters['IMK'], 'name' => 'Survei Industri Mikro dan Kecil 2019 KBLI 2 Digit (Nasional)'],
            ['master_id' => $masters['IMK'], 'name' => 'Survei Industri Mikro dan Kecil 2022 KBLI 2 Digit (Nasional)'],
            ['master_id' => $masters['IMK'], 'name' => 'Survei Industri Mikro dan Kecil 2023 KBLI 2 Digit (Nasional)'],

            // PETA (ID: 7)
            ['master_id' => $masters['PETA'], 'name' => 'Peta Indonesia per Desa 2023'],
            ['master_id' => $masters['PETA'], 'name' => 'Peta Indonesia per Desa 2024'],
        ];

        // Add timestamps
        $now = now();
        foreach ($subData as &$item) {
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('bps_sub_data')->insert($subData);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot fully reverse - would need to restore old data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bps_sub_data')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
