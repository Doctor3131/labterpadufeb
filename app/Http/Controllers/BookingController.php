<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lab;
use App\Helpers\DayHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Show the booking form
     */
    public function create()
    {
        $labs = Lab::orderBy('name')->get();
        return view('booking.create', compact('labs'));
    }

    /**
     * Get available labs based on date, time, and capacity
     * Returns only labs that are truly available (considering schedule conflicts)
     */
    public function getAvailableLabs(Request $request)
    {
        $participantCount = $request->participant_count;
        $date = $request->booking_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        
        // Get day name from date using DayHelper
        $dayName = DayHelper::fromEnglish(date('l', strtotime($date)));
        
        // Get all labs with eager loading to prevent N+1 queries
        // Only get labs that are available (not in maintenance)
        $labs = Lab::where('status', 'available')
            ->with(['schedules', 'bookings' => function ($query) use ($date) {
                $query->where('booking_date', $date)->where('status', 'pending');
            }])->orderBy('capacity', 'asc')->get();
        
        // Filter labs that are available at the requested time (in memory, no additional queries)
        $availableLabs = $labs->filter(function ($lab) use ($dayName, $startTime, $endTime, $date) {
            return $lab->isAvailable($dayName, $startTime, $endTime, $date);
        });
        
        return response()->json($availableLabs->values());
    }



    /**
     * Store a new booking request
     */
    public function store(Request $request)
    {
        // Log incoming request (sanitized - no PII)
        Log::info('Booking store method called', [
            'booking_type' => $request->booking_type,
            'lab_id' => $request->lab_id,
            'booking_date' => $request->booking_date
        ]);

        try {
            $validated = $request->validate([
            'booking_type' => 'required|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'unit_type' => 'required_unless:booking_type,pribadi|in:s1_tembalang,pascasarjana_pleburan',
            'pic_name' => 'required|string|max:255',
            'study_program' => 'required|string|max:255',
            'nim' => 'required|string|size:14|regex:/^[0-9]{14}$/',
            'phone_number' => 'required|string|min:10|max:15|regex:/^[0-9+]{10,15}$/',
            'address' => 'required|string',
            'lab_id' => 'required|exists:labs,id',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'participant_count' => 'required|integer|min:1',
            // Document validation:
            // 1. Required if NOT personal booking
            // 2. Required if personal booking AND applicant status is 'Mahasiswa'
            'document' => [
                function ($attribute, $value, $fail) use ($request) {
                    $isPribadi = $request->booking_type === 'pribadi';
                    $isMahasiswa = $request->applicant_status === 'Mahasiswa';

                    if (!$isPribadi) {
                        // Not pribadi (perkuliahan/non-perkuliahan) -> Required
                        if (!$request->hasFile('document')) {
                            $fail('Dokumen pendukung (Surat/KTM) wajib diupload.');
                        }
                    } else {
                        // Pribadi
                        if ($isMahasiswa) {
                            // Mahasiswa -> Required KTM
                            if (!$request->hasFile('document')) {
                                $fail('Foto/Scan KTM wajib diupload untuk mahasiswa.');
                            }
                        }
                        // Non-mahasiswa (Dosen/Pegawai/Lainnya) -> Optional
                    }
                },
                'file', 'mimes:pdf', 'max:2048'
            ],
            
            // Personal Booking fields
            'applicant_status' => 'required_if:booking_type,pribadi|string|max:255',
            'class_year' => 'nullable|string|max:4',
            'purpose' => 'required_if:booking_type,pribadi|string|max:255',
            
            // Non-perkuliahan fields
            'activity_type' => 'required_if:booking_type,non_perkuliahan',
            'position' => 'required_if:booking_type,non_perkuliahan|string|max:255',
            'equipment_needs' => 'nullable|string',
            'activity_name' => 'required_if:booking_type,non_perkuliahan|string|max:255',
            
            // Perkuliahan fields
            'course_name' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
            'lecturer_name' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
            'lecturer_nip' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:18|regex:/^[0-9]+$/',
            'software_needs' => 'nullable|string|max:255',
        ]);

        Log::info('Validation passed', ['lab_id' => $validated['lab_id']]);

        // Use transaction with lock to prevent race condition (double booking)
        return DB::transaction(function () use ($request, $validated) {
            // Lock the lab row to prevent concurrent bookings
            $lab = Lab::lockForUpdate()->findOrFail($validated['lab_id']);
            
            // Use consistent timezone (Asia/Jakarta)
            $date = Carbon::parse($validated['booking_date'])->timezone('Asia/Jakarta');
            $day = DayHelper::fromIndex($date->dayOfWeek);

            // Check availability inside the transaction (after lock)
            if (!$lab->isAvailable($day, $validated['start_time'], $validated['end_time'], $validated['booking_date'])) {
                return back()->withErrors([
                    'time_conflict' => 'Ruangan ' . $lab->name . ' tidak tersedia pada waktu yang dipilih. Sudah ada jadwal lain yang bentrok dengan waktu peminjaman Anda (' . $validated['start_time'] . ' - ' . $validated['end_time'] . '). Silakan pilih waktu atau ruangan lain.'
                ])->withInput();
            }

            // Handle document upload
            if ($request->hasFile('document')) {
                $path = $request->file('document')->store('booking-documents', 'public');
                $validated['document_path'] = $path;
            }

            // Set is_recurring for perkuliahan tetap
            $validated['is_recurring'] = $request->booking_type === 'perkuliahan_tetap';

            // Generate unique tracking token
            $validated['tracking_token'] = bin2hex(random_bytes(16));

            // Day already set above during conflict check
            $validated['day'] = $day;

            // Create booking (inside transaction - prevents race condition)
            $booking = Booking::create($validated);

            // Log for debugging (no PII)
            Log::info('Booking created successfully', [
                'booking_id' => $booking->id
            ]);

            // Redirect using tracking_token (secure)
            return redirect()->route('booking.success', $booking->tracking_token)
                ->with('success', 'Permintaan peminjaman berhasil diajukan!');
        }); // End DB::transaction
            
    } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show success page using tracking token (secure)
     */
    public function success($token)
    {
        // Find booking by tracking token instead of ID for security
        $booking = Booking::with('lab')
            ->where('tracking_token', $token)
            ->firstOrFail();
            
        return view('booking.success', compact('booking'));
    }

    /**
     * Show print view for downloading PDF
     */
    public function print($token)
    {
        $booking = Booking::with(['lab', 'handler'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        return view('booking.print', compact('booking'));
    }
}
