<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display a listing of schedules
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['lab', 'booking'])
            ->orderBy('day')
            ->orderBy('start_time');

        // Filter by lab
        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by day
        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }

        // Filter out past schedules (only show active or future schedules)
        // A schedule is considered past if:
        // 1. It has an end_date AND end_date is before today
        // 2. It has a start_date but no end_date AND start_date is before today
        $query->where(function ($q) {
            $q->where(function ($q2) {
                // Has end_date and it's today or in the future
                $q2->whereNotNull('end_date')
                   ->where('end_date', '>=', now()->format('Y-m-d'));
            })->orWhere(function ($q2) {
                // No end_date (recurring/permanent schedule)
                $q2->whereNull('end_date');
            });
        });


        $schedules = $query->get();
        $labs = Lab::orderBy('name')->get();
        
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $types = [
            'regular' => 'Regular',
            'perkuliahan_tetap' => 'Perkuliahan Tetap',
            'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
            'non_perkuliahan' => 'Non Perkuliahan',
            'pribadi' => 'Pribadi',
        ];

        return view('admin.schedules.index', compact('schedules', 'labs', 'days', 'types'));
    }

    /**
     * Show the form for creating a new schedule
     */
    public function create()
    {
        $labs = Lab::orderBy('name')->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $types = [
            'regular' => 'Regular (Manual)',
            'perkuliahan_tetap' => 'Perkuliahan Tetap',
            'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
            'non_perkuliahan' => 'Non Perkuliahan',
            'pribadi' => 'Pribadi',
        ];

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
        $validated = $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:regular,perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'course' => 'required|string|max:255',
            'lecturer' => 'nullable|string|max:255',
            'komting' => 'nullable|string|max:255',
            'student_count' => 'nullable|integer|min:1',
        ]);

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

        // Validate student count does not exceed lab capacity (Removed strict check)
        // if (isset($validated['student_count']) && $validated['student_count'] > 0) {
        //     $lab = Lab::findOrFail($validated['lab_id']);
        //     if ($validated['student_count'] > $lab->capacity) {
        //         return back()
        //             ->withErrors(['student_count' => "Jumlah mahasiswa ({$validated['student_count']}) melebihi kapasitas lab {$lab->name} ({$lab->capacity} orang). Silakan kurangi jumlah mahasiswa atau pilih lab dengan kapasitas lebih besar."])
        //             ->withInput();
        //     }
        // }


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

        Schedule::create($validated);

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
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $types = [
            'regular' => 'Regular (Manual)',
            'perkuliahan_tetap' => 'Perkuliahan Tetap',
            'perkuliahan_tidak_tetap' => 'Perkuliahan Tidak Tetap',
            'non_perkuliahan' => 'Non Perkuliahan',
            'pribadi' => 'Pribadi',
        ];

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

        $validated = $request->validate([
            'lab_id' => 'required|exists:labs,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|in:regular,perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan,pribadi',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'course' => 'required|string|max:255',
            'lecturer' => 'nullable|string|max:255',
            'komting' => 'nullable|string|max:255',
            'student_count' => 'nullable|integer|min:1',
        ]);

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

        // Validate student count does not exceed lab capacity (Removed strict check)
        // if (isset($validated['student_count']) && $validated['student_count'] > 0) {
        //     $lab = Lab::findOrFail($validated['lab_id']);
        //     if ($validated['student_count'] > $lab->capacity) {
        //         return back()
        //             ->withErrors(['student_count' => "Jumlah mahasiswa ({$validated['student_count']}) melebihi kapasitas lab {$lab->name} ({$lab->capacity} orang). Silakan kurangi jumlah mahasiswa atau pilih lab dengan kapasitas lebih besar."])
        //             ->withInput();
        //     }
        // }


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

        DB::transaction(function () use ($schedule, $validated) {
            // Update schedule
            $schedule->update($validated);

            // Sync changes to booking if exists
            if ($schedule->booking) {
                $bookingData = [
                    'lab_id' => $validated['lab_id'],
                    'day' => $validated['day'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'participant_count' => $validated['student_count'] ?? $schedule->booking->participant_count,
                ];

                // Sync course/activity based on booking type
                if ($schedule->booking->isPerkuliahan()) {
                    $bookingData['course_name'] = $validated['course'];
                    $bookingData['lecturer_name'] = $validated['lecturer'];
                } else {
                    $bookingData['activity_name'] = $validated['course'];
                }

                // Sync PIC/komting
                $bookingData['pic_name'] = $validated['komting'] ?? $schedule->booking->pic_name;

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
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $days[$date->dayOfWeek];
    }
}
