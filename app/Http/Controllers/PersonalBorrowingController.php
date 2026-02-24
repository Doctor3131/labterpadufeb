<?php

namespace App\Http\Controllers;

use App\Models\MahasiswaFeb;
use Illuminate\Http\Request;

class PersonalBorrowingController extends Controller
{
    /**
     * Validate NIM against mahasiswa_feb database (AJAX)
     * Used by the pribadi booking form for real-time NIM validation
     */
    public function validateNim(Request $request)
    {
        $request->validate([
            'nim' => 'required|string|max:20',
        ]);

        $mahasiswa = MahasiswaFeb::where('nim', $request->nim)->first();

        if (!$mahasiswa) {
            return response()->json([
                'found' => false,
                'message' => 'NIM tidak ditemukan di database mahasiswa FEB.',
            ]);
        }

        return response()->json([
            'found' => true,
        ]);
    }
}
