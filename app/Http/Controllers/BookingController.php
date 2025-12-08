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
     * Get available labs based on capacity
     */
    public function getAvailableLabs(Request $request)
    {
        $jumlahPeserta = $request->jumlah_peserta;
        
        // Smart capacity matching dengan pembulatan ke atas
        $labs = Lab::where('capacity', '>=', $jumlahPeserta)
            ->orderBy('capacity', 'asc') // Urutkan dari terkecil agar dapat yang paling pas
            ->get();
        
        return response()->json($labs);
    }

    /**
     * Check lab availability for specific date and time
     */
    public function checkAvailability(Request $request)
    {
        $labId = $request->lab_id;
        $tanggal = $request->tanggal;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        
        $lab = Lab::find($labId);
        
        if (!$lab) {
            return response()->json(['available' => false, 'message' => 'Lab tidak ditemukan']);
        }
        
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
        
        // Check if lab is available using the model method
        $isAvailable = $lab->isAvailable($dayName, $startTime, $endTime);
        
        return response()->json([
            'available' => $isAvailable,
            'message' => $isAvailable ? 'Lab tersedia' : 'Lab sudah terpakai pada jadwal tersebut'
        ]);
    }

    /**
     * Store a new booking request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_type' => 'required|in:perkuliahan_tetap,perkuliahan_tidak_tetap,non_perkuliahan',
            'nama_peminjam' => 'required|string|max:255',
            'program_studi' => 'required|string|max:255',
            'nim' => 'required|string|max:50',
            'no_telpon' => 'required|string|max:20',
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

        // Handle document upload
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('booking-documents', 'public');
            $validated['document_path'] = $path;
        }

        // Set is_recurring for perkuliahan tetap
        $validated['is_recurring'] = $request->booking_type === 'perkuliahan_tetap';

        // Create booking
        $booking = Booking::create($validated);

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
