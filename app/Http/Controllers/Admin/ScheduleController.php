<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Lab;
use App\Models\Booking;
use App\Helpers\DayHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        'pribadi' => 'Pribadi',
    ];
    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['lab', 'booking']);

        // Filter by Lab
        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        // Filter by Date (specific date)
        if ($request->filled('date')) {
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
        } else {
            // No specific date - show active/future schedules only
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

        // Database-level sorting for performance (instead of in-memory sort)
        // Order: start_date DESC (nulls last) → day order (Senin-Sabtu) → start_time
        $schedules = $query
            ->orderByRaw('CASE WHEN start_date IS NULL THEN 1 ELSE 0 END') // nulls last
            ->orderByDesc('start_date')
            ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->orderBy('start_time')
            ->get();

        $labs = Lab::orderBy('name')->get();
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
        $labs = Lab::orderBy('name')->get();
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
        $labs = Lab::where('status', 'available')
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

        // Map data using helper method (DRY)
        $scheduleData = $this->mapScheduleData($validated, $request->type);

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
        $conflict = $this->checkConflict(
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

        Schedule::create($scheduleData);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a schedule
     */
    public function edit($id)
    {
        $schedule = Schedule::with('booking')->findOrFail($id);
        $labs = Lab::orderBy('name')->get();
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

        // Map data using helper method (DRY)
        $scheduleData = $this->mapScheduleData($validated, $request->type);

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
        $conflict = $this->checkConflict(
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
                    // Booking model might put komting in pic_name or similar, check existing usage
                    // Assuming pic_name usually holds the applicant name, but if admin puts komting here maybe it maps?
                    // Let's stick to existing logic: $bookingData['pic_name'] = $validated['komting'] ?? ...
                    if (isset($validated['komting'])) {
                         // Careful: pic_name is the applicant/account. We probably shouldn't override it with komting unless that was the intent.
                         // But the original code did: $bookingData['pic_name'] = $validated['komting'] ?? ...
                         // Let's check if komting is provided.
                         // Actually, for perkuliahan, the applicant might be a rep.
                         // Let's keep existing logic but be safer.
                         // The original code: $bookingData['pic_name'] = $validated['komting'] ?? $schedule->booking->pic_name;
                         // We will replicate that logic but using the mapped variables.
                         $bookingData['pic_name'] = $validated['komting'] ?? $schedule->booking->pic_name;
                    }
                    
                } elseif ($request->type === 'non_perkuliahan') {
                    $bookingData['activity_name'] = $validated['activity_name'];
                    $bookingData['activity_type'] = $validated['activity_type'];
                    $bookingData['position'] = $validated['position'];
                    $bookingData['equipment_needs'] = $request->equipment_needs; // optional field
                    
                } elseif ($request->type === 'pribadi') {
                    $bookingData['purpose'] = $validated['purpose'];
                    $bookingData['applicant_status'] = $validated['applicant_status'];
                    
                    if (isset($validated['class_year'])) {
                        $bookingData['class_year'] = $validated['class_year'];
                    } else {
                        $bookingData['class_year'] = null;
                    }
                    
                    // Save custom_status when applicant_status is "Lainnya"
                    if (isset($validated['custom_status'])) {
                        $bookingData['custom_status'] = $validated['custom_status'];
                    } else {
                        $bookingData['custom_status'] = null;
                    }
                } else {
                    $bookingData['activity_name'] = $validated['course'];
                }

                $schedule->booking->update($bookingData);
            }
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
     * Check for schedule conflicts
     */
    private function checkConflict($labId, $day, $startTime, $endTime, $startDate, $endDate, $excludeId = null)
    {
        $query = Schedule::where('lab_id', $labId)
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereTime('start_time', '<', $endTime)
                  ->whereTime('end_time', '>', $startTime);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Date overlap check for dated schedules
        if ($startDate || $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($q2) use ($startDate, $endDate) {
                    $q2->whereNull('end_date')
                       ->where(function ($q3) use ($endDate, $startDate) {
                           $q3->whereNull('start_date')
                              ->orWhere('start_date', '<=', $endDate ?? $startDate);
                       });
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->whereNotNull('start_date')
                       ->where('start_date', '<=', $endDate ?? $startDate)
                       ->where(function ($q3) use ($startDate) {
                           $q3->whereNull('end_date')
                              ->orWhere('end_date', '>=', $startDate);
                       });
                });
            });
        }

        $conflicting = $query->first();

        if ($conflicting) {
            $timeRange = Carbon::parse($conflicting->start_time)->format('H:i') . 
                         ' - ' . 
                         Carbon::parse($conflicting->end_time)->format('H:i');
            return $conflicting->course . ' (' . $timeRange . ')';
        }

        return null;
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
                $name = $booking->course_name ?? $booking->activity_name ?? 'Peminjaman Pribadi';
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
            'type' => 'required|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'student_count' => 'required|integer|min:1',
        ];

        // Conditional validation based on type
        if ($request->type === 'perkuliahan_tetap' || $request->type === 'perkuliahan_tidak_tetap') {
            $rules['course_name'] = 'required|string|max:255';
            $rules['lecturer_name'] = 'required|string|max:255';
            $rules['komting'] = 'nullable|string|max:255';
        } elseif ($request->type === 'non_perkuliahan') {
            $rules['activity_name'] = 'required|string|max:255';
            $rules['activity_type'] = 'required|in:Seminar,Workshop,Pelatihan,Rapat,Ujian,Lainnya';
            $rules['position'] = 'required|string|max:255';
        } elseif ($request->type === 'pribadi') {
            $rules['purpose'] = 'required|string|max:255';
            $rules['applicant_status'] = 'required|in:Mahasiswa,Dosen,Pegawai,Lainnya';
            
            // Validate class_year only if status is Mahasiswa
            if ($request->applicant_status === 'Mahasiswa') {
                $rules['class_year'] = 'required|string|max:4';
            }
            
            // Validate custom_status if status is Lainnya
            if ($request->applicant_status === 'Lainnya') {
                $rules['custom_status'] = 'required|string|max:255';
            }
        } else {
            // Regular type - fallback
            $rules['course'] = 'required|string|max:255';
        }

        return $rules;
    }

    /**
     * Map validated data to schedule columns based on type
     * Eliminates duplication between store() and update()
     */
    private function mapScheduleData(array $validated, string $type): array
    {
        // Base schedule data
        $scheduleData = [
            'lab_id' => $validated['lab_id'],
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'student_count' => $validated['student_count'],
        ];

        // Map course/lecturer/komting based on type
        if ($type === 'perkuliahan_tetap' || $type === 'perkuliahan_tidak_tetap') {
            $scheduleData['course'] = $validated['course_name'];
            $scheduleData['lecturer'] = $validated['lecturer_name'];
            $scheduleData['komting'] = $validated['komting'] ?? null;
        } elseif ($type === 'non_perkuliahan') {
            $scheduleData['course'] = $validated['activity_name'];
            $scheduleData['lecturer'] = null;
            $scheduleData['komting'] = null;
        } elseif ($type === 'pribadi') {
            $scheduleData['course'] = $validated['purpose'];
            $scheduleData['lecturer'] = null;
            $scheduleData['komting'] = null;
        } else {
            // Regular type - fallback
            $scheduleData['course'] = $validated['course'] ?? 'Peminjaman';
            $scheduleData['lecturer'] = null;
            $scheduleData['komting'] = null;
        }

        return $scheduleData;
    }
}

