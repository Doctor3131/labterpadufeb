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
            // Use constants from Booking model for validation
            $bookingTypesRule = 'required|in:' . implode(',', Booking::BOOKING_TYPES);
            $activityTypesRule = 'required_if:booking_type,non_perkuliahan|in:' . implode(',', Booking::ACTIVITY_TYPES);
            
            $validated = $request->validate([
            'booking_type' => $bookingTypesRule,
            'unit_type' => 'required_unless:booking_type,pribadi|in:s1_tembalang,pascasarjana_pleburan',
            'pic_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\']+$/'],
            'study_program' => [
                function ($attribute, $value, $fail) use ($request) {
                    $isPribadi = $request->booking_type === 'pribadi';
                    $status = $request->applicant_status;
                    
                    // Program Studi not required for Dosen/Pegawai/Lainnya in pribadi booking
                    if ($isPribadi && in_array($status, ['Dosen', 'Pegawai', 'Lainnya'])) {
                        return; // Skip validation
                    }
                    
                    // Required for other cases
                    if (!$value) {
                        $fail('Program Studi wajib diisi.');
                    }
                },
                'nullable', 'string', 'max:255'
            ],
            'nim' => [
                function ($attribute, $value, $fail) use ($request) {
                    $isPribadi = $request->booking_type === 'pribadi';
                    $status = $request->applicant_status;
                    
                    // NIM not required for Dosen/Pegawai/Lainnya in pribadi booking
                    if ($isPribadi && in_array($status, ['Dosen', 'Pegawai', 'Lainnya'])) {
                        return; // Skip validation
                    }
                    
                    // Required for other cases
                    if (!$value) {
                        $fail('NIM wajib diisi.');
                    } elseif (!preg_match('/^[0-9]{14}$/', $value)) {
                        $fail('NIM harus berupa 14 digit angka.');
                    }
                },
                'nullable', 'string', 'size:14', 'regex:/^[0-9]{14}$/'
            ],
            'nip' => [
                function ($attribute, $value, $fail) use ($request) {
                    $status = $request->applicant_status;
                    
                    // NIP required for Dosen/Pegawai
                    if (in_array($status, ['Dosen', 'Pegawai'])) {
                        if (!$value) {
                            $fail('NIP wajib diisi untuk Dosen/Pegawai.');
                        } elseif (!preg_match('/^[0-9]{18}$/', $value)) {
                            $fail('NIP harus berupa 18 digit angka.');
                        }
                    }
                },
                'nullable', 'string', 'size:18', 'regex:/^[0-9]{18}$/'
            ],
            'phone_number' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
            // Lab is required UNLESS it's a personal booking (pribadi)
            // Personal bookings don't select lab - assignment done on-site by assistants
            'lab_id' => 'required_unless:booking_type,pribadi|nullable|exists:labs,id',
            'booking_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    if ($date->isSunday()) {
                        $fail('Peminjaman lab tidak tersedia pada hari Minggu.');
                    }
                },
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'participant_count' => 'required|integer|min:1',
            // Document validation:
            // 1. Required if NOT personal booking
            // 2. NOT required for personal bookings (pribadi) at all
            // Note: All validation in closure to avoid running file rules on optional empty file
            'document' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $isPribadi = $request->booking_type === 'pribadi';
                    $hasFile = $request->hasFile('document');

                    // Document is NOT required for pribadi bookings
                    $isRequired = !$isPribadi;
                    
                    if ($isRequired && !$hasFile) {
                        $fail('Dokumen pendukung (Surat/KTM) wajib diupload.');
                        return;
                    }

                    // Validate file type and size only if file is uploaded
                    if ($hasFile) {
                        $file = $request->file('document');
                        $allowedMimes = ['pdf'];
                        $maxSize = 5120; // 5MB in KB

                        if (!in_array($file->getClientOriginalExtension(), $allowedMimes) && 
                            !in_array($file->getMimeType(), ['application/pdf'])) {
                            $fail('Dokumen harus berformat PDF.');
                            return;
                        }

                        if ($file->getSize() > $maxSize * 1024) {
                            $fail('Ukuran dokumen maksimal 5MB.');
                        }
                    }
                },
            ],
            
            // Personal Booking fields - validate against allowed values
            'applicant_status' => 'nullable|required_if:booking_type,pribadi|in:' . implode(',', Booking::APPLICANT_STATUSES),
            'custom_status' => 'nullable|required_if:applicant_status,Lainnya|string|max:255|regex:/^[a-zA-Z0-9\s\.\-]+$/',
            'custom_study_program' => 'nullable|required_if:study_program,Lainnya|string|max:255|regex:/^[a-zA-Z0-9\s\.\-]+$/',
            'class_year' => 'nullable|string|max:4|regex:/^[0-9]{4}$/',
            'purpose' => 'nullable|required_if:booking_type,pribadi|string|max:255',
            
            // Non-perkuliahan fields
            'activity_type' => $activityTypesRule,
            'position' => 'required_if:booking_type,non_perkuliahan|string|max:255',
            'equipment_needs' => 'nullable|string',
            'activity_name' => 'required_if:booking_type,non_perkuliahan|string|max:255',
            
            // Perkuliahan fields
            'course_name' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
            'lecturer_name' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
            'lecturer_nip' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:18|regex:/^[0-9]+$/',
            'software_needs' => 'nullable|string|max:255',
        ]);

        Log::info('Validation passed', ['lab_id' => $validated['lab_id'] ?? null]);

        // Use transaction with lock to prevent race condition (double booking)
        return DB::transaction(function () use ($request, $validated) {
            $isPribadi = $request->booking_type === 'pribadi';
            
            // If user selected "Lainnya" for study program, use custom value
            if (isset($validated['study_program']) && $validated['study_program'] === 'Lainnya' && !empty($validated['custom_study_program'])) {
                $validated['study_program'] = $validated['custom_study_program'];
            }
            
            // Note: Timezone already set to Asia/Jakarta in config/app.php
            $date = Carbon::parse($validated['booking_date']);
            $day = DayHelper::fromIndex($date->dayOfWeek);

            // For non-pribadi bookings: check lab availability and conflicts
            // Pribadi bookings skip this - no lab selection, no conflict check
            if (!$isPribadi) {
                // Lock the lab row to prevent concurrent bookings
                $lab = Lab::lockForUpdate()->findOrFail($validated['lab_id']);
                
                // Check availability inside the transaction (after lock)
                if (!$lab->isAvailable($day, $validated['start_time'], $validated['end_time'], $validated['booking_date'])) {
                    return back()->withErrors([
                        'time_conflict' => 'Ruangan ' . $lab->name . ' tidak tersedia pada waktu yang dipilih. Sudah ada jadwal lain yang bentrok dengan waktu peminjaman Anda (' . $validated['start_time'] . ' - ' . $validated['end_time'] . '). Silakan pilih waktu atau ruangan lain.'
                    ])->withInput();
                }
            }

            // Handle document upload
            if ($request->hasFile('document')) {
                $path = $request->file('document')->store('booking-documents', 'public');
                $validated['document_path'] = $path;
            }

            // Set is_recurring for perkuliahan tetap (pribadi is never recurring)
            $validated['is_recurring'] = $request->booking_type === 'perkuliahan_tetap';

            // Generate unique tracking token
            $validated['tracking_token'] = bin2hex(random_bytes(16));

            // Set day for booking
            $validated['day'] = $day;

            // Create booking (inside transaction - prevents race condition)
            $booking = Booking::create($validated);

            // Log for debugging (no PII)
            Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'booking_type' => $request->booking_type
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
