<?php

namespace App\Http\Controllers;

use App\Models\BloombergRequest;
use App\Models\BlockedDate;
use App\Models\ServiceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BloombergRequestController extends Controller
{
    /**
     * Show the reservation form (with capacity check).
     */
    public function create()
    {
        $blockedDates = BlockedDate::getBlockedDatesArray('bloomberg');
        $capacity = (int) ServiceSetting::getValue('bloomberg', 'capacity_per_session', '12');

        return view('bloomberg.create', [
            'applicantTypes' => BloombergRequest::APPLICANT_TYPES,
            'studyPrograms' => BloombergRequest::STUDY_PROGRAMS,
            'purposes' => BloombergRequest::PURPOSES,
            'sessions' => BloombergRequest::SESSIONS,
            'sessionsFriday' => BloombergRequest::SESSIONS_FRIDAY,
            'blockedDates' => $blockedDates,
            'capacity' => $capacity,
            'isWalkIn' => false,
        ]);
    }

    /**
     * Show the walk-in form (no capacity check).
     */
    public function createWalkIn()
    {
        // Check if walk-in is enabled
        if (!ServiceSetting::isEnabled('bloomberg', 'walk_in_enabled')) {
            return view('bloomberg.walk-in-closed');
        }

        $blockedDates = BlockedDate::getBlockedDatesArray('bloomberg');

        return view('bloomberg.create', [
            'applicantTypes' => BloombergRequest::APPLICANT_TYPES,
            'studyPrograms' => BloombergRequest::STUDY_PROGRAMS,
            'purposes' => BloombergRequest::PURPOSES,
            'sessions' => BloombergRequest::SESSIONS,
            'sessionsFriday' => BloombergRequest::SESSIONS_FRIDAY,
            'blockedDates' => $blockedDates,
            'capacity' => null, // No capacity limit for walk-in
            'isWalkIn' => true,
        ]);
    }

    /**
     * Store a newly created Bloomberg request (reservation or walk-in).
     */
    public function store(Request $request)
    {
        $isWalkIn = $request->input('type') === 'walk_in';

        // If walk-in, check if enabled
        if ($isWalkIn && !ServiceSetting::isEnabled('bloomberg', 'walk_in_enabled')) {
            return back()->with('error', 'Form kunjungan langsung sedang ditutup.');
        }

        $applicantType = $request->input('applicant_type');
        // dosen_undip = Dosen Undip (uses NIP)
        // dosen_non_undip = Non Undip (uses university + study_program text fields)
        $isDosenUndip = $applicantType === 'dosen_undip';
        $isNonUndip = $applicantType === 'dosen_non_undip';

        // Base validation rules
        $rules = [
            'type' => 'required|in:reservasi,walk_in',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\']+$/',
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
            'applicant_type' => 'required|in:mahasiswa,dosen_undip,dosen_non_undip',
            'usage_date' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    if ($date->isSunday()) {
                        $fail('Reservasi Bloomberg tidak tersedia pada hari Minggu.');
                    }
                },
                function ($attribute, $value, $fail) use ($request) {
                    $session = $request->input('session');
                    if (BlockedDate::isBlocked('bloomberg', $value, $session)) {
                        $fail('Tanggal/sesi yang dipilih tidak tersedia untuk reservasi Bloomberg.');
                    }
                },
            ],
            'session' => 'required|in:sesi_1,sesi_2',
            'purpose' => 'required|in:skripsi,thesis,disertasi,sertifikasi_bloomberg,lomba,tugas_mk,penelitian_dosen,explore,lainnya',
            'statement_file' => 'required|file|mimes:pdf|max:5120',
            'agreement_citation' => 'required',
            'agreement_compliance' => 'required',
        ];

        // Conditional rules based on applicant type
        if ($isDosenUndip) {
            // Dosen Undip: needs NIP
            $rules['nip'] = ['required', 'string', 'regex:/^[0-9]{18}$/'];
        } elseif ($isNonUndip) {
            // Non Undip: needs university + study_program (text fields)
            $rules['university'] = 'required|string|max:255';
            $rules['study_program'] = 'required|string|max:255';
        } else {
            // Mahasiswa: needs NIM + study_program (from radio list)
            $rules['nim'] = ['required', 'string', 'regex:/^[0-9]{14}$/'];
            $rules['study_program'] = 'required|string|max:255';
        }

        // Study program "Lainnya" applies to mahasiswa only
        if (!$isDosenUndip && !$isNonUndip && $request->input('study_program') === 'Lainnya') {
            $rules['study_program_other'] = 'required|string|max:255';
        }

        // Conditional fields based on purpose
        $purpose = $request->input('purpose');
        if (in_array($purpose, ['skripsi', 'thesis', 'disertasi', 'lomba'])) {
            $rules['research_title'] = 'required|string|max:500';
        }
        if ($purpose === 'tugas_mk') {
            $rules['subject_name'] = 'required|string|max:255';
        }
        if ($purpose === 'penelitian_dosen') {
            $rules['lecturer_name'] = 'required|string|max:255';
        }
        if ($purpose === 'lainnya') {
            $rules['purpose_other'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama wajib diisi',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, dan apostrof',
            'nim.required' => 'NIM wajib diisi',
            'nim.regex' => 'NIM harus 14 digit angka',
            'nip.required' => 'NIP wajib diisi',
            'nip.regex' => 'NIP harus 18 digit angka',
            'university.required' => 'Universitas wajib diisi',
            'phone.required' => 'Nomor HP wajib diisi',
            'phone.regex' => 'Nomor HP harus diawali 08 dan berisi 10-15 digit angka',
            'applicant_type.required' => 'Status wajib dipilih',
            'study_program.required' => 'Program Studi wajib diisi',
            'study_program_other.required' => 'Program studi lainnya wajib diisi',
            'usage_date.required' => 'Tanggal pemakaian wajib diisi',
            'usage_date.after_or_equal' => 'Tanggal pemakaian minimal hari ini',
            'session.required' => 'Sesi pemakaian wajib dipilih',
            'purpose.required' => 'Keperluan kunjungan wajib dipilih',
            'research_title.required' => 'Judul Penelitian/Nama Lomba wajib diisi',
            'subject_name.required' => 'Nama mata kuliah wajib diisi',
            'lecturer_name.required' => 'Nama dosen wajib diisi',
            'purpose_other.required' => 'Keperluan lainnya wajib diisi',
            'statement_file.required' => 'Surat Pengantar wajib diupload',
            'statement_file.mimes' => 'File surat harus berupa PDF',
            'statement_file.max' => 'Ukuran file surat maksimal 5MB',
            'agreement_citation.required' => 'Anda harus menyetujui pernyataan sitasi',
            'agreement_compliance.required' => 'Anda harus menyetujui pernyataan kepatuhan',
        ]);

        try {
            $token = BloombergRequest::generateToken();
            $statementPath = $request->file('statement_file')->store('bloomberg/statements', 'public');

            // Determine NIM/NIP value
            $nimNip = null;
            if ($isDosenUndip) {
                $nimNip = $validated['nip'];
            } elseif ($isNonUndip) {
                $nimNip = null; // Non Undip doesn't have NIM/NIP
            } else {
                $nimNip = $validated['nim'];
            }

            // Determine study program
            $studyProgram = null;
            if ($isNonUndip) {
                // Non Undip: study_program is free text
                $studyProgram = $validated['study_program'];
            } elseif (!$isDosenUndip) {
                // Mahasiswa: study_program from radio, with "Lainnya" override
                $studyProgram = $validated['study_program'] === 'Lainnya'
                    ? ($validated['study_program_other'] ?? $validated['study_program'])
                    : $validated['study_program'];
            }

            // Use transaction with lock to prevent race condition on capacity
            DB::transaction(function () use ($token, $isWalkIn, $validated, $nimNip, $studyProgram, $statementPath, $isNonUndip) {
                // Re-check capacity inside transaction with lock (reservation only)
                if (!$isWalkIn) {
                    $capacity = (int) ServiceSetting::getValue('bloomberg', 'capacity_per_session', '12');
                    $booked = BloombergRequest::where('usage_date', $validated['usage_date'])
                        ->where('session', $validated['session'])
                        ->where('type', BloombergRequest::TYPE_RESERVASI)
                        ->lockForUpdate()
                        ->count();

                    if ($booked >= $capacity) {
                        throw new \Exception('Sesi yang dipilih sudah penuh untuk tanggal tersebut.');
                    }
                }

                BloombergRequest::create([
                    'token' => $token,
                    'type' => $isWalkIn ? BloombergRequest::TYPE_WALK_IN : BloombergRequest::TYPE_RESERVASI,
                    'name' => $validated['name'],
                    'nim_nip' => $nimNip,
                    'phone' => $validated['phone'],
                    'applicant_type' => $validated['applicant_type'],
                    'study_program' => $studyProgram,
                    'university' => $isNonUndip ? $validated['university'] : 'Universitas Diponegoro',
                    'usage_date' => $validated['usage_date'],
                    'session' => $validated['session'],
                    'purpose' => $validated['purpose'],
                    'purpose_other' => $validated['purpose_other'] ?? null,
                    'research_title' => $validated['research_title'] ?? null,
                    'subject_name' => $validated['subject_name'] ?? null,
                    'lecturer_name' => $validated['lecturer_name'] ?? null,
                    'statement_file' => $statementPath,
                ]);
            });

            return redirect()->route('bloomberg.success', ['token' => $token]);

        } catch (\Exception $e) {
            Log::error('Bloomberg submission error: ' . $e->getMessage());

            // User-friendly message for capacity full
            if (str_contains($e->getMessage(), 'sudah penuh')) {
                return back()->withInput()->with('error', $e->getMessage());
            }

            return back()->withInput()->with('error', 'Terjadi kesalahan saat mengirim data. Silakan coba lagi.');
        }
    }

    /**
     * API: Check remaining capacity for a date + session.
     */
    public function checkCapacity(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'session' => 'required|in:sesi_1,sesi_2',
        ]);

        $remaining = BloombergRequest::getRemainingCapacity(
            $request->input('date'),
            $request->input('session')
        );

        $capacity = (int) ServiceSetting::getValue('bloomberg', 'capacity_per_session', '12');

        // Check if this specific session is blocked
        $isBlocked = BlockedDate::isBlocked('bloomberg', $request->input('date'), $request->input('session'));

        return response()->json([
            'remaining' => $remaining,
            'capacity' => $capacity,
            'full' => $remaining <= 0,
            'blocked' => $isBlocked,
        ]);
    }

    /**
     * Show the success page.
     */
    public function success(string $token)
    {
        $bloombergRequest = BloombergRequest::where('token', $token)->firstOrFail();

        return view('bloomberg.success', [
            'request' => $bloombergRequest,
        ]);
    }
}
