<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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
    ];

    /**
     * Build base query with common filters
     * Eliminates code duplication across index(), export(), and exportWord()
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = Booking::with('lab')
            ->whereIn('status', ['approved', 'pending', 'rejected']);

        // Apply date range filter (based on created_at - when booking was made)
        if ($request->filled('start_month')) {
            $startDate = Carbon::parse($request->start_month . '-01')->startOfMonth();
            $query->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_month')) {
            $endDate = Carbon::parse($request->end_month . '-01')->endOfMonth();
            $query->where('created_at', '<=', $endDate);
        }

        // Apply lab filter (only for index)
        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        // Apply type filter (only for index)
        if ($request->filled('type')) {
            $query->where('booking_type', $request->type);
        }

        // Apply status filter (only for index)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Generate filename based on date range
     */
    private function generateFilename($startMonth, $endMonth, $extension)
    {
        $filename = 'laporan_peminjaman';
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
     * Display the booking report page with filters
     */
    public function index(Request $request)
    {
        // Validate input
        $request->validate([
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
            'lab_id' => 'nullable|exists:labs,id',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'status' => 'nullable|in:approved,pending,rejected',
        ]);

        $query = $this->buildFilteredQuery($request);
        $bookings = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        $labs = Lab::orderBy('name')->get();
        $types = $this->typeLabels;

        return view('admin.reports.index', compact('bookings', 'labs', 'types'));
    }

    /**
     * Export bookings to CSV (native PHP, no external package needed)
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
        ]);

        $query = $this->buildFilteredQuery($request);
        $bookings = $query->orderBy('created_at', 'desc')->get();
        $filename = $this->generateFilename($request->start_month, $request->end_month, 'csv');

        // Create CSV content
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
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

            // Data rows
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
     * Export bookings to Word document (HTML table format)
     */
    public function exportWord(Request $request)
    {
        $request->validate([
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
        ]);

        $startMonth = $request->start_month;
        $endMonth = $request->end_month;

        $query = $this->buildFilteredQuery($request);
        $bookings = $query->orderBy('created_at', 'desc')->get();
        $filename = $this->generateFilename($startMonth, $endMonth, 'doc');

        // Generate date range text for title
        $dateRangeText = '';
        if ($startMonth && $endMonth) {
            $dateRangeText = Carbon::parse($startMonth)->locale('id')->isoFormat('MMMM Y') . ' - ' . Carbon::parse($endMonth)->locale('id')->isoFormat('MMMM Y');
        } elseif ($startMonth) {
            $dateRangeText = 'Dari ' . Carbon::parse($startMonth)->locale('id')->isoFormat('MMMM Y');
        } elseif ($endMonth) {
            $dateRangeText = 'Sampai ' . Carbon::parse($endMonth)->locale('id')->isoFormat('MMMM Y');
        } else {
            $dateRangeText = 'Semua Data';
        }

        // Create HTML content for Word
        $html = '
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
            <h1>LAPORAN PEMINJAMAN LABORATORIUM</h1>
            <h2>Lab Digital FEB UNDIP</h2>
            <p class="info"><strong>Periode:</strong> ' . htmlspecialchars($dateRangeText) . '</p>
            <p class="info"><strong>Tanggal Cetak:</strong> ' . Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') . ' WIB</p>
            <p class="info"><strong>Total Data:</strong> ' . $bookings->count() . ' peminjaman</p>
            
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

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($html, 200, $headers);
    }
}
