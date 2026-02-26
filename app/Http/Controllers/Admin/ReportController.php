<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BpsRequest;
use App\Models\RefinitivRequest;
use App\Models\BloombergRequest;
use App\Models\Schedule;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
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
        'bloomberg' => 'Reservasi Bloomberg',
    ];

    /**
     * Build base query with common filters for Lab bookings
     * Now includes manually added schedules (without booking_id) as "approved" entries
     */
    private function buildLabQuery(Request $request)
    {
        // Get approved bookings
        $bookingsQuery = Booking::with('lab')
            ->whereIn('status', ['approved']);

        if ($request->filled('start_month')) {
            $startDate = Carbon::parse($request->start_month . '-01')->startOfMonth();
            $bookingsQuery->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_month')) {
            $endDate = Carbon::parse($request->end_month . '-01')->endOfMonth();
            $bookingsQuery->where('created_at', '<=', $endDate);
        }

        if ($request->filled('lab_id')) {
            $bookingsQuery->where('lab_id', $request->lab_id);
        }

        if ($request->filled('type')) {
            $bookingsQuery->where('booking_type', $request->type);
        }

        return $bookingsQuery;
    }

    /**
     * Get manually added schedules (without booking_id) transformed to booking-like format
     */
    private function getManualSchedules(Request $request)
    {
        $schedulesQuery = Schedule::with('lab')
            ->whereNull('booking_id'); // Only schedules added manually by admin

        if ($request->filled('start_month')) {
            $startDate = Carbon::parse($request->start_month . '-01')->startOfMonth();
            $schedulesQuery->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_month')) {
            $endDate = Carbon::parse($request->end_month . '-01')->endOfMonth();
            $schedulesQuery->where('created_at', '<=', $endDate);
        }

        if ($request->filled('lab_id')) {
            $schedulesQuery->where('lab_id', $request->lab_id);
        }

        if ($request->filled('type')) {
            $schedulesQuery->where('type', $request->type);
        }

        $schedules = $schedulesQuery->get();

        // Transform schedules to booking-like format
        return $schedules->map(function ($schedule) {
            return (object) [
                'id' => 'schedule_' . $schedule->id,
                'created_at' => $schedule->created_at,
                'pic_name' => $schedule->komting ?? $schedule->lecturer ?? 'Admin',
                'booking_type' => $schedule->type,
                'lab' => $schedule->lab,
                'lab_id' => $schedule->lab_id,
                'day' => $schedule->day,
                'booking_date' => $schedule->start_date,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'participant_count' => $schedule->student_count ?? 0,
                'status' => 'approved', // Manual schedules are always "approved"
                'is_manual_schedule' => true, // Flag to identify
            ];
        });
    }

    /**
     * Get combined lab data (bookings + manual schedules) with pagination
     */
    private function getCombinedLabData(Request $request, $perPage = 50)
    {
        // Get bookings
        $bookings = $this->buildLabQuery($request)->get()->map(function ($booking) {
            $booking->is_manual_schedule = false;
            return $booking;
        });

        // Get manual schedules
        $manualSchedules = $this->getManualSchedules($request);

        // Merge and sort by created_at desc
        $combined = $bookings->concat($manualSchedules)
            ->sortByDesc('created_at')
            ->values();

        // Manual pagination
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $items = $combined->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $combined->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Build base query for BPS requests
     * Only includes completed/sent requests
     */
    private function buildBpsQuery(Request $request)
    {
        $query = BpsRequest::where('status', 'completed'); // Only completed/sent

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
     * Only includes requests marked as attended (hadir)
     */
    private function buildRefinitivQuery(Request $request)
    {
        $query = RefinitivRequest::where('attendance_status', 'hadir'); // Only attended

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
            'bloomberg' => 'laporan_bloomberg',
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
            'report_type' => 'nullable|in:lab,bps,refinitiv,bloomberg',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
            'lab_id' => 'nullable|exists:labs,id',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'bloomberg_type' => 'nullable|in:reservasi,walk_in',
        ]);

        $reportType = $request->get('report_type', 'lab');
        $data = collect();
        
        if ($reportType === 'lab') {
            // Use combined data (bookings + manual schedules)
            $data = $this->getCombinedLabData($request);
        } elseif ($reportType === 'bps') {
            $query = $this->buildBpsQuery($request);
            $data = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        } elseif ($reportType === 'refinitiv') {
            $query = $this->buildRefinitivQuery($request);
            $data = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        } elseif ($reportType === 'bloomberg') {
            $query = $this->buildBloombergQuery($request);
            $data = $query->orderBy('usage_date', 'desc')->paginate(50)->withQueryString();
        }

        $labs = Lab::orderBy('name')->get();
        $types = $this->typeLabels;
        $reportTypes = $this->reportTypes;

        // Return partial view for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.reports.partials.table', compact('data', 'reportType', 'reportTypes'))->render(),
                'total' => $data->total(),
            ]);
        }

        return view('admin.reports.index', compact('data', 'labs', 'types', 'reportType', 'reportTypes'));
    }

    /**
     * Export to Excel (.xlsx)
     */
    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:lab,bps,refinitiv,bloomberg',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
            'lab_id' => 'nullable|exists:labs,id',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'bloomberg_type' => 'nullable|in:reservasi,walk_in',
        ]);

        $reportType = $request->get('report_type', 'lab');

        if ($reportType === 'lab') {
            return $this->exportLabExcel($request);
        } elseif ($reportType === 'bps') {
            return $this->exportBpsExcel($request);
        } elseif ($reportType === 'refinitiv') {
            return $this->exportRefinitivExcel($request);
        } elseif ($reportType === 'bloomberg') {
            return $this->exportBloombergExcel($request);
        }
    }

    /**
     * Helper: style an Excel sheet header and return download response
     */
    private function downloadExcel($spreadsheet, $reportType, Request $request)
    {
        $filename = $this->generateFilename($reportType, $request->start_month, $request->end_month, 'xlsx');
        $tempFile = tempnam(sys_get_temp_dir(), $reportType . '_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Helper: apply header style to a range
     */
    private function applyHeaderStyle($sheet, $range, $colorRgb = '1F4E79')
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorRgb]],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);
    }

    /**
     * Helper: apply data borders + alternate row colors
     */
    private function applyDataStyle($sheet, $lastCol, $lastRow)
    {
        if ($lastRow < 2) return;
        $sheet->getStyle('A2:' . $lastCol . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        for ($r = 2; $r <= $lastRow; $r++) {
            if ($r % 2 === 1) {
                $sheet->getStyle('A' . $r . ':' . $lastCol . $r)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F3F4F6');
            }
        }
    }

    /**
     * Export Lab bookings to Excel
     */
    private function exportLabExcel(Request $request)
    {
        $bookings = $this->buildLabQuery($request)->orderBy('created_at', 'desc')->get()->map(function ($b) {
            $b->is_manual_schedule = false;
            return $b;
        });
        $manualSchedules = $this->getManualSchedules($request);
        $combined = $bookings->concat($manualSchedules)->sortByDesc('created_at');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Peminjaman Lab');

        $cols = ['A'=>'No','B'=>'Tanggal Dibuat','C'=>'Nama Peminjam','D'=>'Tipe Peminjaman','E'=>'Laboratorium','F'=>'Hari','G'=>'Tanggal Peminjaman','H'=>'Jam Mulai','I'=>'Jam Selesai','J'=>'Jumlah Peserta','K'=>'Status','L'=>'Sumber'];
        foreach ($cols as $c => $label) $sheet->setCellValue($c.'1', $label);
        $this->applyHeaderStyle($sheet, 'A1:L1', 'D97706'); // yellow/amber

        $row = 2;
        foreach ($combined as $no => $item) {
            $sheet->setCellValue('A'.$row, $no+1);
            $sheet->setCellValue('B'.$row, Carbon::parse($item->created_at)->format('d/m/Y H:i'));
            $sheet->setCellValue('C'.$row, $item->pic_name);
            $sheet->setCellValue('D'.$row, $this->typeLabels[$item->booking_type] ?? $item->booking_type);
            $sheet->setCellValue('E'.$row, $item->lab->name ?? '-');
            $sheet->setCellValue('F'.$row, $item->day);
            $sheet->setCellValue('G'.$row, $item->booking_date ? Carbon::parse($item->booking_date)->format('d/m/Y') : '-');
            $sheet->setCellValue('H'.$row, Carbon::parse($item->start_time)->format('H:i'));
            $sheet->setCellValue('I'.$row, Carbon::parse($item->end_time)->format('H:i'));
            $sheet->setCellValue('J'.$row, $item->participant_count);
            $sheet->setCellValue('K'.$row, $this->statusLabels[$item->status] ?? $item->status);
            $sheet->setCellValue('L'.$row, $item->is_manual_schedule ? 'Jadwal Manual Admin' : 'Booking');
            $row++;
        }

        $this->applyDataStyle($sheet, 'L', $row-1);
        foreach (array_keys($cols) as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

        return $this->downloadExcel($spreadsheet, 'lab', $request);
    }

    /**
     * Export BPS requests to Excel
     */
    private function exportBpsExcel(Request $request)
    {
        $requests = $this->buildBpsQuery($request)->orderBy('created_at', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('BPS');

        $cols = ['A'=>'No','B'=>'Timestamp','C'=>'Nama','D'=>'NIM/NIP','E'=>'Program Studi','F'=>'Keperluan Penggunaan Data'];
        foreach ($cols as $c => $label) $sheet->setCellValue($c.'1', $label);
        $this->applyHeaderStyle($sheet, 'A1:F1', '0D9488'); // teal

        $row = 2;
        foreach ($requests as $no => $req) {
            $identifier = $req->applicant_type === 'mahasiswa' ? $req->nim : $req->nip;
            $purpose = $req->purpose === 'Lainnya' ? $req->purpose_other : $req->purpose;

            $sheet->setCellValue('A'.$row, $no+1);
            $sheet->setCellValue('B'.$row, Carbon::parse($req->created_at)->format('d/m/Y H:i:s'));
            $sheet->setCellValue('C'.$row, $req->name);
            $sheet->setCellValueExplicit('D'.$row, $identifier ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$row, $req->study_program ?? '-');
            $sheet->setCellValue('F'.$row, $purpose);
            $row++;
        }

        $this->applyDataStyle($sheet, 'F', $row-1);
        foreach (array_keys($cols) as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

        return $this->downloadExcel($spreadsheet, 'bps', $request);
    }

    /**
     * Export Refinitiv requests to Excel
     */
    private function exportRefinitivExcel(Request $request)
    {
        $requests = $this->buildRefinitivQuery($request)->orderBy('created_at', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Refinitiv');

        $cols = ['A'=>'No','B'=>'Nama','C'=>'NIM/NIP','D'=>'Program Studi','E'=>'Keperluan Penggunaan Data','F'=>'Tanggal Pemakaian'];
        foreach ($cols as $c => $label) $sheet->setCellValue($c.'1', $label);
        $this->applyHeaderStyle($sheet, 'A1:F1', '2563EB'); // blue

        $row = 2;
        foreach ($requests as $no => $req) {
            $purpose = $req->purpose === 'lainnya' ? $req->purpose_other : (RefinitivRequest::PURPOSES[$req->purpose] ?? $req->purpose);
            $session = RefinitivRequest::SESSIONS[$req->session] ?? $req->session;

            $sheet->setCellValue('A'.$row, $no+1);
            $sheet->setCellValue('B'.$row, $req->name);
            $sheet->setCellValueExplicit('C'.$row, $req->nim_nip ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$row, $req->study_program ?? '-');
            $sheet->setCellValue('E'.$row, $purpose);
            $sheet->setCellValue('F'.$row, Carbon::parse($req->usage_date)->format('d/m/Y') . ' (' . $session . ')');
            $row++;
        }

        $this->applyDataStyle($sheet, 'F', $row-1);
        foreach (array_keys($cols) as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

        return $this->downloadExcel($spreadsheet, 'refinitiv', $request);
    }

    /**
     * Export to Word document
     */
    public function exportWord(Request $request)
    {
        $request->validate([
            'report_type' => 'nullable|in:lab,bps,refinitiv,bloomberg',
            'start_month' => 'nullable|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m',
            'lab_id' => 'nullable|exists:labs,id',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'bloomberg_type' => 'nullable|in:reservasi,walk_in',
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
        } elseif ($reportType === 'bloomberg') {
            return $this->exportBloombergWord($request, $headers);
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
     * Includes both approved bookings and manual schedules
     */
    private function exportLabWord(Request $request, $headers)
    {
        // Get bookings
        $bookings = $this->buildLabQuery($request)->orderBy('created_at', 'desc')->get()->map(function ($booking) {
            $booking->is_manual_schedule = false;
            return $booking;
        });

        // Get manual schedules
        $manualSchedules = $this->getManualSchedules($request);

        // Merge and sort
        $combined = $bookings->concat($manualSchedules)->sortByDesc('created_at');

        $dateRangeText = $this->getDateRangeText($request->start_month, $request->end_month);

        $html = $this->getWordHeader('LAPORAN PEMINJAMAN LABORATORIUM', 'Lab Digital FEB UNDIP', $dateRangeText, $combined->count() . ' peminjaman');
        
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
        foreach ($combined as $item) {
            $html .= '
                    <tr>
                        <td class="center">' . $no++ . '</td>
                        <td>' . Carbon::parse($item->created_at)->format('d/m/Y H:i') . '</td>
                        <td>' . htmlspecialchars($item->pic_name) . '</td>
                        <td>' . htmlspecialchars($this->typeLabels[$item->booking_type] ?? $item->booking_type) . '</td>
                        <td>' . htmlspecialchars($item->lab->name ?? '-') . '</td>
                        <td class="center">' . htmlspecialchars($item->day) . '</td>
                        <td class="center">' . ($item->booking_date ? Carbon::parse($item->booking_date)->format('d/m/Y') : '-') . '</td>
                        <td class="center">' . Carbon::parse($item->start_time)->format('H:i') . '-' . Carbon::parse($item->end_time)->format('H:i') . '</td>
                        <td class="center">' . $item->participant_count . '</td>
                        <td class="center">' . htmlspecialchars($this->statusLabels[$item->status] ?? $item->status) . '</td>
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
     * Build base query for Bloomberg requests
     */
    private function buildBloombergQuery(Request $request)
    {
        $query = BloombergRequest::query();

        if ($request->filled('start_month')) {
            $startDate = Carbon::parse($request->start_month . '-01')->startOfMonth();
            $query->where('usage_date', '>=', $startDate);
        }

        if ($request->filled('end_month')) {
            $endDate = Carbon::parse($request->end_month . '-01')->endOfMonth();
            $query->where('usage_date', '<=', $endDate);
        }

        if ($request->filled('bloomberg_type') && in_array($request->bloomberg_type, ['reservasi', 'walk_in'])) {
            $query->where('type', $request->bloomberg_type);
        }

        return $query;
    }

    /**
     * Export Bloomberg requests to Excel (.xlsx)
     */
    private function exportBloombergExcel(Request $request)
    {
        $requests = $this->buildBloombergQuery($request)->orderBy('usage_date', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Bloomberg');

        $cols = ['A'=>'No','B'=>'Timestamp','C'=>'Tipe','D'=>'Nama','E'=>'Status Pemohon','F'=>'NIM/NIP','G'=>'Program Studi','H'=>'No. HP','I'=>'Tanggal Penggunaan','J'=>'Sesi','K'=>'Keperluan','L'=>'Judul Penelitian / Mata Kuliah / Dosen'];
        foreach ($cols as $c => $label) $sheet->setCellValue($c.'1', $label);
        $this->applyHeaderStyle($sheet, 'A1:L1', '4338CA');

        $row = 2;
        foreach ($requests as $no => $req) {
            $typeLabel = BloombergRequest::TYPES[$req->type] ?? $req->type;
            $purposeLabel = BloombergRequest::PURPOSES[$req->purpose] ?? $req->purpose;
            $sessionLabel = $req->session_label;

            $detail = '';
            if ($req->research_title) {
                $detail = $req->research_title;
            } elseif ($req->subject_name) {
                $detail = $req->subject_name;
            } elseif ($req->lecturer_name) {
                $detail = $req->lecturer_name;
            }

            $sheet->setCellValue('A'.$row, $no + 1);
            $sheet->setCellValue('B'.$row, Carbon::parse($req->created_at)->format('d/m/Y H:i:s'));
            $sheet->setCellValue('C'.$row, $typeLabel);
            $sheet->setCellValue('D'.$row, $req->name);
            $sheet->setCellValue('E'.$row, $req->applicant_type_label);
            $sheet->setCellValueExplicit('F'.$row, $req->nim_nip, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G'.$row, $req->study_program ?? '-');
            $sheet->setCellValueExplicit('H'.$row, $req->phone, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I'.$row, Carbon::parse($req->usage_date)->format('d/m/Y'));
            $sheet->setCellValue('J'.$row, $sessionLabel);
            $sheet->setCellValue('K'.$row, $purposeLabel);
            $sheet->setCellValue('L'.$row, $detail ?: '-');
            $row++;
        }

        $this->applyDataStyle($sheet, 'L', $row-1);
        foreach (array_keys($cols) as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

        return $this->downloadExcel($spreadsheet, 'bloomberg', $request);
    }

    /**
     * Export Bloomberg requests to Word
     */
    private function exportBloombergWord(Request $request, $headers)
    {
        $requests = $this->buildBloombergQuery($request)->orderBy('usage_date', 'desc')->get();
        $dateRangeText = $this->getDateRangeText($request->start_month, $request->end_month);

        $html = $this->getWordHeader('REKAPITULASI PELAYANAN BLOOMBERG TERMINAL', 'Laboratorium dan Fasilitas Digital FEB UNDIP', $dateRangeText, $requests->count() . ' reservasi');
        
        $html .= '
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Timestamp</th>
                        <th>Tipe</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>NIM/NIP</th>
                        <th>Prodi</th>
                        <th>No. HP</th>
                        <th>Tanggal</th>
                        <th>Sesi</th>
                        <th>Keperluan</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        foreach ($requests as $req) {
            $typeLabel = BloombergRequest::TYPES[$req->type] ?? $req->type;
            $purposeLabel = BloombergRequest::PURPOSES[$req->purpose] ?? $req->purpose;
            $sessionLabel = $req->session_label;

            $detail = '';
            if ($req->research_title) {
                $detail = $req->research_title;
            } elseif ($req->subject_name) {
                $detail = $req->subject_name;
            } elseif ($req->lecturer_name) {
                $detail = $req->lecturer_name;
            }

            $html .= '
                    <tr>
                        <td class="center">' . $no++ . '</td>
                        <td>' . Carbon::parse($req->created_at)->format('d/m/Y H:i') . '</td>
                        <td class="center">' . htmlspecialchars($typeLabel) . '</td>
                        <td>' . htmlspecialchars($req->name) . '</td>
                        <td>' . htmlspecialchars($req->applicant_type_label) . '</td>
                        <td>' . htmlspecialchars($req->nim_nip) . '</td>
                        <td>' . htmlspecialchars($req->study_program ?? '-') . '</td>
                        <td>' . htmlspecialchars($req->phone) . '</td>
                        <td class="center">' . Carbon::parse($req->usage_date)->format('d/m/Y') . '</td>
                        <td>' . htmlspecialchars($sessionLabel) . '</td>
                        <td>' . htmlspecialchars($purposeLabel) . '</td>
                        <td>' . htmlspecialchars($detail ?: '-') . '</td>
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
