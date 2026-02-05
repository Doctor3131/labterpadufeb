<?php

namespace App\Http\Controllers;

use App\Models\BpsRequest;
use App\Models\BpsMasterData;
use App\Models\BpsSubData;
use App\Models\BpsRequestVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BpsRequestController extends Controller
{
    /**
     * Show the BPS data request form
     */
    public function create()
    {
        $masterData = BpsMasterData::active()
            ->ordered()
            ->with(['activeSubData'])
            ->get();

        return view('bps.create', compact('masterData'));
    }

    /**
     * Store a new BPS data request
     */
    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'applicant_type' => 'required|in:mahasiswa,dosen',
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\']+$/',
            'email' => 'required|email:rfc,dns|max:255',
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
            'purpose' => 'required|in:' . implode(',', BpsRequest::PURPOSES),
            'purpose_other' => 'required_if:purpose,Lainnya|nullable|string|max:255',
            'has_lecturer_collaboration' => 'required|boolean',
            'collaborating_lecturer_name' => 'required_if:has_lecturer_collaboration,1|nullable|string|max:255',
            'selected_data' => 'required|array|min:1',
            'selected_data.*' => 'exists:bps_sub_data,id',
            'variables' => 'required|array',
            'variables.*' => 'nullable|string|max:1000',
            'statement_letter' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'agreement_accepted' => 'required|accepted',
        ];

        // Conditional rules based on applicant type
        if ($request->input('applicant_type') === 'mahasiswa') {
            $rules['nim'] = ['required', 'string', 'regex:/^[0-9]{14}$/'];
            $rules['study_program'] = 'required|string|max:255';
            $rules['ktm'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } else {
            $rules['nip'] = ['required', 'string', 'regex:/^[0-9]{18}$/'];
        }

        $validated = $request->validate($rules, [
            'applicant_type.required' => 'Pilih status peminjam',
            'name.required' => 'Nama wajib diisi',
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, dan apostrof',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'phone.required' => 'Nomor WhatsApp wajib diisi',
            'phone.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit angka',
            'nim.required' => 'NIM wajib diisi untuk mahasiswa',
            'nim.regex' => 'NIM harus 14 digit angka',
            'nip.required' => 'NIP wajib diisi untuk dosen',
            'nip.regex' => 'NIP harus 18 digit angka',
            'study_program.required' => 'Program studi wajib diisi untuk mahasiswa',
            'purpose.required' => 'Keperluan penggunaan data wajib dipilih',
            'purpose_other.required_if' => 'Jelaskan keperluan lainnya',
            'collaborating_lecturer_name.required_if' => 'Nama dosen pembimbing wajib diisi',
            'selected_data.required' => 'Pilih minimal satu dataset',
            'selected_data.min' => 'Pilih minimal satu dataset',
            'variables.required' => 'Kode variabel wajib diisi',
            'ktm.required' => 'Upload KTM wajib untuk mahasiswa',
            'ktm.mimes' => 'Format file KTM harus PDF, JPG, atau PNG',
            'ktm.max' => 'Ukuran file KTM maksimal 5MB',
            'statement_letter.required' => 'Surat pernyataan kesanggupan wajib diupload',
            'statement_letter.mimes' => 'Format surat pernyataan harus PDF, JPG, atau PNG',
            'statement_letter.max' => 'Ukuran surat pernyataan maksimal 5MB',
            'agreement_accepted.required' => 'Anda harus menyetujui peraturan penggunaan data',
            'agreement_accepted.accepted' => 'Anda harus menyetujui peraturan penggunaan data',
        ]);

        try {
            DB::beginTransaction();

            // Upload files
            $statementLetterPath = $request->file('statement_letter')->store('bps/statements', 'public');
            
            $ktmPath = null;
            if ($request->input('applicant_type') === 'mahasiswa' && $request->hasFile('ktm')) {
                $ktmPath = $request->file('ktm')->store('bps/ktm', 'public');
            }

            // Create the request
            $bpsRequest = BpsRequest::create([
                'applicant_type' => $validated['applicant_type'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'nim' => $validated['nim'] ?? null,
                'nip' => $validated['nip'] ?? null,
                'phone' => $validated['phone'],
                'study_program' => $validated['study_program'] ?? null,
                'purpose' => $validated['purpose'],
                'purpose_other' => $validated['purpose_other'] ?? null,
                'has_lecturer_collaboration' => $validated['has_lecturer_collaboration'],
                'collaborating_lecturer_name' => $validated['collaborating_lecturer_name'] ?? null,
                'ktm_path' => $ktmPath,
                'statement_letter_path' => $statementLetterPath,
                'agreement_accepted' => true,
                'status' => 'pending',
            ]);

            // Attach selected datasets
            $bpsRequest->subData()->attach($validated['selected_data']);

            // Save variables for each selected dataset
            foreach ($validated['selected_data'] as $subDataId) {
                if (isset($validated['variables'][$subDataId]) && !empty($validated['variables'][$subDataId])) {
                    BpsRequestVariable::create([
                        'request_id' => $bpsRequest->id,
                        'sub_data_id' => $subDataId,
                        'variables' => $validated['variables'][$subDataId],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('bps.success', ['token' => $bpsRequest->tracking_token])
                ->with('success', 'Permohonan data BPS berhasil diajukan!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded files if transaction failed
            if (isset($statementLetterPath)) {
                Storage::disk('public')->delete($statementLetterPath);
            }
            if (isset($ktmPath)) {
                Storage::disk('public')->delete($ktmPath);
            }

            return back()->withInput()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Show success page after submission
     */
    public function success($token)
    {
        $bpsRequest = BpsRequest::where('tracking_token', $token)->firstOrFail();
        
        return view('bps.success', compact('bpsRequest'));
    }

    /**
     * Get sub data for a master (AJAX)
     */
    public function getSubData($masterId)
    {
        $subData = BpsSubData::where('master_id', $masterId)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'code']);

        return response()->json($subData);
    }
}
