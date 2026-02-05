<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BpsRequest;
use App\Models\RefinitivRequest;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Booking type labels for display
     */
    private $typeLabels = [
        'perkuliahan_tetap' => 'Perkuliahan Tetap',
        'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
        'non_perkuliahan' => 'Non-Perkuliahan',
        'pribadi' => 'Pribadi',
    ];

    /**
     * Status labels for display
     */
    private $statusLabels = [
        'approved' => 'Disetujui',
        'pending' => 'Menunggu',
        'rejected' => 'Ditolak',
        'completed' => 'Selesai',
    ];

    /**
     * Report type labels
     */
    private $reportTypes = [
        'lab' => 'Peminjaman Lab',
        'bps' => 'Permohonan Data BPS',
        'refinitiv' => 'Permohonan Data Refinitiv',
    ];

    /**
     * Build base query with common filters for Lab bookings
     */
    private function buildLabQuery(Request $request)
    {
        $query = Booking::with('lab')
            ->whereIn('status', ['approved', 'pending', 'rejected']);

        if ($request->filled('start_month')) {
            $startDate = Carbon::parse($request->start_month . '-01')->startOfMonth();
            $query->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_month')) {
            $endDate = Carbon::parse($request->end_month . '-01')->endOfMonth();
            $query->where('created_at', '<=', $endDate);
        }

        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        if ($request->filled('type')) {
            $query->where('booking_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Build base query for BPS requests
     */
    private function buildBpsQuery(Request $request)
    {
        $query = BpsRequest::query();

        if ($request->filled('start_month')) {
            $startDate = Carbon::parse($request->start_month . '-01')->startOfMonth();
            $query->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_month')) {
            $endDate = Carbon::parse($request->end_month . '-01')->endOfMonth();
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Build base query for Refinitiv requests
     */
    private function buildRefinitivQuery(Request $request)
    {
        $query = RefinitivRequest::query();

        if ($request->filled('start_month')) {
            $startDate = Carbon::parse($request->start_month . '-01')->startOfMonth();
            $query->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_month')) {
            $endDate = Carbon::parse($request->end_month . '-01')->endOfMonth();
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Generate filename based on report type and date range
     */
    private function generateFilename($reportType, $startMonth, $endMonth, $extension)
    {
        $typeNames = [
            'lab' => 'laporan_peminjaman_lab',
            'bps' => 'laporan_bps',
            'refinitiv' => 'laporan_refinitiv',
        ];

        $filename = $typeNames[$reportType] ?? 'laporan';
        
        if ($startMonth && $endMonth) {
            $filename .= '_' . $startMonth . '_sd_' . $endMonth;
        } elseif ($startMonth) {
            $filename .= '_dari_' . $startMonth;
        } elseif ($endMonth) {
            $filename .= '_sampai_' . $endMonth;
        } else {
            $filename .= '_semua';
        }
        
        return $filename . '.' . $extension;
    }

    /**
     * Display the combined report page
     */
    public function index(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:lab,bps,refinitiv',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
            'lab_id' => 'nullable|exists:labs,id',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'status' => 'nullable|in:approved,pending,rejected',
        ]);

        $reportType = $request->get('report_type', 'lab');
        $data = collect();
        
        if ($reportType === 'lab') {
            $query = $this->buildLabQuery($request);
            $data = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        } elseif ($reportType === 'bps') {
            $query = $this->buildBpsQuery($request);
            $data = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        } elseif ($reportType === 'refinitiv') {
            $query = $this->buildRefinitivQuery($request);
            $data = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        }

        $labs = Lab::orderBy('name')->get();
        $types = $this->typeLabels;
        $reportTypes = $this->reportTypes;

        return view('admin.reports.index', compact('data', 'labs', 'types', 'reportType', 'reportTypes'));
    }

    /**
     * Export to CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:lab,bps,refinitiv',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
        ]);

        $reportType = $request->get('report_type', 'lab');
        $filename = $this->generateFilename($reportType, $request->start_month, $request->end_month, 'csv');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        if ($reportType === 'lab') {
            return $this->exportLabCsv($request, $headers);
        } elseif ($reportType === 'bps') {
            return $this->exportBpsCsv($request, $headers);
        } elseif ($reportType === 'refinitiv') {
            return $this->exportRefinitivCsv($request, $headers);
        }
    }

    /**
     * Export Lab bookings to CSV
     */
    private function exportLabCsv(Request $request, $headers)
    {
        $bookings = $this->buildLabQuery($request)->orderBy('created_at', 'desc')->get();

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Tanggal Booking Dibuat',
                'Nama Peminjam',
                'Tipe Peminjaman',
                'Laboratorium',
                'Hari Peminjaman',
                'Tanggal Peminjaman',
                'Jam Mulai',
                'Jam Selesai',
                'Jumlah Peserta',
                'Status',
            ]);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    Carbon::parse($booking->created_at)->format('d/m/Y H:i'),
                    $booking->pic_name,
                    $this->typeLabels[$booking->booking_type] ?? $booking->booking_type,
                    $booking->lab->name ?? '-',
                    $booking->day,
                    Carbon::parse($booking->booking_date)->format('d/m/Y'),
                    Carbon::parse($booking->start_time)->format('H:i'),
                    Carbon::parse($booking->end_time)->format('H:i'),
                    $booking->participant_count,
                    $this->statusLabels[$booking->status] ?? $booking->status,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export BPS requests to CSV
     */
    private function exportBpsCsv(Request $request, $headers)
    {
        $requests = $this->buildBpsQuery($request)->orderBy('created_at', 'desc')->get();

        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'Timestamp',
                'Nama',
                'NIM/NIP',
                'Program Studi',
                'Keperluan Penggunaan Data',
            ]);

            $no = 1;
            foreach ($requests as $req) {
                $identifier = $req->applicant_type === 'mahasiswa' ? $req->nim : $req->nip;
                $purpose = $req->purpose === 'Lainnya' ? $req->purpose_other : $req->purpose;
                
                fputcsv($file, [
                    $no++,
                    Carbon::parse($req->created_at)->format('d/m/Y H:i:s'),
                    $req->name,
                    $identifier ?? '-',
                    $req->study_program ?? '-',
                    $purpose,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export Refinitiv requests to CSV
     */
    private function exportRefinitivCsv(Request $request, $headers)
    {
        $requests = $this->buildRefinitivQuery($request)->orderBy('created_at', 'desc')->get();

        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No',
                'Nama',
                'NIM/NIP',
                'Program Studi',
                'Keperluan Penggunaan Data',
                'Tanggal Pemakaian',
            ]);

            $no = 1;
            foreach ($requests as $req) {
                $purpose = $req->purpose === 'lainnya' ? $req->purpose_other : (RefinitivRequest::PURPOSES[$req->purpose] ?? $req->purpose);
                $session = RefinitivRequest::SESSIONS[$req->session] ?? $req->session;
                
                fputcsv($file, [
                    $no++,
                    $req->name,
                    $req->nim_nip ?? '-',
                    $req->study_program ?? '-',
                    $purpose,
                    Carbon::parse($req->usage_date)->format('d/m/Y') . ' (' . $session . ')',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export to Word document
     */
    public function exportWord(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:lab,bps,refinitiv',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
        ]);

        $reportType = $request->get('report_type', 'lab');
        $filename = $this->generateFilename($reportType, $request->start_month, $request->end_month, 'doc');

        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        if ($reportType === 'lab') {
            return $this->exportLabWord($request, $headers);
        } elseif ($reportType === 'bps') {
            return $this->exportBpsWord($request, $headers);
        } elseif ($reportType === 'refinitiv') {
            return $this->exportRefinitivWord($request, $headers);
        }
    }

    /**
     * Generate date range text
     */
    private function getDateRangeText($startMonth, $endMonth)
    {
        if ($startMonth && $endMonth) {
            return Carbon::parse($startMonth)->locale('id')->isoFormat('MMMM Y') . ' - ' . Carbon::parse($endMonth)->locale('id')->isoFormat('MMMM Y');
        } elseif ($startMonth) {
            return 'Dari ' . Carbon::parse($startMonth)->locale('id')->isoFormat('MMMM Y');
        } elseif ($endMonth) {
            return 'Sampai ' . Carbon::parse($endMonth)->locale('id')->isoFormat('MMMM Y');
        }
        return 'Semua Data';
    }

    /**
     * Export Lab bookings to Word
     */
    private function exportLabWord(Request $request, $headers)
    {
        $bookings = $this->buildLabQuery($request)->orderBy('created_at', 'desc')->get();
        $dateRangeText = $this->getDateRangeText($request->start_month, $request->end_month);

        $html = $this->getWordHeader('LAPORAN PEMINJAMAN LABORATORIUM', 'Lab Digital FEB UNDIP', $dateRangeText, $bookings->count() . ' peminjaman');
        
        $html .= '
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Dibuat</th>
                        <th>Nama Peminjam</th>
                        <th>Tipe</th>
                        <th>Lab</th>
                        <th>Hari</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Peserta</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        foreach ($bookings as $booking) {
            $html .= '
                    <tr>
                        <td class="center">' . $no++ . '</td>
                        <td>' . Carbon::parse($booking->created_at)->format('d/m/Y H:i') . '</td>
                        <td>' . htmlspecialchars($booking->pic_name) . '</td>
                        <td>' . htmlspecialchars($this->typeLabels[$booking->booking_type] ?? $booking->booking_type) . '</td>
                        <td>' . htmlspecialchars($booking->lab->name ?? '-') . '</td>
                        <td class="center">' . htmlspecialchars($booking->day) . '</td>
                        <td class="center">' . Carbon::parse($booking->booking_date)->format('d/m/Y') . '</td>
                        <td class="center">' . Carbon::parse($booking->start_time)->format('H:i') . '-' . Carbon::parse($booking->end_time)->format('H:i') . '</td>
                        <td class="center">' . $booking->participant_count . '</td>
                        <td class="center">' . htmlspecialchars($this->statusLabels[$booking->status] ?? $booking->status) . '</td>
                    </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return Response::make($html, 200, $headers);
    }

    /**
     * Export BPS requests to Word
     */
    private function exportBpsWord(Request $request, $headers)
    {
        $requests = $this->buildBpsQuery($request)->orderBy('created_at', 'desc')->get();
        $dateRangeText = $this->getDateRangeText($request->start_month, $request->end_month);

        $html = $this->getWordHeader('REKAPITULASI PELAYANAN AKSES DATA BPS', 'Lab Digital FEB UNDIP', $dateRangeText, $requests->count() . ' permohonan');
        
        $html .= '
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Timestamp</th>
                        <th>Nama</th>
                        <th>NIM/NIP</th>
                        <th>Program Studi</th>
                        <th>Keperluan Penggunaan Data</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        foreach ($requests as $req) {
            $identifier = $req->applicant_type === 'mahasiswa' ? $req->nim : $req->nip;
            $purpose = $req->purpose === 'Lainnya' ? $req->purpose_other : $req->purpose;
            
            $html .= '
                    <tr>
                        <td class="center">' . $no++ . '</td>
                        <td>' . Carbon::parse($req->created_at)->format('d/m/Y H:i:s') . '</td>
                        <td>' . htmlspecialchars($req->name) . '</td>
                        <td>' . htmlspecialchars($identifier ?? '-') . '</td>
                        <td>' . htmlspecialchars($req->study_program ?? '-') . '</td>
                        <td>' . htmlspecialchars($purpose) . '</td>
                    </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return Response::make($html, 200, $headers);
    }

    /**
     * Export Refinitiv requests to Word
     */
    private function exportRefinitivWord(Request $request, $headers)
    {
        $requests = $this->buildRefinitivQuery($request)->orderBy('usage_date', 'desc')->get();
        $dateRangeText = $this->getDateRangeText($request->start_month, $request->end_month);

        $html = $this->getWordHeader('REKAPITULASI PELAYANAN DATA REFINITIV', 'Lab Digital FEB UNDIP', $dateRangeText, $requests->count() . ' permohonan');
        
        $html .= '
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIM/NIP</th>
                        <th>Program Studi</th>
                        <th>Keperluan Penggunaan Data</th>
                        <th>Tanggal Pemakaian</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        foreach ($requests as $req) {
            $purpose = $req->purpose === 'lainnya' ? $req->purpose_other : (RefinitivRequest::PURPOSES[$req->purpose] ?? $req->purpose);
            $session = RefinitivRequest::SESSIONS[$req->session] ?? $req->session;
            
            $html .= '
                    <tr>
                        <td class="center">' . $no++ . '</td>
                        <td>' . htmlspecialchars($req->name) . '</td>
                        <td>' . htmlspecialchars($req->nim_nip ?? '-') . '</td>
                        <td>' . htmlspecialchars($req->study_program ?? '-') . '</td>
                        <td>' . htmlspecialchars($purpose) . '</td>
                        <td class="center">' . Carbon::parse($req->usage_date)->format('d/m/Y') . ' (' . $session . ')</td>
                    </tr>';
        }

        $html .= '</tbody></table></body></html>';

        return Response::make($html, 200, $headers);
    }

    /**
     * Get Word document header HTML
     */
    private function getWordHeader($title, $subtitle, $dateRange, $totalData)
    {
        return '
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: "Times New Roman", Times, serif; font-size: 12pt; }
                h1 { text-align: center; font-size: 16pt; margin-bottom: 5px; }
                h2 { text-align: center; font-size: 14pt; font-weight: normal; margin-top: 0; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #1F4E79; color: white; padding: 8px; border: 1px solid #000; font-size: 10pt; }
                td { padding: 6px; border: 1px solid #000; font-size: 10pt; }
                tr:nth-child(even) { background-color: #f2f2f2; }
                .center { text-align: center; }
                .info { margin-bottom: 10px; font-size: 11pt; }
            </style>
        </head>
        <body>
            <h1>' . htmlspecialchars($title) . '</h1>
            <h2>' . htmlspecialchars($subtitle) . '</h2>
            <p class="info"><strong>Periode:</strong> ' . htmlspecialchars($dateRange) . '</p>
            <p class="info"><strong>Tanggal Cetak:</strong> ' . Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') . ' WIB</p>
            <p class="info"><strong>Total Data:</strong> ' . htmlspecialchars($totalData) . '</p>';
    }
}
