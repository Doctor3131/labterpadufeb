<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\BpsRequest;
use App\Models\RefinitivRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show admin dashboard with 3 cards (Lab, BPS, Refinitiv)
     */
    public function dashboard()
    {
        // Get counts for each service
        $labPendingCount = Booking::where('status', 'pending')->count();
        $bpsPendingCount = BpsRequest::where('status', 'pending')->count();
        $refinitivPendingCount = RefinitivRequest::where('attendance_status', 'menunggu')->count();

        return view('admin.dashboard', compact(
            'labPendingCount',
            'bpsPendingCount',
            'refinitivPendingCount'
        ));
    }

    /**
     * Show lab bookings management
     */
    public function labBookings()
    {
        // Paginate each status separately with custom page parameter names
        // This allows independent pagination for each tab
        $pendingBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'pending_page');

        $approvedBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'approved')
            ->orderBy('handled_at', 'desc')
            ->paginate(15, ['*'], 'approved_page');

        $rejectedBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->paginate(15, ['*'], 'rejected_page');

        return view('admin.lab-bookings', compact('pendingBookings', 'approvedBookings', 'rejectedBookings'));
    }
    /**
     * Show booking detail
     */
    public function show($id)
    {
        $booking = Booking::with('lab')->findOrFail($id);
        return view('admin.booking-detail', compact('booking'));
    }

    /**
     * Approve booking
     */
    public function approve($id)
    {
        // Use transaction with lock to prevent race condition when 2 admins approve simultaneously
        return DB::transaction(function () use ($id) {
            // Lock the booking row to prevent concurrent approvals
            $booking = Booking::lockForUpdate()->findOrFail($id);
            
            // Check if already processed (race condition guard)
            if ($booking->status !== 'pending') {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
            }
            
            $isPribadi = $booking->booking_type === 'pribadi';
            
            // For non-pribadi bookings: check for schedule conflicts
            // Pribadi bookings skip conflict check - no lab, no schedule created
            if (!$isPribadi) {
                $bookingDate = Carbon::parse($booking->booking_date);
                $conflictCheck = $this->checkScheduleConflict(
                    $booking->lab_id,
                    $booking->day,
                    $booking->start_time,
                    $booking->end_time,
                    $bookingDate->format('Y-m-d'),
                    $booking->is_recurring ? null : $bookingDate->format('Y-m-d')
                );

                if ($conflictCheck) {
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'Tidak dapat menyetujui peminjaman: ' . $conflictCheck);
                }
            }
            
            // Update booking status
            $updateData = [
                'status' => 'approved',
                'handled_at' => now()
            ];
            
            if (Auth::check()) {
                $updateData['handled_by'] = Auth::id();
            }
            
            $booking->update($updateData);

            // Create schedule entry from approved booking
            // SKIP for pribadi bookings - they don't appear in public schedule
            if (!$isPribadi) {
                $bookingDate = Carbon::parse($booking->booking_date);
                
                $scheduleData = [
                    'lab_id' => $booking->lab_id,
                    'day' => $booking->day,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'booking_id' => $booking->id,
                    'student_count' => $booking->participant_count,
                ];

                // Tentukan type dan data spesifik berdasarkan booking_type
                if ($booking->is_recurring) {
                    // Perkuliahan tetap - recurring schedule
                    $scheduleData['type'] = 'perkuliahan_tetap';
                    $scheduleData['start_date'] = $bookingDate->toDateString();
                    $scheduleData['end_date'] = null; // Recurring tanpa batas
                    $scheduleData['course'] = $booking->course_name;
                    $scheduleData['lecturer'] = $booking->lecturer_name;
                    $scheduleData['komting'] = $booking->pic_name;
                } else {
                    // One-time booking - use booking_type directly
                    $scheduleData['type'] = $booking->booking_type;
                    $scheduleData['start_date'] = $bookingDate->toDateString();
                    $scheduleData['end_date'] = $bookingDate->toDateString();
                    
                    // Map data based on booking type
                    if ($booking->booking_type === 'perkuliahan_tidak_tetap') {
                        $scheduleData['course'] = $booking->course_name;
                        $scheduleData['lecturer'] = $booking->lecturer_name;
                        $scheduleData['komting'] = $booking->pic_name;
                    } elseif ($booking->booking_type === 'non_perkuliahan') {
                        $scheduleData['course'] = $booking->activity_name;
                        $scheduleData['lecturer'] = null;
                        $scheduleData['komting'] = null;
                    } else {
                        // Fallback for unknown booking type
                        $scheduleData['course'] = $booking->course_name ?? $booking->activity_name ?? 'Peminjaman';
                        $scheduleData['lecturer'] = null;
                        $scheduleData['komting'] = null;
                    }
                }

                Schedule::create($scheduleData);
            }
            
            return redirect()->route('admin.dashboard')
                ->with('success', 'Peminjaman berhasil disetujui!');
        }); // End DB::transaction
    }

    /**
     * Reject booking
     */
    public function reject(Request $request, $id)
    {
        Log::info('Reject booking called', [
            'booking_id' => $id,
            'reason' => $request->rejection_reason
        ]);

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $booking = Booking::findOrFail($id);
        
        Log::info('Booking found', [
            'booking_id' => $booking->id,
            'current_status' => $booking->status
        ]);
        
        DB::transaction(function () use ($booking, $request) {
            // Update booking status
            $updateData = [
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'handled_at' => now()
            ];
            
            if (Auth::check()) {
                $updateData['handled_by'] = Auth::id();
            }
            
            $booking->update($updateData);
            
            Log::info('Booking rejected successfully', [
                'booking_id' => $booking->id,
                'new_status' => $booking->status
            ]);
        });

        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil ditolak.');
    }

    /**
     * Check for schedule conflicts before approving a booking
     * Returns error message if conflict exists, null otherwise
     */
    private function checkScheduleConflict($labId, $day, $startTime, $endTime, $startDate, $endDate = null)
    {
        // Check for conflicts with existing schedules
        $conflictingSchedule = Schedule::where('lab_id', $labId)
            ->where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereTime('start_time', '<', $endTime)
                  ->whereTime('end_time', '>', $startTime);
            })
            ->where(function ($q) use ($startDate, $endDate) {
                // Date overlap check
                $q->where(function ($q2) use ($startDate, $endDate) {
                    // Permanent schedules (no end_date) that started before or on this date
                    $q2->whereNull('end_date')
                       ->where(function ($q3) use ($endDate, $startDate) {
                           $q3->whereNull('start_date')
                              ->orWhere('start_date', '<=', $endDate ?? $startDate);
                       });
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    // Scheduled with specific date range
                    $q2->whereNotNull('start_date')
                       ->where('start_date', '<=', $endDate ?? $startDate)
                       ->where(function ($q3) use ($startDate) {
                           $q3->whereNull('end_date')
                              ->orWhere('end_date', '>=', $startDate);
                       });
                });
            })
            ->first();

        if ($conflictingSchedule) {
            $timeRange = Carbon::parse($conflictingSchedule->start_time)->format('H:i') . 
                         ' - ' . 
                         Carbon::parse($conflictingSchedule->end_time)->format('H:i');
            $courseName = $conflictingSchedule->course ?? 'Jadwal';
            return "Bentrok dengan jadwal yang sudah ada: {$courseName} ({$timeRange})";
        }

        // Check for conflicts with OTHER pending bookings (exclude current one being approved)
        // This is handled by the unique constraint in the database, but we can add extra validation
        $conflictingBooking = Booking::where('lab_id', $labId)
            ->where('day', $day)
            ->where('status', 'approved')
            ->where('booking_date', $startDate)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereTime('start_time', '<', $endTime)
                  ->whereTime('end_time', '>', $startTime);
            })
            ->first();

        if ($conflictingBooking) {
            $timeRange = Carbon::parse($conflictingBooking->start_time)->format('H:i') . 
                         ' - ' . 
                         Carbon::parse($conflictingBooking->end_time)->format('H:i');
            $bookingName = $conflictingBooking->course_name ?? $conflictingBooking->activity_name ?? 'Peminjaman';
            return "Bentrok dengan peminjaman yang sudah disetujui: {$bookingName} ({$timeRange})";
        }

        return null;
    }
}
