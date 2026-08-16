<?php

namespace App\Http\Controllers;

use App\Models\AssetBorrowing;
use App\Models\BloombergRequest;
use App\Models\Booking;
use App\Models\BpsRequest;
use App\Models\RefinitivRequest;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Show admin dashboard with service cards
     */
    public function dashboard()
    {
        $labPendingCount = Booking::where('status', 'pending')->where('booking_type', '!=', 'pribadi')->count();
        $bpsPendingCount = BpsRequest::where('status', 'pending')->count();
        $refinitivPendingCount = RefinitivRequest::where('attendance_status', 'pending')->count();
        $bloombergTotalCount = BloombergRequest::count();
        $personalTotalCount = Booking::where('booking_type', 'pribadi')->count();
        $assetBorrowingPendingCount = AssetBorrowing::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'labPendingCount',
            'bpsPendingCount',
            'refinitivPendingCount',
            'bloombergTotalCount',
            'personalTotalCount',
            'assetBorrowingPendingCount'
        ));
    }

    /**
     * Show lab bookings management (paginated)
     */
    public function labBookings()
    {
        $pendingBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'pending')
            ->where('booking_type', '!=', 'pribadi')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'pending_page');

        $approvedBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'approved')
            ->where('booking_type', '!=', 'pribadi')
            ->orderBy('handled_at', 'desc')
            ->paginate(15, ['*'], 'approved_page');

        $rejectedBookings = Booking::with(['lab', 'handler'])
            ->where('status', 'rejected')
            ->where('booking_type', '!=', 'pribadi')
            ->orderBy('updated_at', 'desc')
            ->paginate(15, ['*'], 'rejected_page');

        // Asset Borrowings
        $pendingAssetBorrowings = AssetBorrowing::with(['lab', 'borrowedItems.item'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'asset_pending_page');

        $approvedAssetBorrowings = AssetBorrowing::with(['lab', 'borrowedItems.item', 'approvedBy'])
            ->whereIn('status', ['approved', 'borrowed'])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'asset_approved_page');

        $completedAssetBorrowings = AssetBorrowing::with(['lab', 'borrowedItems.item'])
            ->whereIn('status', ['returned', 'rejected', 'cancelled'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15, ['*'], 'asset_completed_page');

        return view('admin.lab-bookings', compact(
            'pendingBookings',
            'approvedBookings',
            'rejectedBookings',
            'pendingAssetBorrowings',
            'approvedAssetBorrowings',
            'completedAssetBorrowings'
        ));
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
        $booking = Booking::findOrFail($id);

        // State guard: only pending bookings can be approved
        if ($booking->status !== 'pending') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
        }

        // CRITICAL: Check for schedule conflicts BEFORE approving
        $bookingDate = Carbon::parse($booking->booking_date);
        $conflictCheck = $this->checkScheduleConflict(
            $booking->lab_id,
            $booking->day,
            $booking->start_time,
            $booking->end_time,
            $bookingDate->format('Y-m-d'),
            $booking->is_recurring
                ? ($booking->end_date ? $booking->end_date->format('Y-m-d') : null)
                : $bookingDate->format('Y-m-d')
        );

        if ($conflictCheck) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Tidak dapat menyetujui peminjaman: '.$conflictCheck);
        }

        DB::transaction(function () use ($booking) {
            // Update booking status via explicit assignment (not mass assignment)
            $booking->status = 'approved';
            $booking->handled_at = now();
            if (Auth::check()) {
                $booking->handled_by = Auth::id();
            }
            $booking->save();

            // Create schedule entry from approved booking
            // Important: Use Carbon with Asia/Jakarta timezone to get correct day
            $bookingDate = Carbon::parse($booking->booking_date)->timezone('Asia/Jakarta');

            $scheduleData = [
                'lab_id' => $booking->lab_id,
                'day' => $booking->day, // Use day from booking (already correct)
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
                $scheduleData['end_date'] = $booking->end_date ? $booking->end_date->toDateString() : null; // Recurring (bisa tanpa batas)
                $scheduleData['course'] = $booking->course_name;
                $scheduleData['lecturer'] = $booking->lecturer_name;
                $scheduleData['komting'] = $booking->pic_name;
            } else {
                // One-time booking - use booking_type directly
                $scheduleData['type'] = $booking->booking_type; // perkuliahan_tidak_tetap, non_perkuliahan, or pribadi
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
                } elseif ($booking->booking_type === 'pribadi') {
                    $scheduleData['course'] = $booking->purpose ?? 'Peminjaman Pribadi';
                    $scheduleData['lecturer'] = null;
                    $scheduleData['komting'] = null;
                } else {
                    // Fallback for unknown booking type
                    $scheduleData['course'] = $booking->course_name ?? $booking->activity_name ?? $booking->purpose ?? 'Peminjaman';
                    $scheduleData['lecturer'] = null;
                    $scheduleData['komting'] = null;
                }
            }

            Schedule::create($scheduleData);
        });

        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil disetujui!');
    }

    /**
     * Reject booking
     */
    public function reject(Request $request, $id)
    {
        Log::info('Reject booking called', [
            'booking_id' => $id,
            'reason' => $request->rejection_reason,
        ]);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $booking = Booking::findOrFail($id);

        // State guard: only pending bookings can be rejected
        if ($booking->status !== 'pending') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
        }

        Log::info('Booking found', [
            'booking_id' => $booking->id,
            'current_status' => $booking->status,
        ]);

        DB::transaction(function () use ($booking, $request) {
            // Delete related schedule if exists
            if ($booking->schedule) {
                $booking->schedule->delete();
            }

            // Update booking status via explicit assignment (not mass assignment)
            $booking->status = 'rejected';
            $booking->rejection_reason = $request->rejection_reason;
            $booking->handled_at = now();
            if (Auth::check()) {
                $booking->handled_by = Auth::id();
            }
            $booking->save();

            Log::info('Booking rejected successfully', [
                'booking_id' => $booking->id,
                'new_status' => $booking->status,
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
            $timeRange = Carbon::parse($conflictingSchedule->start_time)->format('H:i').
                         ' - '.
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
            $timeRange = Carbon::parse($conflictingBooking->start_time)->format('H:i').
                         ' - '.
                         Carbon::parse($conflictingBooking->end_time)->format('H:i');
            $bookingName = $conflictingBooking->course_name ?? $conflictingBooking->activity_name ?? 'Peminjaman';

            return "Bentrok dengan peminjaman yang sudah disetujui: {$bookingName} ({$timeRange})";
        }

        return null;
    }
}
