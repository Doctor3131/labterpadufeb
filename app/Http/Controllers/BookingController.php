<?php

namespace App\Http\Controllers;

use App\Helpers\DayHelper;
use App\Models\Booking;
use App\Models\Lab;
use App\Models\MahasiswaFeb;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
            'booking_date' => $request->booking_date,
        ]);

        try {
            $isPribadi = $request->booking_type === 'pribadi';
            $isNonPerkuliahanOnBehalfLecturer = $request->booking_type === 'non_perkuliahan'
                && $request->boolean('is_on_behalf_lecturer');

            // Use constants from Booking model for validation
            $bookingTypesRule = 'required|in:'.implode(',', Booking::BOOKING_TYPES);
            $activityTypesRule = 'required_if:booking_type,non_perkuliahan|in:'.implode(',', Booking::ACTIVITY_TYPES);

            // Build validation rules - pribadi has very different requirements
            $rules = [
                'booking_type' => $bookingTypesRule,
                'pribadi_sub_type' => 'nullable|required_if:booking_type,pribadi|in:mahasiswa,non_mahasiswa',
            ];

            if ($isPribadi) {
                // Pribadi bookings: only personal data, no date/time/lab/document
                $subType = $request->pribadi_sub_type;

                if ($subType === 'mahasiswa') {
                    $rules['nim'] = ['required', 'string', 'max:20'];
                } else {
                    // Non-mahasiswa
                    $rules['pic_name'] = ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\']+$/'];
                    $rules['nip'] = ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'];
                    $rules['phone_number'] = ['required', 'string', 'regex:/^08[0-9]{8,13}$/'];
                }
            } else {
                // Non-pribadi bookings: full validation
                $rules = array_merge($rules, [
                    'unit_type' => 'required|in:s1_tembalang,pascasarjana_pleburan',
                    'pic_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\']+$/'],
                    'study_program' => ['required', 'string', 'max:255'],
                    'nim' => $isNonPerkuliahanOnBehalfLecturer
                        ? ['nullable', 'string', 'size:14', 'regex:/^[0-9]{14}$/']
                        : ['required', 'string', 'size:14', 'regex:/^[0-9]{14}$/'],
                    'nip' => $isNonPerkuliahanOnBehalfLecturer
                        ? ['required', 'string', 'size:18', 'regex:/^[0-9]{18}$/']
                        : [
                            function ($attribute, $value, $fail) use ($request) {
                                $status = $request->applicant_status;
                                if (in_array($status, ['Dosen', 'Pegawai'])) {
                                    if (! $value) {
                                        $fail('NIP wajib diisi untuk Dosen/Pegawai.');
                                    } elseif (! preg_match('/^[0-9]{18}$/', $value)) {
                                        $fail('NIP harus berupa 18 digit angka.');
                                    }
                                }
                            },
                            'nullable', 'string', 'size:18', 'regex:/^[0-9]{18}$/',
                        ],
                    'phone_number' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
                    'lab_id' => 'required|exists:labs,id',
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
                    'document' => [
                        'nullable',
                        function ($attribute, $value, $fail) use ($request) {
                            $hasFile = $request->hasFile('document');

                            if (! $hasFile) {
                                $fail('Dokumen pendukung (Surat/KTM) wajib diupload.');

                                return;
                            }

                            if ($hasFile) {
                                $file = $request->file('document');
                                $maxSize = 5120; // 5MB in KB

                                // Validate MIME type (server-side, not client extension)
                                if (! in_array($file->getMimeType(), ['application/pdf'])) {
                                    $fail('Dokumen harus berformat PDF.');

                                    return;
                                }

                                if ($file->getSize() > $maxSize * 1024) {
                                    $fail('Ukuran dokumen maksimal 5MB.');
                                }
                            }
                        },
                    ],
                    'custom_study_program' => 'nullable|required_if:study_program,Lainnya|string|max:255|regex:/^[a-zA-Z0-9\s\.\-]+$/',
                    'is_bimbingan_dosen' => 'nullable|boolean',
                    'is_on_behalf_lecturer' => 'nullable|boolean',
                    'activity_type' => $request->booking_type === 'non_perkuliahan' && $request->is_bimbingan_dosen
                        ? 'nullable|in:'.implode(',', Booking::ACTIVITY_TYPES)
                        : $activityTypesRule,
                    'position' => $request->booking_type === 'non_perkuliahan' && $request->is_bimbingan_dosen
                        ? 'nullable|string|max:255'
                        : 'required_if:booking_type,non_perkuliahan|string|max:255',
                    'equipment_needs' => 'nullable|string',
                    'activity_name' => 'required_if:booking_type,non_perkuliahan|string|max:255',
                    'course_name' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
                    'lecturer_name' => ($request->booking_type === 'non_perkuliahan' && $request->is_bimbingan_dosen)
                        ? ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\']+$/']
                        : 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
                    'lecturer_nip' => ($request->booking_type === 'non_perkuliahan' && $request->is_bimbingan_dosen)
                        ? ['required', 'string', 'max:18', 'regex:/^[0-9]+$/']
                        : 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:18|regex:/^[0-9]+$/',
                    'software_needs' => 'nullable|string|max:255',

                    // Recurrence end for perkuliahan_tetap (Google Calendar style)
                    'repeat_type' => 'nullable|required_if:booking_type,perkuliahan_tetap|in:never,count,date',
                    'repeat_count' => 'nullable|required_if:repeat_type,count|integer|min:2|max:52',
                    'repeat_end_date' => 'nullable|required_if:repeat_type,date|date',
                ]);
            }

            $validated = $request->validate($rules);

            Log::info('Validation passed', ['booking_type' => $validated['booking_type']]);

            // Use transaction with lock to prevent race condition (double booking)
            return DB::transaction(function () use ($request, $validated, $isPribadi, $isNonPerkuliahanOnBehalfLecturer) {
                // If user selected "Lainnya" for study program, use custom value
                if (isset($validated['study_program']) && $validated['study_program'] === 'Lainnya' && ! empty($validated['custom_study_program'])) {
                    $validated['study_program'] = $validated['custom_study_program'];
                }

                if ($isPribadi) {
                    // Pribadi: handle mahasiswa NIM lookup
                    $subType = $validated['pribadi_sub_type'] ?? null;

                    if ($subType === 'mahasiswa') {
                        $mahasiswa = MahasiswaFeb::where('nim', $validated['nim'])->first();

                        if (! $mahasiswa) {
                            return back()->withErrors(['nim' => 'NIM tidak ditemukan di database mahasiswa FEB.'])->withInput();
                        }

                        // Auto-populate from mahasiswa_feb
                        $validated['pic_name'] = $mahasiswa->nama;
                        $validated['study_program'] = $mahasiswa->prodi;
                        $validated['applicant_status'] = 'Mahasiswa';
                    } else {
                        // Non-mahasiswa: applicant_status based on having NIP
                        $validated['applicant_status'] = 'Lainnya';
                    }

                    // Generate unique tracking token
                    $validated['tracking_token'] = bin2hex(random_bytes(16));

                    // Create booking (pribadi - no date/time/lab)
                    $booking = Booking::create($validated);

                    // Auto-approve pribadi (status not mass-assignable for security)
                    $booking->status = 'approved';
                    $booking->save();
                } else {
                    // Non-pribadi: regular booking flow
                    if ($isNonPerkuliahanOnBehalfLecturer) {
                        $validated['applicant_status'] = 'Dosen';
                    }

                    unset($validated['is_on_behalf_lecturer']);

                    $date = Carbon::parse($validated['booking_date']);
                    $day = DayHelper::fromIndex($date->dayOfWeek);

                    // Compute recurrence end for perkuliahan_tetap
                    $isTetap = $request->booking_type === 'perkuliahan_tetap';
                    $recurringEnd = $isTetap
                        ? $this->computeRecurringEndDate($date->copy(), $request->repeat_type, $request->repeat_count, $request->repeat_end_date)
                        : null;

                    // Lock the lab row to prevent concurrent bookings
                    $lab = Lab::lockForUpdate()->findOrFail($validated['lab_id']);

                    // Check availability inside the transaction (after lock), across the whole range
                    if (! $this->isLabAvailableForRange($lab, $day, $validated['start_time'], $validated['end_time'], $date, $recurringEnd)) {
                        return back()->withErrors([
                            'time_conflict' => 'Ruangan '.$lab->name.' tidak tersedia pada waktu yang dipilih. Sudah ada jadwal lain yang bentrok dengan waktu peminjaman Anda ('.$validated['start_time'].' - '.$validated['end_time'].'). Silakan pilih waktu atau ruangan lain.',
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

                    // Set day for booking
                    $validated['day'] = $day;

                    // Set end date for recurring perkuliahan_tetap series
                    if ($isTetap) {
                        $validated['end_date'] = $recurringEnd;
                    }

                    // Create booking
                    $booking = Booking::create($validated);
                }

                // Log for debugging (no PII)
                Log::info('Booking created successfully', [
                    'booking_id' => $booking->id,
                    'booking_type' => $request->booking_type,
                ]);

                // Redirect using tracking_token (secure)
                return redirect()->route('booking.success', $booking->tracking_token)
                    ->with('success', 'Permintaan peminjaman berhasil diajukan!');
            }); // End DB::transaction

        } catch (ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Compute the end date of a recurring series based on the repeat selection.
     */
    private function computeRecurringEndDate(Carbon $startDate, $repeatType, $repeatCount, $repeatEndDate): ?string
    {
        if ($repeatType === 'count' && (int) $repeatCount > 1) {
            return $startDate->copy()->addWeeks((int) $repeatCount - 1)->toDateString();
        }

        if ($repeatType === 'date' && $repeatEndDate) {
            return Carbon::parse($repeatEndDate)->toDateString();
        }

        return null;
    }

    /**
     * Check lab availability across every occurrence of a recurring series.
     * Falls back to a single-date check when the series has no end date.
     */
    private function isLabAvailableForRange(Lab $lab, $day, $startTime, $endTime, Carbon $startDate, $endDate = null): bool
    {
        $current = $startDate->copy();
        $guard = 0;

        do {
            if (! $lab->isAvailable($day, $startTime, $endTime, $current->toDateString())) {
                return false;
            }

            $current->addWeek();
            $guard++;
        } while ($endDate && $current->lte(Carbon::parse($endDate)) && $guard < 60);

        return true;
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

        $showRecontactNotice = false;

        if (! $booking->isPribadi() && $booking->booking_date) {
            $showRecontactNotice = now()->diffInDays($booking->booking_date, false) > 3;
        }

        return view('booking.success', compact('booking', 'showRecontactNotice'));
    }

    /**
     * Show print view for downloading PDF
     */
    public function print($token)
    {
        $booking = Booking::with(['lab', 'handler'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        // Pribadi bookings don't have a print form (no date/time/lab)
        if ($booking->isPribadi()) {
            abort(404, 'Peminjaman pribadi tidak memiliki form cetak.');
        }

        return view('booking.print', compact('booking'));
    }
}
