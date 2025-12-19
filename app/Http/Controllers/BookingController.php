<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $jumlahPeserta = $request->jumlah_peserta;
        $tanggal = $request->tanggal;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        
        // Get day name from date
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $dayName = $days[date('l', strtotime($tanggal))];
        
        // Get labs with sufficient capacity
        $labs = Lab::where('capacity', '>=', $jumlahPeserta)
            ->orderBy('capacity', 'asc')
            ->get();
        
        // Filter labs that are available at the requested time
        $availableLabs = $labs->filter(function ($lab) use ($dayName, $startTime, $endTime, $tanggal) {
            return $lab->isAvailable($dayName, $startTime, $endTime, $tanggal);
        });
        
        return response()->json($availableLabs->values());
    }



    /**
     * Store a new booking request
     */
    public function store(Request $request)
    {
        // Log incoming request
        \Log::info('Booking store method called', [
            'booking_type' => $request->booking_type,
            'all_data' => $request->except(['_token', 'document'])
        ]);

        try {
            $validated = $request->validate([
            'booking_type' => 'required|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
            'nama_peminjam' => 'required|string|max:255',
            'program_studi' => 'required|string|max:255',
            'nim' => 'required|string|size:14|regex:/^[0-9]{14}$/',
            'no_telpon' => 'required|string|min:10|max:15|regex:/^[0-9+]{10,15}$/',
            'alamat' => 'nullable|string',
            'lab_id' => 'required|exists:labs,id',
            'tanggal' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'jumlah_peserta' => 'required|integer|min:1',
            'document' => 'nullable|file|mimes:pdf|max:5120', // Max 5MB
            
            // Non-perkuliahan fields
            'jenis_kegiatan' => 'required_if:booking_type,non_perkuliahan',
            'jabatan' => 'nullable|string|max:255',
            'kebutuhan_peralatan' => 'nullable|string',
            'nama_kegiatan' => 'required_if:booking_type,non_perkuliahan|string|max:255',
            
            // Perkuliahan fields
            'mata_kuliah' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
            'dosen_pengampu' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:255',
            'nip_dosen' => 'required_if:booking_type,perkuliahan_tetap,perkuliahan_tidak_tetap|string|max:50',
            'software_digunakan' => 'nullable|string|max:255',
        ]);

        \Log::info('Validation passed');

        // Handle document upload
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('booking-documents', 'public');
            $validated['document_path'] = $path;
        }

        // Set is_recurring for perkuliahan tetap
        $validated['is_recurring'] = $request->booking_type === 'perkuliahan_tetap';

        // Create booking
        $booking = Booking::create($validated);

        // Log for debugging
        \Log::info('Booking created successfully', [
            'booking_id' => $booking->id,
            'redirect_to' => route('booking.success', $booking->id)
        ]);

            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
        return redirect()->route('booking.success', $booking->id)
            ->with('success', 'Permintaan peminjaman berhasil diajukan!');
    }

    /**
     * Show success page
     */
    public function success($id)
    {
        $booking = Booking::with('lab')->findOrFail($id);
        return view('booking.success', compact('booking'));
    }
}
