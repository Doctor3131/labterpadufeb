<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Lab;
use App\Models\Booking;
use App\Helpers\DayHelper;
use Illuminate\Http\Request;
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

        // Filter Logic: Date OR Day
        if ($request->filled('date') || $request->filled('day')) {
            $query->where(function($q) use ($request) {
                // 1. Filter by Specific Date
                if ($request->filled('date')) {
                    $q->orWhere(function($subQ) use ($request) {
                        $date = Carbon::parse($request->date);
                        $dayName = $this->getDayOfWeekInIndonesian($date);
                        
                        // Match Day of the specific date
                        $subQ->where('day', $dayName);

                        // Match Date Range
                        $subQ->where(function ($q2) use ($request) {
                            $q2->where(function ($q3) use ($request) {
                                // Specific date range
                                $q3->whereNotNull('start_date')
                                   ->where('start_date', '<=', $request->date)
                                   ->where(function ($q4) use ($request) {
                                       $q4->whereNull('end_date')
                                          ->orWhere('end_date', '>=', $request->date);
                                   });
                            })->orWhere(function ($q3) use ($request) {
                                // Recurring (no start_date) but check end_date
                                $q3->whereNull('start_date')
                                   ->where(function ($q4) use ($request) {
                                       $q4->whereNull('end_date')
                                          ->orWhere('end_date', '>=', $request->date);
                                   });
                            });
                        });
                    });
                }

                // 2. Filter by Day (Explicit)
                if ($request->filled('day')) {
                    $q->orWhere(function($subQ) use ($request) {
                        $subQ->where('day', $request->day);
                        
                        // Apply standard "Active" check for general day filter
                        // so we don't show expired schedules for that day
                        $subQ->where(function ($q2) {
                            $q2->where(function ($q3) {
                                // Has end_date and it's today or in the future
                                $q3->whereNotNull('end_date')
                                   ->where('end_date', '>=', now()->format('Y-m-d'));
                            })->orWhere(function ($q3) {
                                // No end_date (recurring/permanent schedule)
                                $q3->whereNull('end_date');
                            });
                        });
                    });
                }
            });
        } elseif (!$request->filled('date')) {
            // Default "Active" filter if NO Date/Day filter is applied
            // (If Date is applied above, we handled validity inside; if Day is applied, we handled inside)
            // Actually, if ONLY Day was applied above, we handled it.
            // If ONLY Date was applied, we handled it.
            // So this `elseif` handles the "No Date AND No Day" case (Pure initial load or only Type/Lab filter)
            
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('end_date')
                       ->where('end_date', '>=', now()->format('Y-m-d'));
                })->orWhere(function ($q2) {
                    $q2->whereNull('end_date');
                });
            });
        }

        $schedules = $query->get();

        // Custom Sorting in PHP using DayHelper
        $schedules = $schedules->sort(function ($a, $b) {
            $dayA = DayHelper::getOrder($a->day);
            $dayB = DayHelper::getOrder($b->day);

            if ($dayA === $dayB) {
                return $a->start_time <=> $b->start_time;
            }
            return $dayA <=> $dayB;
        });

        $labs = Lab::orderBy('name')->get();
        
        $days = DayHelper::SCHEDULE_DAYS;
        $types = $this->types;

        return view('admin.schedules.index', compact('schedules', 'labs', 'days', 'types'));
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
     * Store a newly created schedule
     */
    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'lab_id' => 'required|exists:labs,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:regular,perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'student_count' => 'nullable|integer|min:1',
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
        }

        $validated = $request->validate($rules);

        // Map request fields to schedule columns
        $scheduleData = [
            'lab_id' => $validated['lab_id'],
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'student_count' => $validated['student_count'] ?? null,
        ];

        // Map course/lecturer/komting based on type
        if ($request->type === 'perkuliahan_tetap' || $request->type === 'perkuliahan_tidak_tetap') {
            $scheduleData['course'] = $validated['course_name'];
            $scheduleData['lecturer'] = $validated['lecturer_name'];
            $scheduleData['komting'] = $validated['komting'] ?? null;
        } elseif ($request->type === 'non_perkuliahan') {
            $scheduleData['course'] = $validated['activity_name'];
            $scheduleData['lecturer'] = null;
            $scheduleData['komting'] = null;
        } elseif ($request->type === 'pribadi') {
            $scheduleData['course'] = $validated['purpose'];
            $scheduleData['lecturer'] = null;
            $scheduleData['komting'] = null;
        }

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
                ->withErrors(['conflict' => 'Jadwal bentrok dengan: ' . $conflict])
                ->withInput();
        }

        Schedule::create($scheduleData);

        // Check for pending bookings that might conflict (warning only)
        $pendingBookingWarning = $this->checkPendingBookings(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        $successMessage = 'Jadwal berhasil ditambahkan!';
        if ($pendingBookingWarning) {
            $successMessage .= ' ΓÜá∩╕Å Perhatian: ' . $pendingBookingWarning;
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', $successMessage);
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
        
        // Base validation rules
        $rules = [
            'lab_id' => 'required|exists:labs,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:regular,perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'student_count' => 'nullable|integer|min:1',
        ];

        // Conditional validation and data mapping based on type
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
            // Regular (manual) fallback
            $rules['course'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        // Map request fields to schedule columns (generic columns)
        $scheduleData = [
            'lab_id' => $validated['lab_id'],
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'type' => $validated['type'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'student_count' => $validated['student_count'] ?? null,
        ];

        // Map course/lecturer/komting based on type
        if ($request->type === 'perkuliahan_tetap' || $request->type === 'perkuliahan_tidak_tetap') {
            $scheduleData['course'] = $validated['course_name'];
            $scheduleData['lecturer'] = $validated['lecturer_name'];
            $scheduleData['komting'] = $validated['komting'] ?? null;
        } elseif ($request->type === 'non_perkuliahan') {
            $scheduleData['course'] = $validated['activity_name'];
            $scheduleData['lecturer'] = null;
            $scheduleData['komting'] = null;
        } elseif ($request->type === 'pribadi') {
            $scheduleData['course'] = $validated['purpose'];
            $scheduleData['lecturer'] = null;
            $scheduleData['komting'] = null;
        }

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
                ->withErrors(['conflict' => 'Jadwal bentrok dengan: ' . $conflict])
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

        // Check for pending bookings that might conflict (warning only)
        $pendingBookingWarning = $this->checkPendingBookings(
            $validated['lab_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        $successMessage = 'Jadwal berhasil diperbarui!';
        if ($pendingBookingWarning) {
            $successMessage .= ' ΓÜá∩╕Å Perhatian: ' . $pendingBookingWarning;
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', $successMessage);
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
                $schedule->booking->update([
                    'status' => 'deleted',
                    'handled_by' => auth()->id(),
                    'handled_at' => now(),
                ]);
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
        $startDayName = $this->getDayOfWeekInIndonesian($start);
        if ($startDayName !== $selectedDay) {
            $formattedStart = $start->format('d/m/Y');
            return "Tanggal mulai ({$formattedStart}) adalah hari {$startDayName}, tetapi hari yang dipilih adalah {$selectedDay}. Silakan pilih tanggal mulai yang jatuh pada hari {$selectedDay}.";
        }

        // Check if selected day exists in the date range
        $dayFound = false;
        $currentDate = $start->copy();

        while ($currentDate->lte($end)) {
            if ($this->getDayOfWeekInIndonesian($currentDate) === $selectedDay) {
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
     * Get Indonesian day name from Carbon date
     */
    private function getDayOfWeekInIndonesian($date)
    {
        return DayHelper::fromDate($date);
    }

    /**
     * Check for pending bookings that might conflict with the schedule
     * Returns a warning message if any pending bookings are found, null otherwise
     */
    private function checkPendingBookings($labId, $day, $startTime, $endTime, $startDate, $endDate)
    {
        $query = Booking::where('lab_id', $labId)
            ->where('day', $day)
            ->where('status', 'pending')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereTime('start_time', '<', $endTime)
                  ->whereTime('end_time', '>', $startTime);
            });

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
                return "{$name} ({$date})";
            })->join(', ');

            return "Ada {$pendingBookings->count()} booking pending yang mungkin konflik: {$bookingList}. Booking tersebut perlu ditolak atau diubah waktunya.";
        }

        return null;
    }
}
