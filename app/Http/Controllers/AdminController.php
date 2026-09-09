<?php

namespace App\Http\Controllers;

use App\Models\AssetBorrowing;
use App\Models\BloombergRequest;
use App\Models\Booking;
use App\Models\BpsRequest;
use App\Models\RefinitivRequest;
use App\Models\Schedule;
use App\Services\RecurrenceDateService;
use App\Services\ScheduleCalendarService;
use App\Services\ScheduleService;
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

        return view('admin.lab-bookings', compact(
            'pendingBookings',
            'approvedBookings',
            'rejectedBookings'
        ));
    }

    /**
     * Show booking detail
     */
    public function show($id)
    {
        $booking = Booking::with(['lab', 'handler'])->findOrFail($id);

        return view('admin.booking-detail', compact('booking'));
    }

    /**
     * Approve booking
     */
    public function approve(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // State guard: only pending bookings can be approved
        if ($booking->status !== 'pending') {
            return $this->bookingManagementRedirect($request)
                ->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
        }

        // CRITICAL: Check for schedule conflicts BEFORE approving
        $bookingDate = Carbon::parse($booking->booking_date);
        $conflictCheck = null;
        $occurrenceDates = app(RecurrenceDateService::class)->datesForBooking($booking);

        foreach ($occurrenceDates as $occurrenceDateString) {
            $occurrenceDate = Carbon::parse($occurrenceDateString);
            $conflict = app(ScheduleCalendarService::class)->findConflict(
                (int) $booking->lab_id,
                $occurrenceDate,
                $booking->start_time,
                $booking->end_time,
                null,
                null,
                true,
                $booking->id
            );

            if ($conflict) {
                $conflictCheck = $conflict['label'].' pada '.$occurrenceDate->format('d/m/Y');
                break;
            }
        }

        if ($conflictCheck) {
            return $this->bookingManagementRedirect($request)
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

            Schedule::create(ScheduleService::mapFromBooking($booking));
        });

        return $this->bookingManagementRedirect($request)
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
            return $this->bookingManagementRedirect($request)
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

        return $this->bookingManagementRedirect($request)
            ->with('success', 'Peminjaman berhasil ditolak.');
    }

    private function bookingManagementRedirect(Request $request)
    {
        $status = $request->input('return_status', 'pending');

        return redirect()->route('admin.lab.bookings', [
            'status' => in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : 'pending',
        ]);
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
