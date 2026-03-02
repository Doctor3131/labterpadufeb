<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ScheduleDocument;
use App\Models\Lab;
use App\Models\Booking;
use App\Helpers\DayHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\ScheduleService;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Schedule types for dropdown
     * Note: 'regular' was merged into 'perkuliahan_tetap' as they serve the same purpose
     */
    protected array $types = [
        'perkuliahan_tetap' => 'Perkuliahan Tetap',
        'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
        'non_perkuliahan' => 'Non Perkuliahan',
    ];
    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        // Security Fix: Input Validation
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'month' => 'nullable|date_format:Y-m',
            'lab_id' => 'nullable|exists:labs,id',
            'day' => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'type' => 'nullable|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
            'search' => 'nullable|string|max:255',
        ]);

        $query = Schedule::with(['lab', 'booking']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Database-level sorting for performance (instead of in-memory sort)
        // Order: start_date DESC (nulls last) → day order (Senin-Sabtu) → start_time
        $schedules = $query
            ->orderByRaw('CASE WHEN start_date IS NULL THEN 1 ELSE 0 END') // nulls last
            ->orderByDesc('start_date')
            ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->orderBy('start_time')
            ->paginate(100)
            ->withQueryString();

        $labs = Lab::excludeWarehouse()->orderBy('name')->get();
        $types = $this->types;

        // Return partial view for AJAX requests
        if ($request->ajax()) {
            return view('admin.schedules.partials.table', compact('schedules'))->render();
        }

        return view('admin.schedules.index', compact('schedules', 'labs', 'types'));
    }

    /**
     * Show the form for creating a new schedule
     */
    public function create()
    {
        $labs = Lab::excludeWarehouse()->orderBy('name')->get();
        $days = DayHelper::SCHEDULE_DAYS;
        $types = $this->types;

        return view('admin.schedules.form', [
            'schedule' => null,
            'labs' => $labs,
            'days' => $days,
            'types' => $types,
            'isEdit' => false,
        ]);
    }

    /**
     * Get available labs based on day, time, and date range
     * Used by AJAX to dynamically filter labs that don't have conflicts
     */
    public function getAvailableLabs(Request $request)
    {
        $day = $request->day;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $excludeScheduleId = $request->exclude_schedule_id;

        // Validate required fields
        if (!$day || !$startTime || !$endTime) {
            return response()->json([]);
        }

        // Get all labs with schedules and bookings eager loaded
        // Only get labs that are available (not in maintenance)
        $labs = Lab::excludeWarehouse()->where('status', 'available')
            ->with(['schedules', 'bookings'])
            ->orderBy('name')
            ->get();

        // Filter available labs
        $availableLabs = $labs->filter(function ($lab) use ($day, $startTime, $endTime, $startDate, $endDate, $excludeScheduleId) {
            return $this->isLabAvailableForSchedule($lab, $day, $startTime, $endTime, $startDate, $endDate, $excludeScheduleId);
        });

        // Return labs with id, name, and capacity
        return response()->json($availableLabs->map(function ($lab) {
            return [
                'id' => $lab->id,
                'name' => $lab->name,
                'capacity' => $lab->capacity,
            ];
        })->values());
    }

    /**
     * Check if a lab is available for the given schedule criteria
     * Returns true if no conflicts exist
     */
    private function isLabAvailableForSchedule($lab, $day, $startTime, $endTime, $startDate, $endDate, $excludeScheduleId = null)
    {
        // Query for conflicting schedules
        $query = $lab->schedules()
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                // Time overlap check
                $q->whereTime('start_time', '<', $endTime)
                  ->whereTime('end_time', '>', $startTime);
            });

        // Exclude current schedule if editing
        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        // Date range conflict check
        if ($startDate || $endDate) {
            // If the new schedule has dates, check overlap with existing schedules
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($q2) use ($startDate, $endDate) {
                    // Existing schedule has no end_date (permanent)
                    $q2->whereNull('end_date')
                       ->where(function ($q3) use ($endDate, $startDate) {
                           $q3->whereNull('start_date')
                              ->orWhere('start_date', '<=', $endDate ?? $startDate);
                       });
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    // Existing schedule has date range
                    $q2->whereNotNull('start_date')
                       ->where('start_date', '<=', $endDate ?? $startDate)
                       ->where(function ($q3) use ($startDate) {
                           $q3->whereNull('end_date')
                              ->orWhere('end_date', '>=', $startDate);
                       });
                });
            });
        } else {
            // New schedule is permanent (no dates), conflicts with any existing schedule on that day/time
            // No additional date filtering needed - any overlap is a conflict
        }

        $hasScheduleConflict = $query->exists();

        if ($hasScheduleConflict) {
            return false;
        }

        // Check pending bookings only if dates are specified
        if ($startDate || $endDate) {
            $pendingQuery = $lab->bookings()
                ->where('day', $day)
                ->where('status', 'pending')
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->whereTime('start_time', '<', $endTime)
                      ->whereTime('end_time', '>', $startTime);
                });

            // Filter by date range
            if ($startDate && $endDate) {
                $pendingQuery->whereBetween('booking_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $pendingQuery->where('booking_date', '>=', $startDate);
            } else {
                $pendingQuery->where('booking_date', '<=', $endDate);
            }

            if ($pendingQuery->exists()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        // Get validation rules using helper method (DRY)
        $rules = $this->getScheduleValidationRules($request);
        $validated = $request->validate($rules);

        // Map data using Service (DRY)
        $scheduleData = ScheduleService::mapFromRequest($validated, $request->type);

        // Validate day exists in date range
        $dayValidation = $this->validateDayInDateRange(
            $validated['day'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        if ($dayValidation !== true) {
            return back()
                ->withErrors(['day' => $dayValidation])
                ->withInput();
        }

        // Check for conflicts
        $conflict = ScheduleService::checkConflict(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            null
        );

        if ($conflict) {
            return back()
                ->withErrors(['conflict' => 'Jadwal bentrok dengan jadwal yang sudah ada: ' . $conflict])
                ->withInput();
        }

        // Check for conflicts with pending bookings - BLOCKER
        $pendingConflict = $this->checkPendingBookings(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        if ($pendingConflict) {
            return back()
                ->withErrors(['conflict' => $pendingConflict])
                ->withInput();
        }

        $schedule = Schedule::create($scheduleData);

        // Save document fields if any provided
        $this->saveDocumentData($schedule, $request);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a schedule
     */
    public function edit($id)
    {
        $schedule = Schedule::with(['booking', 'document'])->findOrFail($id);
        $labs = Lab::excludeWarehouse()->orderBy('name')->get();
        $days = DayHelper::SCHEDULE_DAYS;
        $types = $this->types;

        return view('admin.schedules.form', [
            'schedule' => $schedule,
            'labs' => $labs,
            'days' => $days,
            'types' => $types,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::with('booking')->findOrFail($id);
        
        // Get validation rules using helper method (DRY)
        $rules = $this->getScheduleValidationRules($request);
        $validated = $request->validate($rules);

        // Map data using Service (DRY)
        $scheduleData = ScheduleService::mapFromRequest($validated, $request->type);

        // Validate day exists in date range
        $dayValidation = $this->validateDayInDateRange(
            $validated['day'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        if ($dayValidation !== true) {
            return back()
                ->withErrors(['day' => $dayValidation])
                ->withInput();
        }

        // Check for conflicts (excluding current schedule)
        $conflict = ScheduleService::checkConflict(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            $id
        );

        if ($conflict) {
            return back()
                ->withErrors(['conflict' => 'Jadwal bentrok dengan jadwal yang sudah ada: ' . $conflict])
                ->withInput();
        }

        // Check for conflicts with pending bookings - BLOCKER
        $pendingConflict = $this->checkPendingBookings(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            $schedule->booking_id // Exclude current booking if schedule is from booking
        );

        if ($pendingConflict) {
            return back()
                ->withErrors(['conflict' => $pendingConflict])
                ->withInput();
        }

        DB::transaction(function () use ($schedule, $scheduleData, $validated, $request) {
            // Update schedule
            $schedule->update($scheduleData);

            // Sync changes to booking if exists
            if ($schedule->booking) {
                $bookingData = [
                    'lab_id' => $validated['lab_id'],
                    'booking_date' => $validated['start_date'] ?? $schedule->booking->booking_date, // update date if changed
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'participant_count' => $validated['student_count'] ?? $schedule->booking->participant_count,
                    'booking_type' => $validated['type'], // Also update type
                ];

                // Sync type-specific fields
                if ($request->type === 'perkuliahan_tetap' || $request->type === 'perkuliahan_tidak_tetap') {
                    $bookingData['course_name'] = $validated['course_name'];
                    $bookingData['lecturer_name'] = $validated['lecturer_name'];
                    if (isset($validated['komting'])) {
                         $bookingData['pic_name'] = $validated['komting'] ?? $schedule->booking->pic_name;
                    }
                    
                } elseif ($request->type === 'non_perkuliahan') {
                    $bookingData['activity_name'] = $validated['activity_name'];
                    $bookingData['activity_type'] = $validated['activity_type'];
                    $bookingData['position'] = $validated['position'];
                    $bookingData['equipment_needs'] = $validated['equipment_needs'] ?? null;
                    $bookingData['pic_name'] = $validated['pic_name_non_perkuliahan'] ?? $schedule->booking->pic_name;
                }
                // Note: no else branch needed - validation only accepts 3 valid types

                $schedule->booking->update($bookingData);
            }

            // Save document fields
            $this->saveDocumentData($schedule, $request);
        });

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui!');
    }

    /**
     * Remove the specified schedule
     */
    public function destroy($id)
    {
        $schedule = Schedule::with('booking')->findOrFail($id);
        
        $info = $schedule->course . ' (' . $schedule->day . ')';
        
        DB::transaction(function () use ($schedule) {
            // If schedule has booking, mark booking as deleted
            if ($schedule->booking) {
                $bookingUpdate = [
                    'status' => 'deleted',
                    'handled_at' => now(),
                ];
                
                if (Auth::check()) {
                    $bookingUpdate['handled_by'] = Auth::id();
                }
                
                $schedule->booking->update($bookingUpdate);
            }
            
            // Delete the schedule
            $schedule->delete();
        });

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal "' . $info . '" berhasil dihapus!');
    }



    /**
     * Validate that the selected day exists within the date range
     */
    private function validateDayInDateRange($selectedDay, $startDate, $endDate)
    {
        // If no date range specified, validation passes (schedule berlaku selamanya)
        if (!$startDate && !$endDate) {
            return true;
        }

        // If only one date is specified, use it for both start and end
        $start = Carbon::parse($startDate ?? $endDate);
        $end = Carbon::parse($endDate ?? $startDate);

        // IMPORTANT: Validate that start date matches the selected day
        $startDayName = DayHelper::fromDate($start);
        if ($startDayName !== $selectedDay) {
            $formattedStart = $start->format('d/m/Y');
            return "Tanggal mulai ({$formattedStart}) adalah hari {$startDayName}, tetapi hari yang dipilih adalah {$selectedDay}. Silakan pilih tanggal mulai yang jatuh pada hari {$selectedDay}.";
        }

        // Check if selected day exists in the date range
        $dayFound = false;
        $currentDate = $start->copy();

        while ($currentDate->lte($end)) {
            if (DayHelper::fromDate($currentDate) === $selectedDay) {
                $dayFound = true;
                break;
            }
            $currentDate->addDay();
        }

        if (!$dayFound) {
            $formattedStart = $start->format('d/m/Y');
            $formattedEnd = $end->format('d/m/Y');
            return "Hari {$selectedDay} tidak ditemukan dalam rentang tanggal {$formattedStart} - {$formattedEnd}. Silakan pilih rentang tanggal yang mengandung hari {$selectedDay} atau ubah pilihan hari.";
        }

        return true;
    }



    /**
     * Check for pending bookings that conflict with the schedule
     * Returns an error message if any pending bookings are found, null otherwise
     */
    private function checkPendingBookings($labId, $day, $startTime, $endTime, $startDate, $endDate, $excludeBookingId = null)
    {
        $query = Booking::where('lab_id', $labId)
            ->where('day', $day)
            ->where('status', 'pending')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereTime('start_time', '<', $endTime)
                  ->whereTime('end_time', '>', $startTime);
            });

        // Exclude specific booking if needed (for update scenario)
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        // If dates are specified, filter bookings within date range
        if ($startDate || $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $q->whereBetween('booking_date', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $q->where('booking_date', '>=', $startDate);
                } else {
                    $q->where('booking_date', '<=', $endDate);
                }
            });
        }

        $pendingBookings = $query->get();

        if ($pendingBookings->count() > 0) {
            $bookingList = $pendingBookings->map(function ($booking) {
                $name = $booking->course_name ?? $booking->activity_name ?? 'Peminjaman';
                $date = Carbon::parse($booking->booking_date)->format('d/m/Y');
                $time = Carbon::parse($booking->start_time)->format('H:i') . ' - ' . Carbon::parse($booking->end_time)->format('H:i');
                return "{$name} ({$date}, {$time})";
            })->join(', ');

            return "Tidak dapat membuat jadwal. Terdapat {$pendingBookings->count()} peminjaman pending yang bentrok: {$bookingList}. Silakan tolak atau approve peminjaman tersebut terlebih dahulu.";
        }

        return null;
    }

    /**
     * Get validation rules based on schedule type
     * Eliminates duplication between store() and update()
     */
    private function getScheduleValidationRules(Request $request): array
    {
        // Base validation rules
        $rules = [
            'lab_id' => 'required|exists:labs,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'student_count' => 'required|integer|min:1',
        ];

        // Conditional validation based on type
        if ($request->type === 'perkuliahan_tetap' || $request->type === 'perkuliahan_tidak_tetap') {
            $rules['course_name'] = 'required|string|max:255';
            $rules['lecturer_name'] = 'required|string|max:255';
            $rules['komting'] = 'nullable|string|max:255';
            $rules['komting_phone'] = 'nullable|string|max:20';
        } elseif ($request->type === 'non_perkuliahan') {
            $rules['activity_name'] = 'required|string|max:255';
            $rules['activity_type'] = 'required|in:' . implode(',', Booking::ACTIVITY_TYPES);
            $rules['position'] = 'nullable|string|max:255';
            $rules['equipment_needs'] = 'nullable|string|max:1000';
            $rules['pic_name_non_perkuliahan'] = 'required|string|max:255';
        }

        // Document fields (optional, for all types)
        $rules['study_program'] = 'nullable|string|max:255';
        $rules['nim'] = 'nullable|string|max:50';
        $rules['nip'] = 'nullable|string|max:50';
        $rules['lecturer_nip'] = 'nullable|string|max:50';
        $rules['doc_phone_number'] = 'nullable|string|max:20';
        $rules['software_needs'] = 'nullable|string|max:1000';
        $rules['ktm_file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'; // 5MB

        return $rules;
    }





    /**
     * Apply filters to the schedule query
     */
    private function applyFilters($query, Request $request)
    {
        // Filter by Lab
        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Day (manual day selection - independent of date filter)
        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }

        // Filter by Search (course/activity name or lecturer)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('course', 'like', $searchTerm)
                  ->orWhere('lecturer', 'like', $searchTerm)
                  ->orWhere('komting', 'like', $searchTerm);
            });
        }

        // Filter by Date (specific date) - HIGHEST PRIORITY
        if ($request->filled('date')) {
            // Note: Validation handled in index method
            try {
                $date = Carbon::parse($request->date);
                $dayName = DayHelper::fromDate($date);
                
                $query->where('day', $dayName)
                      ->where(function ($q) use ($request) {
                          $q->where(function ($q2) use ($request) {
                              // Has start_date, check range
                              $q2->whereNotNull('start_date')
                                 ->where('start_date', '<=', $request->date)
                                 ->where(function ($q3) use ($request) {
                                     $q3->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $request->date);
                                 });
                          })->orWhere(function ($q2) use ($request) {
                              // No start_date (recurring), check end_date only
                              $q2->whereNull('start_date')
                                 ->where(function ($q3) use ($request) {
                                     $q3->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $request->date);
                                 });
                          });
                      });
            } catch (\Exception $e) {
                // Ignore parser error here as it's handled in index
            }
        } elseif ($request->filled('month')) {
            // Filter by Month (broader than date) - SECOND PRIORITY
            $yearMonth = $request->month; // Format: "2026-01"
            $firstDayOfMonth = Carbon::parse($yearMonth . '-01');
            $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
            
            $query->where(function ($q) use ($firstDayOfMonth, $lastDayOfMonth) {
                $q->where(function ($q2) use ($firstDayOfMonth, $lastDayOfMonth) {
                    // Has start_date, check if schedule is active during this month
                    $q2->whereNotNull('start_date')
                       ->where('start_date', '<=', $lastDayOfMonth->format('Y-m-d'))
                       ->where(function ($q3) use ($firstDayOfMonth) {
                           $q3->whereNull('end_date')
                              ->orWhere('end_date', '>=', $firstDayOfMonth->format('Y-m-d'));
                       });
                })->orWhere(function ($q2) use ($firstDayOfMonth) {
                    // No start_date (recurring), check if still active in this month
                    $q2->whereNull('start_date')
                       ->where(function ($q3) use ($firstDayOfMonth) {
                           $q3->whereNull('end_date')
                              ->orWhere('end_date', '>=', $firstDayOfMonth->format('Y-m-d'));
                       });
                });
            });
        } else {
            // No specific date/month - show active/future schedules only - DEFAULT
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    // Has end_date in the future
                    $q2->whereNotNull('end_date')
                       ->where('end_date', '>=', now()->format('Y-m-d'));
                })->orWhere(function ($q2) {
                    // No end_date (permanent/recurring)
                    $q2->whereNull('end_date');
                });
            });
        }
    }

    /**
     * Save or update document data for a schedule
     */
    private function saveDocumentData(Schedule $schedule, Request $request): void
    {
        $documentFields = [
            'study_program', 'nim', 'nip', 'lecturer_nip',
            'software_needs',
        ];

        // Only save phone to document for non_perkuliahan (perkuliahan uses schedules.komting_phone)
        $docData = $request->only($documentFields);
        if ($schedule->type === 'non_perkuliahan') {
            $docData['phone_number'] = $request->input('doc_phone_number');
        } else {
            $docData['phone_number'] = null; // Clear for perkuliahan types
        }

        // Handle KTM file upload
        if ($request->hasFile('ktm_file')) {
            // Delete old KTM if exists
            if ($schedule->document && $schedule->document->ktm_path) {
                Storage::disk('public')->delete($schedule->document->ktm_path);
            }
            $docData['ktm_path'] = $request->file('ktm_file')->store('ktm', 'public');
        }

        // Only save if any document field has a value
        $hasData = collect($docData)->filter()->isNotEmpty();
        if ($hasData || ($schedule->document && $schedule->document->exists)) {
            $schedule->document()
                ->updateOrCreate(
                    ['schedule_id' => $schedule->id],
                    $docData
                );
        }
    }

    /**
     * Print schedule document
     */
    public function print($id)
    {
        $schedule = Schedule::with(['lab', 'booking', 'document'])->findOrFail($id);

        return view('admin.schedules.print', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * Delete KTM file for a schedule
     */
    public function deleteKtm($id)
    {
        $schedule = Schedule::with('document')->findOrFail($id);

        if ($schedule->document && $schedule->document->ktm_path) {
            Storage::disk('public')->delete($schedule->document->ktm_path);
            $schedule->document->update(['ktm_path' => null]);
        }

        return back()->with('success', 'File KTM berhasil dihapus.');
    }
}

