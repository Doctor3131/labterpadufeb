<?php

namespace Database\Seeders;

use App\Models\BloombergRequest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BloombergRequestSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // === 8 Reservasi ===
            [
                'type' => 'reservasi',
                'name' => 'Ahmad Rizky Pratama',
                'nim_nip' => '12030122140001',
                'phone' => '081234567890',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Manajemen',
                'usage_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'session' => 'sesi_1',
                'purpose' => 'skripsi',
                'research_title' => 'Analisis Pengaruh Volatilitas Pasar Modal Terhadap Return Saham LQ45',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Siti Nurhaliza',
                'nim_nip' => '12030122140002',
                'phone' => '082345678901',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Akuntansi',
                'usage_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'session' => 'sesi_2',
                'purpose' => 'tugas_mk',
                'subject_name' => 'Analisis Laporan Keuangan',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Budi Santoso',
                'nim_nip' => '12030122140003',
                'phone' => '083456789012',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Ekonomi',
                'usage_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'session' => 'sesi_1',
                'purpose' => 'sertifikasi_bloomberg',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Diana Putri Maharani',
                'nim_nip' => '12030122140004',
                'phone' => '084567890123',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S2 Manajemen',
                'usage_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'session' => 'sesi_1',
                'purpose' => 'thesis',
                'research_title' => 'Studi Komparatif Kinerja Reksa Dana Saham dan ETF di Indonesia',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Prof. Dr. Imam Ghozali',
                'nim_nip' => '196205141988031003',
                'phone' => '085678901234',
                'applicant_type' => 'dosen_undip',
                'usage_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'session' => 'sesi_2',
                'purpose' => 'penelitian_dosen',
                'lecturer_name' => 'Prof. Dr. Imam Ghozali',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Fajar Dwi Nugroho',
                'nim_nip' => '12030122140005',
                'phone' => '086789012345',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Ekonomi Islam',
                'usage_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'session' => 'sesi_1',
                'purpose' => 'lomba',
                'research_title' => 'Bloomberg Global Trading Challenge 2026',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Galih Pramudya',
                'nim_nip' => '12030122140006',
                'phone' => '087890123456',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Manajemen',
                'usage_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'session' => 'sesi_2',
                'purpose' => 'explore',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Dr. Rina Andriani',
                'nim_nip' => '197803221005012001',
                'phone' => '088901234567',
                'applicant_type' => 'dosen_non_undip',
                'usage_date' => Carbon::now()->addDays(6)->format('Y-m-d'),
                'session' => 'sesi_1',
                'purpose' => 'penelitian_dosen',
                'lecturer_name' => 'Dr. Rina Andriani',
            ],

            // === 2 Sudah di Tempat (walk_in) ===
            [
                'type' => 'walk_in',
                'name' => 'Hendra Wijaya',
                'nim_nip' => '12030122140007',
                'phone' => '089012345678',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Akuntansi',
                'usage_date' => Carbon::now()->format('Y-m-d'),
                'session' => 'sesi_1',
                'purpose' => 'explore',
            ],
            [
                'type' => 'walk_in',
                'name' => 'Indah Permatasari',
                'nim_nip' => '12030122140008',
                'phone' => '081123456789',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Bisnis Digital',
                'usage_date' => Carbon::now()->format('Y-m-d'),
                'session' => 'sesi_2',
                'purpose' => 'skripsi',
                'research_title' => 'Implementasi Strategi Hedging Menggunakan Derivatif pada Perusahaan Manufaktur',
            ],
        ];

        foreach ($data as $item) {
            BloombergRequest::create(array_merge($item, [
                'token' => BloombergRequest::generateToken(),
                'statement_file' => 'bloomberg/statements/dummy_surat.pdf',
            ]));
        }

        $this->command->info('✅ 10 Bloomberg requests seeded (8 reservasi + 2 walk-in)');
    }
}
