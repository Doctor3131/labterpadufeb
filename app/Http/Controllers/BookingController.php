<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lab;
use Illuminate\Http\Request;

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
        
        // Get day name from date
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $dayName = $days[date('l', strtotime($date))];
        
        // Get all labs (ignore capacity filter here, we'll warn on frontend)
        $labs = Lab::orderBy('capacity', 'asc')->get();
        
        // Filter labs that are available at the requested time
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
        // Log incoming request
        \Log::info('Booking store method called', [
            'booking_type' => $request->booking_type,
            'all_data' => $request->except(['_token', 'document'])
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
            'document' => 'required|file|mimes:pdf|max:2048', // Max 2MB
            
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

        \Log::info('Validation passed');

        // Check for schedule conflicts
        $lab = Lab::findOrFail($validated['lab_id']);
        $date = \Carbon\Carbon::parse($validated['booking_date']);
        $dayMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];
        $day = $dayMap[$date->dayOfWeek];

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

        // Create booking
        $booking = Booking::create($validated);

        // Send confirmation email (DISABLED - Uncomment saat email sudah ready)
        // try {
        //     Mail::to($booking->email)->send(new BookingConfirmation($booking));
        // } catch (\Exception $e) {
        //     \Log::warning('Failed to send booking confirmation email', [
        //         'booking_id' => $booking->id,
        //         'error' => $e->getMessage()
        //     ]);
        //     // Continue even if email fails
        // }

        // Log for debugging
        \Log::info('Booking created successfully', [
            'booking_id' => $booking->id,
            'tracking_token' => $booking->tracking_token,
            'redirect_to' => route('booking.success', $booking->tracking_token)
        ]);

        // ✅ Redirect using tracking_token (secure)
        return redirect()->route('booking.success', $booking->tracking_token)
            ->with('success', 'Permintaan peminjaman berhasil diajukan!');
            
    } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Booking creation failed', [
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
        // Validate token format (Relaxed for flexibility)
        // if (!preg_match('/^[a-f0-9]{32}$/i', $token)) {
        //     abort(404, 'Invalid tracking token format');
        // }
        
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
