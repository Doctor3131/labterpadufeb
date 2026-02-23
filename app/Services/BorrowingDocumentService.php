<?php

namespace App\Services;

use App\Models\AssetBorrowing;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class BorrowingDocumentService
{
    /**
     * Generate nomor surat peminjaman barang
     * Format: XXX/SPB/UPKFEB/ROMAN_MONTH/YEAR
     */
    public function generateDocumentNumber(): string
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        
        // Hitung jumlah surat di bulan ini
        $count = AssetBorrowing::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->whereNotNull('document_number')
            ->count() + 1;
        
        // Konversi bulan ke angka romawi
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        
        $romanMonth = $romanMonths[$currentMonth];
        
        // Format: 003/SPB/UPKFEB/IX/2025
        return sprintf('%03d/SPB/UPKFEB/%s/%d', $count, $romanMonth, $currentYear);
    }
    
    /**
     * Generate PDF Surat Peminjaman Barang
     */
    public function generatePDF(AssetBorrowing $borrowing): string
    {
        // Load borrowed items with relationships
        $borrowing->load([
            'borrowedItems.item', 
            'borrowedItems.assetUnit.batch', 
            'borrowedItems.inventoryBalance.batch',
            'lab'
        ]);
        
        // Generate nomor surat jika belum ada
        if (!$borrowing->document_number) {
            $borrowing->document_number = $this->generateDocumentNumber();
            $borrowing->save();
        }
        
        // Set document date jika belum ada
        if (!$borrowing->document_date) {
            $borrowing->document_date = Carbon::now();
            $borrowing->save();
        }
        
        // Prepare data for PDF
        $data = [
            'borrowing' => $borrowing,
            'items' => $borrowing->borrowedItems,
            'documentDate' => $this->formatIndonesianDate($borrowing->document_date),
            'documentFullDate' => $this->formatFullIndonesianDate($borrowing->document_date),
        ];
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.borrowing-document', $data);
        $pdf->setPaper('a4', 'portrait');
        
        // Save PDF
        $filename = 'surat-peminjaman-' . $borrowing->id . '-' . time() . '.pdf';
        $path = 'borrowing-documents/' . $filename;
        
        Storage::put('public/' . $path, $pdf->output());
        
        // Update path di database
        $borrowing->generated_document_path = $path;
        $borrowing->save();
        
        return $path;
    }
    
    /**
     * Format tanggal ke format Indonesia
     */
    public function formatIndonesianDate($date): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $carbonDate = Carbon::parse($date);
        $day = $carbonDate->day;
        $month = $months[$carbonDate->month];
        $year = $carbonDate->year;
        
        return "{$day} {$month} {$year}";
    }
    
    /**
     * Format tanggal lengkap dengan hari dalam bahasa Indonesia
     * Format: "Rabu tanggal 12 bulan Februari tahun 2026"
     */
    public function formatFullIndonesianDate($date): string
    {
        $days = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $carbonDate = Carbon::parse($date);
        $dayName = $days[$carbonDate->dayOfWeek];
        $day = $carbonDate->day;
        $month = $months[$carbonDate->month];
        $year = $carbonDate->year;
        
        return "{$dayName} tanggal {$day} bulan {$month} tahun {$year}";
    }
    
    /**
     * Update data PIHAK PERTAMA (Admin/Penanggung Jawab)
     */
    public function updateFirstPartyData(AssetBorrowing $borrowing, array $data): void
    {
        $updateData = [
            'first_party_name' => $data['first_party_name'],
            'first_party_position' => $data['first_party_position'],
            'first_party_address' => $data['first_party_address'],
            'first_party_phone' => $data['first_party_phone'],
            'document_date' => $data['document_date'] ?? Carbon::now(),
        ];

        // Jika admin mengisi nomor surat manual, gunakan itu
        if (!empty($data['document_number'])) {
            $updateData['document_number'] = $data['document_number'];
        }

        $borrowing->update($updateData);
    }
}
