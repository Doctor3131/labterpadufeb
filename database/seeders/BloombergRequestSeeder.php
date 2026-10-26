<?php

namespace Database\Seeders;

use App\Models\BloombergRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BloombergRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Preview Bloomberg hanya boleh diisi pada environment local.');
        }

        $documentPath = 'bloomberg/statements/demo-preview.pdf';
        $disk = Storage::disk('public');

        if (! $disk->exists($documentPath)) {
            $disk->put($documentPath, $this->demoPdf());
        }

        $records = [
            [
                'type' => 'reservasi',
                'name' => 'Demo Mahasiswa 01',
                'nim_nip' => 'DEMO-001',
                'phone' => '0000000001',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Manajemen',
                'usage_date' => now()->addDays(2)->toDateString(),
                'session' => 'sesi_1',
                'purpose' => 'skripsi',
                'research_title' => 'Dampak Pergerakan Harga Saham terhadap Return',
                'variables' => 'Harga saham, return harian, volume transaksi, dan indeks LQ45',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Demo Mahasiswa 02',
                'nim_nip' => 'DEMO-002',
                'phone' => '0000000002',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Akuntansi',
                'usage_date' => now()->addDays(2)->toDateString(),
                'session' => 'sesi_2',
                'purpose' => 'tugas_mk',
                'subject_name' => 'Analisis Laporan Keuangan',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Demo Mahasiswa 03',
                'nim_nip' => 'DEMO-003',
                'phone' => '0000000003',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Ekonomi',
                'usage_date' => now()->addDays(3)->toDateString(),
                'session' => 'sesi_1',
                'purpose' => 'sertifikasi_bloomberg',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Demo Mahasiswa 04',
                'nim_nip' => 'DEMO-004',
                'phone' => '0000000004',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S2 Manajemen',
                'usage_date' => now()->addDays(3)->toDateString(),
                'session' => 'sesi_1',
                'purpose' => 'thesis',
                'research_title' => 'Analisis Kinerja Keuangan Perusahaan',
                'variables' => 'ROA, DER, dividend payout ratio, dan return saham',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Demo Dosen 05',
                'nim_nip' => 'DEMO-005',
                'phone' => '0000000005',
                'applicant_type' => 'dosen_undip',
                'usage_date' => now()->addDays(4)->toDateString(),
                'session' => 'sesi_2',
                'purpose' => 'penelitian_dosen',
                'lecturer_name' => 'Dosen Demo 05',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Demo Mahasiswa 06',
                'nim_nip' => 'DEMO-006',
                'phone' => '0000000006',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Ekonomi Islam',
                'usage_date' => now()->addDays(5)->toDateString(),
                'session' => 'sesi_1',
                'purpose' => 'lomba',
                'research_title' => 'Kompetisi Data Pasar Keuangan',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Demo Mahasiswa 07',
                'nim_nip' => 'DEMO-007',
                'phone' => '0000000007',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Bisnis Digital',
                'usage_date' => now()->addDays(5)->toDateString(),
                'session' => 'sesi_2',
                'purpose' => 'explore',
            ],
            [
                'type' => 'reservasi',
                'name' => 'Demo Pemohon Eksternal 08',
                'nim_nip' => 'DEMO-008',
                'phone' => '0000000008',
                'applicant_type' => 'dosen_non_undip',
                'study_program' => 'Program Demo',
                'university' => 'Universitas Demo Nusantara',
                'usage_date' => now()->addDays(6)->toDateString(),
                'session' => 'sesi_1',
                'purpose' => 'penelitian_dosen',
                'lecturer_name' => 'Pemohon Demo 08',
            ],
            [
                'type' => 'walk_in',
                'name' => 'Demo Walk-in 09',
                'nim_nip' => 'DEMO-009',
                'phone' => '0000000009',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Akuntansi',
                'usage_date' => today()->toDateString(),
                'session' => 'sesi_1',
                'purpose' => 'explore',
            ],
            [
                'type' => 'walk_in',
                'name' => 'Demo Walk-in 10',
                'nim_nip' => 'DEMO-010',
                'phone' => '0000000010',
                'applicant_type' => 'mahasiswa',
                'study_program' => 'S1 Manajemen',
                'usage_date' => today()->toDateString(),
                'session' => 'sesi_2',
                'purpose' => 'skripsi',
                'research_title' => 'Pengaruh Inflasi terhadap Keputusan Investasi',
                'variables' => 'Inflasi, suku bunga, kurs rupiah, dan indeks harga saham',
            ],
        ];

        foreach ($records as $index => $record) {
            $token = hash('sha256', 'labdigital-bloomberg-preview-'.($index + 1));

            BloombergRequest::updateOrCreate(
                ['token' => $token],
                $record + [
                    'token' => $token,
                    'statement_file' => $documentPath,
                ],
            );
        }

        $this->command?->info('10 data sintetis Bloomberg siap untuk preview lokal.');
    }

    private function demoPdf(): string
    {
        $stream = "BT\n/F1 18 Tf\n72 720 Td\n(Demo Bloomberg - Lokal) Tj\n/F1 11 Tf\n0 -24 Td\n(Data sintetis untuk preview antarmuka) Tj\nET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}endstream",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $crossReferenceOffset = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf
            .'trailer'."\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n"
            .'startxref'."\n{$crossReferenceOffset}\n%%EOF\n";
    }
}
