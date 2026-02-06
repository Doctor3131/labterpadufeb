<?php

namespace App\Http\Controllers;

use App\Models\RefinitivRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RefinitivRequestController extends Controller
{
    /**
     * Show the form for creating a new Refinitiv request.
     */
    public function create()
    {
        return view('refinitiv.create', [
            'affiliations' => RefinitivRequest::AFFILIATIONS,
            'studyPrograms' => RefinitivRequest::STUDY_PROGRAMS,
            'purposes' => RefinitivRequest::PURPOSES,
            'sessions' => RefinitivRequest::SESSIONS,
        ]);
    }

    /**
     * Store a newly created Refinitiv request.
     */
    public function store(Request $request)
    {
        // Determine if applicant is dosen or mahasiswa
        $isDosen = $request->input('applicant_type') === 'dosen';
        
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\']+$/',
            'whatsapp' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
            'affiliation' => 'required|in:internal_feb,internal_undip,eksternal',
            'applicant_type' => 'required|in:dosen,mahasiswa',
            'purpose' => 'required|in:skripsi,thesis,disertasi,lomba,tugas_mk,penelitian_dosen,lainnya',
            'usage_date' => 'required|date|after_or_equal:today',
            'session' => 'required|in:sesi_1,sesi_2,sesi_3',
            'variables' => 'required|string|max:2000',
            'statement_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'agreement' => 'required|accepted',
            'understood' => 'required|accepted',
        ];

        // Add conditional rules based on applicant type
        if ($isDosen) {
            $rules['nip'] = ['required', 'string', 'regex:/^[0-9]{18}$/'];
        } else {
            $rules['nim'] = ['required', 'string', 'regex:/^[0-9]{14}$/'];
            $rules['study_program'] = 'required|string|max:255';
            $rules['ktm_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        if ($request->input('purpose') === 'lainnya') {
            $rules['purpose_other'] = 'required|string|max:255';
        }

        if (!$isDosen && $request->input('study_program') === 'Lainnya') {
            $rules['study_program_other'] = 'required|string|max:255';
        }

        if ($request->input('purpose') === 'penelitian_dosen') {
            $rules['lecturer_name'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama wajib diisi',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, dan apostrof',
            'nim.required' => 'NIM wajib diisi',
            'nim.regex' => 'NIM harus 14 digit angka',
            'nip.required' => 'NIP wajib diisi',
            'nip.regex' => 'NIP harus 18 digit angka',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi',
            'whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit angka',
            'affiliation.required' => 'Keterangan wajib dipilih',
            'applicant_type.required' => 'Status (Dosen/Mahasiswa) wajib dipilih',
            'study_program.required' => 'Program Studi wajib dipilih',
            'purpose.required' => 'Keperluan penggunaan data wajib dipilih',
            'purpose_other.required' => 'Keperluan lainnya wajib diisi',
            'study_program_other.required' => 'Program studi lainnya wajib diisi',
            'lecturer_name.required' => 'Nama dosen wajib diisi',
            'usage_date.required' => 'Tanggal pemakaian wajib diisi',
            'usage_date.after_or_equal' => 'Tanggal pemakaian minimal hari ini',
            'session.required' => 'Sesi pemakaian wajib dipilih',
            'variables.required' => 'Variabel yang dibutuhkan wajib diisi',
            'ktm_file.required' => 'Upload KTM wajib bagi mahasiswa',
            'ktm_file.mimes' => 'File KTM harus berupa PDF, JPG, atau PNG',
            'ktm_file.max' => 'Ukuran file KTM maksimal 10MB',
            'statement_file.required' => 'Surat Pernyataan Kesanggupan wajib diupload',
            'statement_file.mimes' => 'File surat harus berupa PDF, JPG, atau PNG',
            'statement_file.max' => 'Ukuran file surat maksimal 10MB',
            'agreement.required' => 'Anda harus menyetujui pernyataan',
            'agreement.accepted' => 'Anda harus menyetujui pernyataan',
            'understood.required' => 'Anda harus memahami mekanisme',
            'understood.accepted' => 'Anda harus memahami mekanisme',
        ]);

        try {
            // Generate unique token
            $token = RefinitivRequest::generateToken();

            // Handle file uploads
            $statementPath = $request->file('statement_file')->store('refinitiv/statements', 'public');
            
            $ktmPath = null;
            if (!$isDosen && $request->hasFile('ktm_file')) {
                $ktmPath = $request->file('ktm_file')->store('refinitiv/ktm', 'public');
            }

            // Get NIM or NIP based on applicant type
            $nimNip = $isDosen ? $validated['nip'] : $validated['nim'];

            // Create the request
            $refinitivRequest = RefinitivRequest::create([
                'token' => $token,
                'name' => $validated['name'],
                'nim_nip' => $nimNip,
                'whatsapp' => $validated['whatsapp'],
                'affiliation' => $validated['affiliation'],
                'applicant_type' => $validated['applicant_type'],
                'study_program' => $isDosen ? null : ($validated['study_program'] === 'Lainnya' ? $validated['study_program_other'] : $validated['study_program']),
                'purpose' => $validated['purpose'],
                'purpose_other' => $validated['purpose_other'] ?? null,
                'lecturer_name' => $validated['lecturer_name'] ?? null,
                'usage_date' => $validated['usage_date'],
                'session' => $validated['session'],
                'variables' => $validated['variables'],
                'ktm_file' => $ktmPath,
                'statement_file' => $statementPath,
            ]);

            return redirect()->route('refinitiv.success', ['token' => $token]);

        } catch (\Exception $e) {
            Log::error('Refinitiv request submission error: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengirim permohonan. Silakan coba lagi.');
        }
    }

    /**
     * Show the success page.
     */
    public function success(string $token)
    {
        $refinitivRequest = RefinitivRequest::where('token', $token)->firstOrFail();
        
        return view('refinitiv.success', [
            'request' => $refinitivRequest,
        ]);
    }
}
