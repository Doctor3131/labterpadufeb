<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isWalkIn ? 'Kunjungan Langsung' : 'Reservasi' }} Bloomberg - Laboratorium dan Fasilitas Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .step-indicator { transition: all 0.3s ease; }
        .step-active { background: linear-gradient(135deg, #4f46e5, #818cf8); color: white; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
        .step-completed { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .step-inactive { background: #e5e7eb; color: #9ca3af; }
        .form-step { display: none; }
        .form-step.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .radio-card { transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease; }
        .radio-card:has(input:checked) { border-color: #6366f1; background-color: #eef2ff; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2); }
        .radio-card:focus-within { outline: 2px solid rgba(99, 102, 241, 0.45); outline-offset: 2px; }
        #bloomberg-form input[type="radio"] { accent-color: #4f46e5; }
        #bloomberg-form input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"]):not([type="submit"]):not([type="button"]):focus,
        #bloomberg-form textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); caret-color: #4f46e5; }
        .bloomberg-date-trigger { min-height: 48px; border: 1px solid #cbd5e1; transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease; }
        .bloomberg-date-trigger:hover { border-color: #818cf8; }
        .bloomberg-date-trigger:focus-visible { outline: 3px solid rgba(99, 102, 241, 0.25); outline-offset: 2px; border-color: #6366f1; }
        .bloomberg-date-trigger[aria-invalid="true"] { border-color: #ef4444; }
        .bloomberg-date-popover { z-index: 30; width: min(20rem, calc(100vw - 2.5rem)); border: 1px solid #e2e8f0; box-shadow: 0 18px 44px rgba(15, 23, 42, 0.16); animation: datePopoverIn 0.16s ease-out; }
        @keyframes datePopoverIn { from { opacity: 0; transform: translateY(-4px) scale(0.99); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .bloomberg-calendar-day { min-height: 2.25rem; transition: color 0.15s ease, background-color 0.15s ease, border-color 0.15s ease; }
        .bloomberg-calendar-day.is-selected { color: white; background: #4f46e5; font-weight: 700; }
        .bloomberg-calendar-day[aria-current="date"]:not(.is-selected) { color: #4338ca; border-color: #a5b4fc; background: #eef2ff; font-weight: 700; }
        .bloomberg-calendar-day:disabled { color: #cbd5e1; cursor: not-allowed; background: transparent; }
        @media (prefers-reduced-motion: reduce) { .step-indicator, .radio-card, .bloomberg-date-popover, .bloomberg-calendar-day { animation: none; transition: none; } }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50 to-slate-100 min-h-screen">

    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('bloomberg.index') }}" class="flex items-center text-gray-600 hover:text-indigo-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="font-medium">Kembali</span>
                </a>
                <div class="text-right">
                    <h1 class="text-lg font-bold text-gray-800">Reservasi Bloomberg</h1>
                    <p class="text-xs text-gray-500">Laboratorium dan Fasilitas Digital FEB UNDIP</p>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl shadow-lg mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Form {{ $isWalkIn ? 'Kunjungan Langsung' : 'Reservasi' }} Bloomberg Terminal</h2>
            <p class="text-gray-500">{{ $isWalkIn ? 'Silakan isi formulir di bawah untuk pendataan kunjungan langsung Bloomberg Terminal' : 'Silakan isi formulir di bawah untuk mengajukan reservasi penggunaan Bloomberg Terminal' }}</p>
            @if($isWalkIn)
                <span class="inline-block mt-3 px-3 py-1 bg-orange-100 text-orange-700 text-sm font-semibold rounded-lg">Kunjungan Langsung</span>
            @endif
        </div>

        <!-- Step Indicators -->
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center space-x-2 sm:space-x-4">
                <div class="flex items-center">
                    <div id="step1-indicator" class="step-indicator step-active w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:inline">Data Diri</span>
                </div>
                <div class="w-8 sm:w-12 h-0.5 bg-gray-300" id="connector-1-2"></div>
                <div class="flex items-center">
                    <div id="step2-indicator" class="step-indicator step-inactive w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-400 hidden sm:inline">Jadwal & Keperluan</span>
                </div>
                <div class="w-8 sm:w-12 h-0.5 bg-gray-300" id="connector-2-3"></div>
                <div class="flex items-center">
                    <div id="step3-indicator" class="step-indicator step-inactive w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-400 hidden sm:inline">Dokumen & Persetujuan</span>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-r-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold mb-1">Terdapat kesalahan pada formulir:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form id="bloomberg-form" action="{{ route('bloomberg.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="{{ $isWalkIn ? 'walk_in' : 'reservasi' }}">

            <!-- Tata Tertib -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 mb-6">
                <h4 class="font-bold text-indigo-900 mb-3 flex items-center text-lg">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Tata Tertib Penggunaan Laboratorium Bloomberg FEB UNDIP
                </h4>
                <div class="text-sm text-indigo-800 space-y-2">
                    <ol class="list-decimal list-inside space-y-2 ml-2">
                        <li>Bagi pengunjung yang belum melakukan reservasi tetapi sudah berada di tempat, silakan hubungi admin untuk mengisi form kunjungan langsung. WA admin: <a href="https://wa.me/6285155266697" target="_blank" class="font-semibold text-indigo-600 hover:text-indigo-800 underline">085155266697</a></li>
                        <li>Pengunjung <strong>TIDAK DIPERKENANKAN</strong> menggunakan fasilitas <strong>EXPORT</strong> atau <strong>DOWNLOAD</strong> di Terminal Bloomberg.</li>
                        <li>Pengunjung <strong>TIDAK DIPERKENANKAN</strong> untuk menggunakan <strong>FLASHDISK</strong> atau Penyimpanan External Lainnya di komputer Bloomberg.</li>
                        <li>Tidak memperjualbelikan atau menyebarluaskan data yang diperoleh dari Terminal Bloomberg.</li>
                        <li>Akun tidak boleh dipindahtangankan kepada orang lain.</li>
                        <li>Wajib melakukan citation (sitasi).</li>
                        <li><strong>Wajib membawa surat izin penggunaan Lab dan KTM.</strong></li>
                    </ol>
                </div>
            </div>

            <!-- STEP 1: Data Diri -->
            <div id="step-1" class="form-step active">
                <div class="bg-white rounded-2xl shadow-lg p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">1</span>
                        Data Diri
                    </h3>

                    <!-- Applicant Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Status <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($applicantTypes as $value => $label)
                                <label class="radio-card border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-indigo-300 block">
                                    <div class="flex items-center">
                                        <input type="radio" name="applicant_type" value="{{ $value }}" 
                                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                                               {{ old('applicant_type', 'mahasiswa') === $value ? 'checked' : '' }}>
                                        <span class="ml-3 font-medium text-gray-700">{{ $label }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="mb-6">
                        <label for="name_input" class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="name_input" name="name" value="{{ old('name') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan nama lengkap Anda">
                    </div>

                    <!-- NIM Field (for Mahasiswa) -->
                    <div id="nim-field" class="mb-6">
                        <label for="nim_input" class="block text-sm font-semibold text-gray-700 mb-2">NIM <span class="text-red-500">*</span></label>
                        <input type="text" id="nim_input" name="nim" value="{{ old('nim') }}" maxlength="14"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan 14 digit NIM">
                        <p class="text-xs text-gray-500 mt-1">NIM harus 14 digit angka</p>
                    </div>

                    <!-- NIP Field (for Dosen Undip) -->
                    <div id="nip-field" class="mb-6 hidden">
                        <label for="nip_input" class="block text-sm font-semibold text-gray-700 mb-2">NIP <span class="text-red-500">*</span></label>
                        <input type="text" id="nip_input" name="nip" value="{{ old('nip') }}" maxlength="18"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan 18 digit NIP">
                        <p class="text-xs text-gray-500 mt-1">NIP harus 18 digit angka</p>
                    </div>

                    <!-- University Field (for Non Undip) -->
                    <div id="university-field" class="mb-6 hidden">
                        <label for="university_input" class="block text-sm font-semibold text-gray-700 mb-2">Universitas <span class="text-red-500">*</span></label>
                        <input type="text" id="university_input" name="university" value="{{ old('university') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan nama universitas">
                    </div>

                    <!-- Phone -->
                    <div class="mb-6">
                        <label for="phone_input" class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP <span class="text-red-500">*</span></label>
                        <input type="text" id="phone_input" name="phone" value="{{ old('phone') }}" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="08xxxxxxxxxx">
                        <p class="text-xs text-gray-500 mt-1">Contoh: 081234567890</p>
                    </div>

                    <!-- Study Program (for Mahasiswa - radio list) -->
                    <div id="study-program-field" class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Program Studi <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($studyPrograms as $program)
                                <label class="radio-card border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-indigo-300 block">
                                    <div class="flex items-center">
                                        <input type="radio" name="study_program" value="{{ $program }}"
                                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                                               {{ old('study_program') === $program ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ $program }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Study Program Other (for Mahasiswa - Lainnya) -->
                    <div id="study-program-other-field" class="mb-6 hidden">
                        <label for="study_program_other_input" class="block text-sm font-semibold text-gray-700 mb-2">Program Studi Lainnya <span class="text-red-500">*</span></label>
                        <input type="text" id="study_program_other_input" name="study_program_other" value="{{ old('study_program_other') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan nama program studi">
                    </div>

                    <!-- Study Program Text (for Non Undip - free text) -->
                    <div id="study-program-text-field" class="mb-6 hidden">
                        <label for="study_program_text_input" class="block text-sm font-semibold text-gray-700 mb-2">Program Studi <span class="text-red-500">*</span></label>
                        <input type="text" id="study_program_text_input" name="study_program" value="{{ old('study_program') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan program studi Anda">
                    </div>

                    <!-- Next Button -->
                    <div class="flex justify-end mt-8">
                        <button type="button" onclick="goToStep(2)"
                                class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors flex items-center">
                            Selanjutnya
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Jadwal & Keperluan -->
            <div id="step-2" class="form-step">
                <div class="bg-white rounded-2xl shadow-lg p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">2</span>
                        Jadwal & Keperluan
                    </h3>

                    <!-- Usage Date -->
                    <div class="mb-6">
                        <p id="usage-date-label" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Penggunaan <span class="text-red-500">*</span></p>
                        <input type="hidden" id="usage_date_input" name="usage_date" value="{{ old('usage_date') }}">
                        <div class="relative max-w-sm" id="usage-date-picker">
                            <button type="button" id="usage-date-trigger" class="bloomberg-date-trigger w-full rounded-xl bg-white px-4 text-left text-sm text-slate-700 flex items-center justify-between gap-3" aria-haspopup="dialog" aria-expanded="false" aria-controls="usage-date-calendar" aria-labelledby="usage-date-label usage-date-trigger-text">
                                <span class="flex min-w-0 items-center gap-3">
                                    <svg class="h-5 w-5 flex-shrink-0 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                    </svg>
                                    <span id="usage-date-trigger-text" class="truncate text-slate-400">Pilih tanggal</span>
                                </span>
                                <svg class="h-4 w-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>
                            <div id="usage-date-calendar" class="bloomberg-date-popover absolute left-0 top-full mt-2 hidden rounded-2xl bg-white p-4" role="dialog" aria-label="Pilih tanggal penggunaan" aria-modal="false">
                                <div class="mb-4 flex items-center justify-between">
                                    <button type="button" id="usage-date-prev" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-500" aria-label="Bulan sebelumnya">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6" /></svg>
                                    </button>
                                    <h4 id="usage-date-month" class="text-sm font-semibold text-slate-800" aria-live="polite"></h4>
                                    <button type="button" id="usage-date-next" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-500" aria-label="Bulan berikutnya">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" /></svg>
                                    </button>
                                </div>
                                <div class="mb-1 grid grid-cols-7 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-400" aria-hidden="true">
                                    <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                                </div>
                                <div id="usage-date-days" class="grid grid-cols-7 gap-1 text-center" aria-label="Tanggal dalam bulan"></div>
                                <p id="usage-date-hint" class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-500">Minggu dan tanggal yang diblokir tidak tersedia.</p>
                            </div>
                        </div>
                        <div id="sunday-warning" class="hidden mt-2 text-red-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Reservasi Bloomberg tidak tersedia pada hari Minggu.
                        </div>
                        <div id="blocked-date-warning" class="hidden mt-2 text-red-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                            </svg>
                            Tanggal ini tidak tersedia untuk reservasi Bloomberg.
                        </div>
                        <div id="friday-notice" class="hidden mt-2 text-indigo-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Hari Jumat: Sesi 2 dimulai pukul 13.30 WIB.
                        </div>
                    </div>

                    <!-- Session -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Sesi Penggunaan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="radio-card border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-indigo-300 block">
                                <div class="flex items-center">
                                    <input type="radio" name="session" value="sesi_1" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                                           {{ old('session') === 'sesi_1' ? 'checked' : '' }}>
                                    <span class="ml-3 font-medium text-gray-700" id="session1-label">{{ $sessions['sesi_1'] }}</span>
                                </div>
                            </label>
                            <label class="radio-card border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-indigo-300 block">
                                <div class="flex items-center">
                                    <input type="radio" name="session" value="sesi_2" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                                           {{ old('session') === 'sesi_2' ? 'checked' : '' }}>
                                    <span class="ml-3 font-medium text-gray-700" id="session2-label">{{ $sessions['sesi_2'] }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    @if(!$isWalkIn)
                    <!-- Capacity Indicator -->
                    <div id="capacity-indicator" class="mb-6 hidden">
                        <div id="capacity-box" class="p-4 rounded-xl border-2 border-indigo-200 bg-indigo-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-indigo-700">Ketersediaan Slot</span>
                                <span id="capacity-text" class="text-sm font-bold text-indigo-800"></span>
                            </div>
                            <div class="w-full bg-indigo-200 rounded-full h-2.5">
                                <div id="capacity-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 100%"></div>
                            </div>
                        </div>
                        <div id="capacity-full-warning" class="hidden mt-2 text-red-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Sesi ini sudah penuh untuk tanggal yang dipilih.
                        </div>
                    </div>
                    @endif

                    <!-- Purpose -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Keperluan Kunjungan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($purposes as $value => $label)
                                <label class="radio-card border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-indigo-300 block">
                                    <div class="flex items-center">
                                        <input type="radio" name="purpose" value="{{ $value }}"
                                               class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 purpose-radio"
                                               {{ old('purpose') === $value ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Purpose Other (for lainnya) -->
                    <div id="purpose-other-field" class="mb-6 hidden">
                        <label for="purpose_other_input" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jelaskan Keperluan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="purpose_other_input" name="purpose_other" value="{{ old('purpose_other') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan keperluan Anda">
                    </div>

                    <!-- Research Title (for skripsi/thesis/disertasi/lomba) -->
                    <div id="research-title-field" class="mb-6 hidden">
                        <label for="research_title_input" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span id="research-title-label">Judul Penelitian / Nama Lomba</span> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="research_title_input" name="research_title" value="{{ old('research_title') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan judul penelitian atau nama lomba">
                    </div>

                    <!-- Variables/data (for selected research purposes or Non-UNDIP applicants) -->
                    <div id="variables-field" class="mb-6 hidden">
                        <label for="variables_input" class="block text-sm font-semibold text-gray-700 mb-2">
                            Variabel/data yang dicari <span class="text-red-500">*</span>
                        </label>
                        <p id="variables-hint" class="text-xs text-gray-500 mb-2">
                            Sebutkan variabel dan cakupan data yang dibutuhkan.
                        </p>
                        <textarea id="variables_input" name="variables" rows="3" maxlength="2000" disabled
                                  aria-describedby="variables-hint"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                  placeholder="Contoh: ROA, ROE, dan harga saham harian perusahaan sektor perbankan Indonesia periode 2020–2025">{{ old('variables') }}</textarea>
                        @error('variables')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subject Name (for tugas_mk) -->
                    <div id="subject-name-field" class="mb-6 hidden">
                        <label for="subject_name_input" class="block text-sm font-semibold text-gray-700 mb-2">Nama Mata Kuliah <span class="text-red-500">*</span></label>
                        <input type="text" id="subject_name_input" name="subject_name" value="{{ old('subject_name') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan nama mata kuliah">
                    </div>

                    <!-- Lecturer Name (for penelitian_dosen) -->
                    <div id="lecturer-name-field" class="mb-6 hidden">
                        <label for="lecturer_name_input" class="block text-sm font-semibold text-gray-700 mb-2">Nama Dosen <span class="text-red-500">*</span></label>
                        <input type="text" id="lecturer_name_input" name="lecturer_name" value="{{ old('lecturer_name') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                               placeholder="Masukkan nama dosen pembimbing/penelitian">
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="goToStep(1)"
                                class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Kembali
                        </button>
                        <button type="button" onclick="goToStep(3)"
                                class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors flex items-center">
                            Selanjutnya
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Dokumen & Persetujuan -->
            <div id="step-3" class="form-step">
                <div class="bg-white rounded-2xl shadow-lg p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center mr-3 text-sm font-bold">3</span>
                        Dokumen & Persetujuan
                    </h3>

                    <!-- Statement File Upload -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Surat Pengantar Kaprodi/Dosen <span class="text-red-500">*</span></label>
                        
                        <!-- Template Download -->
                        <div class="mb-3 p-3 bg-indigo-50 border border-indigo-200 rounded-xl flex items-center gap-3">
                            <div class="flex-shrink-0 bg-indigo-100 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-indigo-800">Template Surat Izin</p>
                                <a href="https://bit.ly/Surat_Bloomberg" target="_blank" rel="noopener noreferrer" class="text-sm text-indigo-600 hover:text-indigo-800 underline transition-colors">
                                    https://bit.ly/Surat_Bloomberg
                                </a>
                            </div>
                        </div>

                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 transition-colors">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <div class="flex justify-center items-center">
                                <input type="file" id="statement_file_input" name="statement_file" accept=".pdf" required
                                       class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Format: PDF | Maksimal: 5MB</p>
                        </div>
                        <div id="file-error" class="hidden mt-2 text-red-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span id="file-error-text"></span>
                        </div>
                        <div id="file-success" class="hidden mt-2 text-green-600 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span id="file-success-text"></span>
                        </div>
                    </div>

                    <!-- Agreement 1: Citation -->
                    <div class="mb-6">
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
                            <p class="text-sm text-gray-800 mb-3">
                                Bersedia mensitasi segala data yang bersumber dari Bloomberg Laboratory FEB Undip. <span class="text-red-500">*</span>
                            </p>
                            <p class="text-sm text-gray-700 mb-2">Contoh:</p>
                            <div class="text-sm text-gray-600 space-y-3 ml-2">
                                <div>
                                    <p class="italic">Untuk data saham dan instrumen keuangan yang bersifat harian atau lebih kecil lagi (jam atau menit), ditulis tiap variabel, negara (atau tempat dimana perusahaan-perusahaan tersebut listed) dan nama ticker:</p>
                                    <p class="mt-1 text-gray-800 font-medium">Bloomberg L.P. (2006). Daily stock price and trading volume data from 11/1/05 to 11/1/06 for firms that were listed in Indonesia Stock Exchange: AALI, ASII, and BBCA. Retrieved from Bloomberg database</p>
                                </div>
                                <div>
                                    <p class="italic">Untuk data rasio keuangan dan informasi ekonomi yang bersifat kuartalan atau tahunan, ditulis tiap variabel, sektor perusahaan dan negara yang diambil:</p>
                                    <p class="mt-1 text-gray-800 font-medium">Bloomberg L.P. (2006). Annual data on ROA, NPL, and Total Equity for firms that were listed in Indonesia financial sector from 2010 to 2020. Retrieved from Bloomberg database.</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">Sumber: <a href="https://guides.library.ubc.ca/finance/bloomberg/citing" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">https://guides.library.ubc.ca/finance/bloomberg/citing</a></p>
                        </div>
                        <label class="flex items-center mt-3 cursor-pointer">
                            <input type="radio" id="agreement_citation" name="agreement_citation" value="1" required
                                   class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Saya bersedia</span>
                        </label>
                    </div>

                    <!-- Agreement 2: Compliance -->
                    <div class="mb-6">
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
                            <p class="text-sm text-gray-800">
                                Dengan ini saya bersedia untuk mematuhi aturan di Lab Bloomberg FEB-Undip. <span class="text-red-500">*</span>
                                Bahwa saya tidak akan menggunakan fasilitas <strong>EXPORT</strong> atau <strong>DOWNLOAD</strong> di Terminal Bloomberg, tidak akan memakai <strong>Flash disk</strong> atau penyimpanan eksternal lainnya pada komputer di Lab Bloomberg serta tidak memperjualbelikan atau menyebarluaskan data yang diperoleh dari Terminal Bloomberg.
                            </p>
                        </div>
                        <label class="flex items-center mt-3 cursor-pointer">
                            <input type="radio" id="agreement_compliance" name="agreement_compliance" value="1" required
                                   class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Saya bersedia</span>
                        </label>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8">
                        <button type="button" onclick="goToStep(2)"
                                class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Kembali
                        </button>
                        <button type="submit" id="submit-btn"
                                class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Kirim Reservasi
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-8">
            &copy; {{ date('Y') }} Laboratorium dan Fasilitas Digital FEB UNDIP
        </p>
    </div>

    <script>
        // === Blocked Dates from server (now array of objects with date, reason, blocked_session) ===
        const blockedDates = @json($blockedDates ?? []);
        const isWalkIn = @json($isWalkIn ?? false);
        const totalCapacity = @json($capacity ?? 12);
        const purposesRequiringVariables = @json($purposesRequiringVariables);
        const variablesField = document.getElementById('variables-field');
        const variablesInput = document.getElementById('variables_input');
        let currentStep = 1;

        function requiresVariables() {
            const applicantType = document.querySelector('input[name="applicant_type"]:checked');
            const purpose = document.querySelector('input[name="purpose"]:checked');

            return applicantType?.value === 'dosen_non_undip'
                || purposesRequiringVariables.includes(purpose?.value);
        }

        function updateVariablesField() {
            const shouldShow = requiresVariables();

            variablesField.classList.toggle('hidden', !shouldShow);
            variablesInput.disabled = !shouldShow;
            variablesInput.required = shouldShow;
            variablesInput.setAttribute('aria-required', String(shouldShow));
        }

        function goToStep(step) {
            // Validate current step before moving forward
            if (step > currentStep && !validateStep(currentStep)) {
                return;
            }

            // Hide current step
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            // Show target step
            document.getElementById(`step-${step}`).classList.add('active');

            // Update indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById(`step${i}-indicator`);
                if (i < step) {
                    indicator.className = 'step-indicator step-completed w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold';
                    indicator.innerHTML = '✓';
                } else if (i === step) {
                    indicator.className = 'step-indicator step-active w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold';
                    indicator.innerHTML = i;
                } else {
                    indicator.className = 'step-indicator step-inactive w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold';
                    indicator.innerHTML = i;
                }
            }

            // Update connectors
            document.getElementById('connector-1-2').style.backgroundColor = step >= 2 ? '#818cf8' : '#d1d5db';
            document.getElementById('connector-2-3').style.backgroundColor = step >= 3 ? '#818cf8' : '#d1d5db';

            currentStep = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // === Step Validation ===
        function validateStep(step) {
            if (step === 1) {
                return validateStep1();
            } else if (step === 2) {
                return validateStep2();
            }
            return true;
        }

        function validateStep1() {
            const name = document.getElementById('name_input').value.trim();
            const applicantType = document.querySelector('input[name="applicant_type"]:checked');
            const phone = document.getElementById('phone_input').value.trim();

            if (!name) { alert('Nama lengkap wajib diisi.'); return false; }
            if (!applicantType) { alert('Status wajib dipilih.'); return false; }
            if (!phone || !/^08[0-9]{8,13}$/.test(phone)) { alert('Nomor HP wajib diisi dan harus diawali 08 (10-15 digit).'); return false; }

            const type = applicantType.value;
            
            if (type === 'dosen_undip') {
                // Dosen Undip: needs NIP
                const nip = document.getElementById('nip_input').value.trim();
                if (!nip || !/^[0-9]{18}$/.test(nip)) { alert('NIP wajib diisi dan harus 18 digit angka.'); return false; }
            } else if (type === 'dosen_non_undip') {
                // Non Undip: needs university + study program (text)
                const university = document.getElementById('university_input').value.trim();
                if (!university) { alert('Universitas wajib diisi.'); return false; }
                const studyProgramText = document.getElementById('study_program_text_input').value.trim();
                if (!studyProgramText) { alert('Program Studi wajib diisi.'); return false; }
            } else {
                // Mahasiswa: needs NIM + study program (radio)
                const nim = document.getElementById('nim_input').value.trim();
                if (!nim || !/^[0-9]{14}$/.test(nim)) { alert('NIM wajib diisi dan harus 14 digit angka.'); return false; }

                const studyProgram = document.querySelector('input[name="study_program"]:checked');
                if (!studyProgram) { alert('Program studi wajib dipilih.'); return false; }
                
                if (studyProgram.value === 'Lainnya') {
                    const other = document.getElementById('study_program_other_input').value.trim();
                    if (!other) { alert('Program studi lainnya wajib diisi.'); return false; }
                }
            }

            return true;
        }

        function validateStep2() {
            const usageDate = document.getElementById('usage_date_input').value;
            const session = document.querySelector('input[name="session"]:checked');
            const purpose = document.querySelector('input[name="purpose"]:checked');

            if (!usageDate) { alert('Tanggal penggunaan wajib diisi.'); return false; }
            
            const date = new Date(usageDate);
            if (date.getDay() === 0) { alert('Reservasi tidak tersedia pada hari Minggu.'); return false; }
            
            // Check if entire day is blocked
            const fullDayBlock = blockedDates.find(b => b.date === usageDate && b.blocked_session === null);
            if (fullDayBlock) { 
                alert('Tanggal ini tidak tersedia: ' + (fullDayBlock.reason || 'Tanggal diblokir')); 
                return false; 
            }

            if (!session) { alert('Sesi penggunaan wajib dipilih.'); return false; }

            // Check if selected session is blocked
            const sessionBlock = blockedDates.find(b => b.date === usageDate && b.blocked_session === session.value);
            if (sessionBlock) {
                const sessionLabel = session.value === 'sesi_1' ? 'Sesi 1' : 'Sesi 2';
                alert(sessionLabel + ' tidak tersedia pada tanggal ini: ' + (sessionBlock.reason || 'Sesi diblokir'));
                return false;
            }

            if (!isWalkIn && capacityFull) { alert('Sesi yang dipilih sudah penuh untuk tanggal tersebut.'); return false; }

            if (!purpose) { alert('Keperluan kunjungan wajib dipilih.'); return false; }

            if (requiresVariables() && !variablesInput.value.trim()) {
                alert('Variabel/data yang dicari wajib diisi.');
                return false;
            }

            // Validate conditional fields
            const purposeVal = purpose.value;
            if (['skripsi', 'thesis', 'disertasi', 'lomba'].includes(purposeVal)) {
                const title = document.getElementById('research_title_input').value.trim();
                if (!title) { alert('Judul Penelitian / Nama Lomba wajib diisi.'); return false; }
            }
            if (purposeVal === 'tugas_mk') {
                const subject = document.getElementById('subject_name_input').value.trim();
                if (!subject) { alert('Nama mata kuliah wajib diisi.'); return false; }
            }
            if (purposeVal === 'penelitian_dosen') {
                const lecturer = document.getElementById('lecturer_name_input').value.trim();
                if (!lecturer) { alert('Nama dosen wajib diisi.'); return false; }
            }
            if (purposeVal === 'lainnya') {
                const other = document.getElementById('purpose_other_input').value.trim();
                if (!other) { alert('Keperluan lainnya wajib diisi.'); return false; }
            }

            return true;
        }

        // === Applicant Type Toggle ===
        const applicantRadios = document.querySelectorAll('input[name="applicant_type"]');
        const nimField = document.getElementById('nim-field');
        const nipField = document.getElementById('nip-field');
        const universityField = document.getElementById('university-field');
        const nimInput = document.getElementById('nim_input');
        const nipInput = document.getElementById('nip_input');
        const universityInput = document.getElementById('university_input');
        const studyProgramField = document.getElementById('study-program-field');
        const studyProgramTextField = document.getElementById('study-program-text-field');
        const studyProgramTextInput = document.getElementById('study_program_text_input');

        function updateFormForApplicantType() {
            const selected = document.querySelector('input[name="applicant_type"]:checked');
            if (!selected) return;

            const type = selected.value;
            const isDosenUndip = type === 'dosen_undip';
            const isNonUndip = type === 'dosen_non_undip';
            const isMahasiswa = type === 'mahasiswa';

            // NIM: only for mahasiswa
            nimField.classList.toggle('hidden', !isMahasiswa);
            nimInput.toggleAttribute('required', isMahasiswa);

            // NIP: only for dosen_undip
            nipField.classList.toggle('hidden', !isDosenUndip);
            nipInput.toggleAttribute('required', isDosenUndip);

            // University: only for Non Undip
            universityField.classList.toggle('hidden', !isNonUndip);
            universityInput.toggleAttribute('required', isNonUndip);

            // Study Program radio list: only for mahasiswa
            studyProgramField.classList.toggle('hidden', !isMahasiswa);
            
            // Study Program text field: only for Non Undip
            studyProgramTextField.classList.toggle('hidden', !isNonUndip);
            studyProgramTextInput.toggleAttribute('required', isNonUndip);

            // Hide "Lainnya" field if not mahasiswa
            if (!isMahasiswa) {
                document.getElementById('study-program-other-field').classList.add('hidden');
            }
        }

        applicantRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                updateFormForApplicantType();
                updateVariablesField();
            });
        });
        updateFormForApplicantType();
        updateVariablesField();

        // === Study Program "Lainnya" Toggle (Mahasiswa only) ===
        const studyProgramRadios = document.querySelectorAll('input[name="study_program"]');
        const studyProgramOtherField = document.getElementById('study-program-other-field');

        studyProgramRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Lainnya') {
                    studyProgramOtherField.classList.remove('hidden');
                } else {
                    studyProgramOtherField.classList.add('hidden');
                }
            });
        });

        // Initialize on load
        const selectedProgram = document.querySelector('input[name="study_program"]:checked');
        if (selectedProgram && selectedProgram.value === 'Lainnya') {
            studyProgramOtherField.classList.remove('hidden');
        }

        // === Sunday Validation, Blocked Dates & Friday Session ===
        const usageDateInput = document.getElementById('usage_date_input');
        const datePicker = document.getElementById('usage-date-picker');
        const dateTrigger = document.getElementById('usage-date-trigger');
        const dateTriggerText = document.getElementById('usage-date-trigger-text');
        const dateCalendar = document.getElementById('usage-date-calendar');
        const dateMonthLabel = document.getElementById('usage-date-month');
        const dateDays = document.getElementById('usage-date-days');
        const sundayWarning = document.getElementById('sunday-warning');
        const blockedDateWarning = document.getElementById('blocked-date-warning');
        const fridayNotice = document.getElementById('friday-notice');
        const session2Label = document.getElementById('session2-label');

        const minimumUsageDate = @json(date('Y-m-d'));
        const dateLabelFormatter = new Intl.DateTimeFormat('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
        const dateMonthFormatter = new Intl.DateTimeFormat('id-ID', {
            month: 'long', year: 'numeric'
        });

        function parseLocalDate(dateString) {
            const [year, month, day] = dateString.split('-').map(Number);
            return new Date(year, month - 1, day);
        }

        function toLocalDateString(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function isFullDayBlocked(dateString) {
            return blockedDates.find(block => block.date === dateString && block.blocked_session === null);
        }

        let visibleCalendarMonth = parseLocalDate(usageDateInput.value || minimumUsageDate);
        visibleCalendarMonth.setDate(1);

        function updateDateTrigger() {
            if (usageDateInput.value) {
                dateTriggerText.textContent = dateLabelFormatter.format(parseLocalDate(usageDateInput.value));
                dateTriggerText.classList.remove('text-slate-400');
                dateTriggerText.classList.add('text-slate-700', 'font-medium');
            } else {
                dateTriggerText.textContent = 'Pilih tanggal';
                dateTriggerText.classList.add('text-slate-400');
                dateTriggerText.classList.remove('text-slate-700', 'font-medium');
            }
        }

        function focusCalendarDate() {
            const focusableDay = dateDays.querySelector('.is-selected:not(:disabled)')
                || dateDays.querySelector('button:not(:disabled)');
            focusableDay?.focus();
        }

        function renderDateCalendar() {
            const year = visibleCalendarMonth.getFullYear();
            const month = visibleCalendarMonth.getMonth();
            const firstDayOffset = (new Date(year, month, 1).getDay() + 6) % 7;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const minimumMonth = parseLocalDate(minimumUsageDate);
            const monthIsAtMinimum = year === minimumMonth.getFullYear() && month === minimumMonth.getMonth();
            const monthIsBeforeMinimum = new Date(year, month, 1) < new Date(minimumMonth.getFullYear(), minimumMonth.getMonth(), 1);

            dateMonthLabel.textContent = dateMonthFormatter.format(visibleCalendarMonth);
            dateDays.replaceChildren();

            for (let blank = 0; blank < firstDayOffset; blank++) {
                const spacer = document.createElement('span');
                spacer.setAttribute('aria-hidden', 'true');
                dateDays.appendChild(spacer);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateString = toLocalDateString(date);
                const blocked = isFullDayBlocked(dateString);
                const unavailable = dateString < minimumUsageDate || date.getDay() === 0 || Boolean(blocked);
                const button = document.createElement('button');

                button.type = 'button';
                button.className = 'bloomberg-calendar-day mx-auto flex w-full items-center justify-center rounded-lg border border-transparent text-sm text-slate-700 hover:border-indigo-200 hover:bg-indigo-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-500';
                button.textContent = String(day);
                const dateLabel = dateLabelFormatter.format(date);
                const isSelectedDate = dateString === usageDateInput.value;
                button.setAttribute('aria-label', isSelectedDate ? `Tanggal dipilih, ${dateLabel}` : dateLabel);
                if (isSelectedDate) button.classList.add('is-selected');

                if (dateString === minimumUsageDate) {
                    button.setAttribute('aria-current', 'date');
                }

                if (unavailable) {
                    button.disabled = true;
                    button.classList.remove('text-slate-700', 'hover:border-indigo-200', 'hover:bg-indigo-50');
                    button.title = blocked ? `Tanggal diblokir${blocked.reason ? `: ${blocked.reason}` : ''}` : (date.getDay() === 0 ? 'Reservasi tidak tersedia pada hari Minggu' : 'Tanggal sudah lewat');
                }

                button.addEventListener('click', () => {
                    usageDateInput.value = dateString;
                    updateDateTrigger();
                    dateDays.querySelector('.is-selected')?.classList.remove('is-selected');
                    button.classList.add('is-selected');
                    usageDateInput.dispatchEvent(new Event('change', { bubbles: true }));
                    closeDateCalendar(true);
                });

                dateDays.appendChild(button);
            }

            document.getElementById('usage-date-prev').disabled = monthIsAtMinimum || monthIsBeforeMinimum;
        }

        function openDateCalendar() {
            if (!dateCalendar.classList.contains('hidden')) return;

            dateCalendar.classList.remove('hidden');
            dateTrigger.setAttribute('aria-expanded', 'true');
            renderDateCalendar();
            focusCalendarDate();
        }

        function closeDateCalendar(restoreFocus = false) {
            if (dateCalendar.classList.contains('hidden')) return;

            dateCalendar.classList.add('hidden');
            dateTrigger.setAttribute('aria-expanded', 'false');
            if (restoreFocus) dateTrigger.focus();
        }

        dateTrigger.addEventListener('click', () => {
            if (dateCalendar.classList.contains('hidden')) {
                openDateCalendar();
            } else {
                closeDateCalendar();
            }
        });

        document.getElementById('usage-date-prev').addEventListener('click', () => {
            visibleCalendarMonth = new Date(visibleCalendarMonth.getFullYear(), visibleCalendarMonth.getMonth() - 1, 1);
            renderDateCalendar();
            focusCalendarDate();
        });

        document.getElementById('usage-date-next').addEventListener('click', () => {
            visibleCalendarMonth = new Date(visibleCalendarMonth.getFullYear(), visibleCalendarMonth.getMonth() + 1, 1);
            renderDateCalendar();
            focusCalendarDate();
        });

        document.addEventListener('pointerdown', event => {
            if (!datePicker.contains(event.target)) closeDateCalendar();
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !dateCalendar.classList.contains('hidden')) {
                closeDateCalendar(true);
            }
        });

        updateDateTrigger();
        renderDateCalendar();

        /**
         * Check blocked status for a given date and optionally a specific session.
         * Returns: { isBlocked: bool, reason: string|null, type: 'full'|'session'|null }
         */
        function getBlockedInfo(dateStr, sessionValue) {
            // Check full day block first
            const fullBlock = blockedDates.find(b => b.date === dateStr && b.blocked_session === null);
            if (fullBlock) {
                return { isBlocked: true, reason: fullBlock.reason, type: 'full' };
            }

            // Check session-specific block
            if (sessionValue) {
                const sessionBlock = blockedDates.find(b => b.date === dateStr && b.blocked_session === sessionValue);
                if (sessionBlock) {
                    return { isBlocked: true, reason: sessionBlock.reason, type: 'session' };
                }
            }

            return { isBlocked: false, reason: null, type: null };
        }

        const session2RegularLabel = @json($sessions['sesi_2']);
        const session2FridayLabel = @json($sessionsFriday['sesi_2']);

        function validateDate() {
            if (!usageDateInput.value) {
                sundayWarning.classList.add('hidden');
                blockedDateWarning.classList.add('hidden');
                fridayNotice.classList.add('hidden');
                dateTrigger.classList.remove('border-red-500');
                dateTrigger.setAttribute('aria-invalid', 'false');
                session2Label.textContent = session2RegularLabel;
                updateSessionBlockedState();
                return;
            }

            const date = new Date(usageDateInput.value);
            const isSunday = date.getDay() === 0;
            const isFriday = date.getDay() === 5;
            
            // Get selected session for block checking
            const selectedSession = document.querySelector('input[name="session"]:checked');
            const blockedInfo = getBlockedInfo(usageDateInput.value, selectedSession?.value);

            // Sunday check
            if (isSunday) {
                sundayWarning.classList.remove('hidden');
                dateTrigger.classList.add('border-red-500');
                dateTrigger.setAttribute('aria-invalid', 'true');
            } else {
                sundayWarning.classList.add('hidden');
            }

            // Blocked date check (full day)
            if (blockedInfo.type === 'full') {
                blockedDateWarning.classList.remove('hidden');
                blockedDateWarning.innerHTML = `
                    <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Tanggal ini tidak tersedia untuk reservasi Bloomberg. <strong>Alasan: ${blockedInfo.reason || '-'}</strong></span>
                `;
                dateTrigger.classList.add('border-red-500');
                dateTrigger.setAttribute('aria-invalid', 'true');
            } else {
                blockedDateWarning.classList.add('hidden');
            }

            // Remove red border only if neither Sunday nor blocked
            if (!isSunday && blockedInfo.type !== 'full') {
                dateTrigger.classList.remove('border-red-500');
                dateTrigger.setAttribute('aria-invalid', 'false');
            }

            // Friday session adjustment
            if (isFriday) {
                fridayNotice.classList.remove('hidden');
                session2Label.textContent = session2FridayLabel;
            } else {
                fridayNotice.classList.add('hidden');
                session2Label.textContent = session2RegularLabel;
            }

            // Update per-session blocked indicators
            updateSessionBlockedState();
        }

        /**
         * Update visual state of session radio buttons based on blocked dates.
         * Shows warning per session if that specific session is blocked.
         */
        function updateSessionBlockedState() {
            const dateVal = usageDateInput.value;
            if (!dateVal) return;

            const sessionCards = document.querySelectorAll('input[name="session"]');
            
            // Remove any existing session-blocked warnings
            document.querySelectorAll('.session-blocked-warning').forEach(el => el.remove());

            sessionCards.forEach(radio => {
                const card = radio.closest('.radio-card');
                const blocked = getBlockedInfo(dateVal, radio.value);
                const sessionName = radio.value === 'sesi_1' ? 'Sesi 1' : 'Sesi 2';
                
                if (blocked.isBlocked) {
                    card.classList.add('opacity-50', 'bg-gray-50', 'cursor-not-allowed');
                    card.classList.remove('cursor-pointer', 'hover:border-indigo-300');
                    radio.disabled = true;
                    if (radio.checked) {
                        radio.checked = false;
                        // Avoid triggering full fetchCapacity loop to prevent infinite loop
                        const indicator = document.getElementById('capacity-indicator');
                        if (indicator) indicator.classList.add('hidden');
                    }

                    // Add warning below the grid
                    const warning = document.createElement('div');
                    warning.className = 'session-blocked-warning col-span-1 sm:col-span-2 text-red-600 text-sm mt-1 flex items-center font-medium';
                    warning.innerHTML = `
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                        <span>${sessionName} tidak tersedia: ${blocked.reason || 'Sesi diblokir'}</span>
                    `;
                    card.parentElement.appendChild(warning);
                } else {
                    card.classList.remove('opacity-50', 'bg-gray-50', 'cursor-not-allowed');
                    card.classList.add('cursor-pointer', 'hover:border-indigo-300');
                    radio.disabled = false;
                }
            });
        }

        if (usageDateInput) {
            usageDateInput.addEventListener('change', function() {
                validateDate();
                fetchCapacity();
            });
            validateDate(); // Run on load
        }

        // === Session Radio - Trigger capacity fetch and update blocked state ===
        const sessionRadios = document.querySelectorAll('input[name="session"]');
        sessionRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                fetchCapacity();
                validateDate(); // Re-validate to update blocked warnings
            });
        });

        // === AJAX Capacity Check (reservation only) ===
        let capacityFull = false;

        function fetchCapacity() {
            if (isWalkIn) return; // Walk-in doesn't check capacity

            const dateVal = usageDateInput.value;
            const sessionEl = document.querySelector('input[name="session"]:checked');
            const indicator = document.getElementById('capacity-indicator');
            const capacityText = document.getElementById('capacity-text');
            const capacityBar = document.getElementById('capacity-bar');
            const capacityBox = document.getElementById('capacity-box');
            const fullWarning = document.getElementById('capacity-full-warning');

            if (!indicator) return;

            if (!dateVal || !sessionEl) {
                indicator.classList.add('hidden');
                capacityFull = false;
                return;
            }

            // Fetch from API
            fetch(`{{ route('bloomberg.capacity') }}?date=${dateVal}&session=${sessionEl.value}`)
                .then(res => res.json())
                .then(data => {
                    // If session is blocked, hide capacity and show as blocked
                    if (data.blocked) {
                        indicator.classList.add('hidden');
                        capacityFull = true;
                        return;
                    }

                    indicator.classList.remove('hidden');
                    const remaining = data.remaining;
                    const total = data.capacity;
                    const pct = total > 0 ? (remaining / total * 100) : 0;

                    capacityText.textContent = `${remaining} / ${total} tersedia`;
                    capacityBar.style.width = `${pct}%`;

                    if (remaining <= 0) {
                        capacityFull = true;
                        capacityBox.className = 'p-4 rounded-xl border-2 border-red-200 bg-red-50';
                        capacityBar.className = 'bg-red-500 h-2.5 rounded-full transition-all duration-300';
                        capacityText.className = 'text-sm font-bold text-red-800';
                        fullWarning.classList.remove('hidden');
                    } else if (remaining <= 3) {
                        capacityFull = false;
                        capacityBox.className = 'p-4 rounded-xl border-2 border-yellow-200 bg-yellow-50';
                        capacityBar.className = 'bg-yellow-500 h-2.5 rounded-full transition-all duration-300';
                        capacityText.className = 'text-sm font-bold text-yellow-800';
                        fullWarning.classList.add('hidden');
                    } else {
                        capacityFull = false;
                        capacityBox.className = 'p-4 rounded-xl border-2 border-indigo-200 bg-indigo-50';
                        capacityBar.className = 'bg-indigo-600 h-2.5 rounded-full transition-all duration-300';
                        capacityText.className = 'text-sm font-bold text-indigo-800';
                        fullWarning.classList.add('hidden');
                    }
                })
                .catch(() => {
                    indicator.classList.add('hidden');
                    capacityFull = false;
                });
        }

        // === Purpose Conditional Fields ===
        const purposeRadios = document.querySelectorAll('.purpose-radio');
        const researchTitleField = document.getElementById('research-title-field');
        const subjectNameField = document.getElementById('subject-name-field');
        const lecturerNameField = document.getElementById('lecturer-name-field');
        const purposeOtherField = document.getElementById('purpose-other-field');
        const researchTitleLabel = document.getElementById('research-title-label');

        purposeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Hide all conditional fields
                researchTitleField.classList.add('hidden');
                subjectNameField.classList.add('hidden');
                lecturerNameField.classList.add('hidden');
                purposeOtherField.classList.add('hidden');

                // Show relevant field
                if (['skripsi', 'thesis', 'disertasi'].includes(this.value)) {
                    researchTitleField.classList.remove('hidden');
                    researchTitleLabel.textContent = 'Judul Penelitian';
                } else if (this.value === 'lomba') {
                    researchTitleField.classList.remove('hidden');
                    researchTitleLabel.textContent = 'Nama Lomba';
                } else if (this.value === 'tugas_mk') {
                    subjectNameField.classList.remove('hidden');
                } else if (this.value === 'penelitian_dosen') {
                    lecturerNameField.classList.remove('hidden');
                } else if (this.value === 'lainnya') {
                    purposeOtherField.classList.remove('hidden');
                }

                updateVariablesField();
            });
        });

        // Initialize on load
        const selectedPurpose = document.querySelector('.purpose-radio:checked');
        if (selectedPurpose) {
            selectedPurpose.dispatchEvent(new Event('change'));
        }

        // === File Validation ===
        const fileInput = document.getElementById('statement_file_input');
        const fileError = document.getElementById('file-error');
        const fileErrorText = document.getElementById('file-error-text');
        const fileSuccess = document.getElementById('file-success');
        const fileSuccessText = document.getElementById('file-success-text');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                fileError.classList.add('hidden');
                fileSuccess.classList.add('hidden');

                if (this.files.length === 0) return;
                const file = this.files[0];

                // Check file type
                if (file.type !== 'application/pdf') {
                    fileError.classList.remove('hidden');
                    fileErrorText.textContent = 'File harus berupa PDF.';
                    this.value = '';
                    return;
                }

                // Check file size (5MB)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    fileError.classList.remove('hidden');
                    fileErrorText.textContent = `Ukuran file (${(file.size / 1024 / 1024).toFixed(2)} MB) melebihi batas maksimal 5MB.`;
                    this.value = '';
                    return;
                }

                fileSuccess.classList.remove('hidden');
                fileSuccessText.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) — File valid`;
            });
        }

        // === Form Submit Validation ===
        const form = document.getElementById('bloomberg-form');
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', function(e) {
            if (!document.getElementById('agreement_citation').checked || !document.getElementById('agreement_compliance').checked) {
                e.preventDefault();
                alert('Anda harus menyetujui kedua pernyataan persetujuan.');
                return;
            }

            // Disable Non Undip study_program text input if not Non Undip, to avoid conflict with radio study_program
            const applicantType = document.querySelector('input[name="applicant_type"]:checked');
            if (applicantType && applicantType.value !== 'dosen_non_undip') {
                studyProgramTextInput.disabled = true;
            }

            // Double submit prevention
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...';
        });
    </script>
</body>
</html>
