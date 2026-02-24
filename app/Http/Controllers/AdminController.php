<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\ScheduleDocument;
use App\Models\BpsRequest;
use App\Models\RefinitivRequest;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show admin dashboard with 4 cards (Lab, BPS, Refinitiv, Personal)
     */
    public function dashboard()
    {
        // Get counts for each service (pribadi excluded from lab — separate tab)
        $labPendingCount = Booking::where('status', 'pending')->where('booking_type', '!=', 'pribadi')->count();
        $bpsPendingCount = BpsRequest::where('status', 'pending')->count();
        $refinitivPendingCount = RefinitivRequest::where('attendance_status', 'pending')->count();
        $personalTotalCount = Booking::where('booking_type', 'pribadi')->count();

        return view('admin.dashboard', compact(
            'labPendingCount',
            'bpsPendingCount',
            'refinitivPendingCount',
            'personalTotalCount'
        ));
    }

    /**
     * Show lab bookings management
     */
    public function labBookings()
    {
        // Paginate each status separately with custom page parameter names
        // Pribadi excluded — managed in dedicated "Pencatatan Peminjaman Pribadi" tab
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

        return view('admin.lab-bookings', compact('pendingBookings', 'approvedBookings', 'rejectedBookings'));
    }
    /**
     * Show booking detail
     */
    public function show($id)
    {
        $booking = Booking::with('lab')->findOrFail($id);

        // Pribadi bookings are managed in dedicated tab, not here
        if ($booking->isPribadi()) {
            return redirect()->route('admin.personal-borrowings.index');
        }

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

            // Pribadi bookings are auto-approved, cannot be manually approved
            if ($booking->isPribadi()) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Peminjaman pribadi tidak memerlukan persetujuan.');
            }
            
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
                $conflictCheck = ScheduleService::checkConflict(
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
            
            // Update booking status (direct assignment — not mass-assignable for security)
            $booking->status = 'approved';
            $booking->handled_at = now();
            
            if (Auth::check()) {
                $booking->handled_by = Auth::id();
            }
            
            $booking->save();

            // Create schedule entry from approved booking
            // SKIP for pribadi bookings - they don't appear in public schedule
            if (!$isPribadi) {
                $bookingDate = Carbon::parse($booking->booking_date);
                
                // Map booking data using Service (Optimization)
                $scheduleData = ScheduleService::mapFromBooking($booking);

                // No need for manual mapping block here anymore

                $schedule = Schedule::create($scheduleData);

                // Sync booking document data to schedule document
                try {
                    ScheduleDocument::create([
                        'schedule_id' => $schedule->id,
                        'study_program' => $booking->study_program,
                        'nim' => $booking->nim,
                        'nip' => $booking->nip,
                        'lecturer_nip' => $booking->lecturer_nip,
                        // Only store phone in document for non_perkuliahan (perkuliahan uses schedules.komting_phone)
                        'phone_number' => $booking->booking_type === 'non_perkuliahan' ? $booking->phone_number : null,
                        'software_needs' => $booking->software_needs,
                        'ktm_path' => $booking->document_path,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the approval transaction
                    Log::error('Failed to sync schedule document: ' . $e->getMessage());
                }
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

        // Pribadi bookings are auto-approved, cannot be rejected
        if ($booking->isPribadi()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Peminjaman pribadi tidak dapat ditolak.');
        }
        
        Log::info('Booking found', [
            'booking_id' => $booking->id,
            'current_status' => $booking->status
        ]);
        
        DB::transaction(function () use ($booking, $request) {
            // Update booking status (direct assignment — not mass-assignable for security)
            $booking->status = 'rejected';
            $booking->rejection_reason = $request->rejection_reason;
            $booking->handled_at = now();
            
            if (Auth::check()) {
                $booking->handled_by = Auth::id();
            }
            
            $booking->save();
            
            Log::info('Booking rejected successfully', [
                'booking_id' => $booking->id,
                'new_status' => $booking->status
            ]);
        });

        return redirect()->route('admin.dashboard')
            ->with('success', 'Peminjaman berhasil ditolak.');
    }
}
