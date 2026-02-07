<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Permohonan Akses Data Refinitiv - Lab Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .step-indicator { transition: all 0.3s ease; }
        .step-indicator.active { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; }
        .step-indicator.completed { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 min-h-screen">
    
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="{{ route('landing') }}" class="flex items-center space-x-3">
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-12 lg:h-16 w-auto object-contain">
                </a>
                <a href="{{ route('data.index') }}" class="px-4 py-2 text-gray-600 hover:text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-all duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-600 via-blue-500 to-blue-700 text-white py-8 lg:py-12">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-2xl lg:text-3xl font-bold mb-3">Permohonan Akses Data Refinitiv</h1>
                <p class="text-blue-100 text-sm lg:text-base">Laboratorium Komputer FEB Universitas Diponegoro</p>
            </div>
        </div>
    </section>

    <!-- Important Info -->
    <section class="py-6 bg-white border-b">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 lg:p-6">
                    <h3 class="font-bold text-blue-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Tata Tertib & Mekanisme
                    </h3>
                    <div class="text-sm text-blue-700 space-y-2">
                        <p><strong>TATA TERTIB:</strong> Tidak memperjualbelikan atau menyebarluaskan data tanpa seizin FEB-UNDIP</p>
                        <div>
                            <strong>MEKANISME:</strong>
                            <ol class="list-decimal ml-5 mt-1 space-y-1">
                                <li>Mengisi formulir permohonan <strong>paling lambat sehari sebelum</strong> jadwal penggunaan</li>
                                <li>Menuliskan jadwal penggunaan dan mengisi seluruh formulir</li>
                                <li>Menggunakan lab Refinitiv sesuai tanggal dan sesi yang diajukan</li>
                                <li>Menuju ruang <strong>EL.3.05</strong> di Gedung Laboratorium FEB-UNDIP lt.3</li>
                            </ol>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-200">
                        <a href="https://bit.ly/SuratPernyataanKesanggupanMenjagaInformasi" target="_blank" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download Format Surat Pernyataan Kesanggupan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Error Message -->
    @if(session('error'))
        <div class="container mx-auto px-4 lg:px-8 mt-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-r-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Form Section -->
    <section class="py-8 lg:py-12">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <form action="{{ route('refinitiv.store') }}" method="POST" enctype="multipart/form-data" id="refinitivForm">
                    @csrf
                    
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <!-- Step 1: Data Diri -->
                        <div class="p-6 lg:p-8 border-b border-gray-100">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">1</span>
                                Data Diri
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Status Pemohon (Mahasiswa/Dosen) -->
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-semibold mb-3">
                                        Status Pemohon <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="applicant_type" value="mahasiswa" id="type_mahasiswa"
                                                {{ old('applicant_type', 'mahasiswa') == 'mahasiswa' ? 'checked' : '' }}
                                                class="peer sr-only" required>
                                            <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                                <div class="flex items-center justify-center mb-2">
                                                    <svg class="w-8 h-8 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-center font-semibold text-gray-800">Mahasiswa</p>
                                            </div>
                                        </label>
                                        <label class="relative cursor-pointer">
                                            <input type="radio" name="applicant_type" value="dosen" id="type_dosen"
                                                {{ old('applicant_type') == 'dosen' ? 'checked' : '' }}
                                                class="peer sr-only">
                                            <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                                <div class="flex items-center justify-center mb-2">
                                                    <svg class="w-8 h-8 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-center font-semibold text-gray-800">Dosen</p>
                                            </div>
                                        </label>
                                    </div>
                                    @error('applicant_type')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Nama -->
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Nama <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                        placeholder="Nama lengkap Anda"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- NIM (for Mahasiswa) -->
                                <div id="nim_field">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        NIM <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nim" id="nim_input" value="{{ old('nim') }}"
                                        placeholder="Masukkan NIM (14 digit)"
                                        pattern="[0-9]{14}"
                                        maxlength="14"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nim') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">NIM harus 14 digit angka</p>
                                    @error('nim')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- NIP (for Dosen) -->
                                <div id="nip_field" class="hidden">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        NIP <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nip" id="nip_input" value="{{ old('nip') }}"
                                        placeholder="Masukkan NIP (18 digit)"
                                        pattern="[0-9]{18}"
                                        maxlength="18"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nip') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">NIP harus 18 digit angka</p>
                                    @error('nip')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- WhatsApp -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Nomor WhatsApp <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="whatsapp" id="whatsapp_input" value="{{ old('whatsapp') }}" required
                                        placeholder="08xxxxxxxxxx"
                                        pattern="^08[0-9]{8,13}$"
                                        minlength="10"
                                        maxlength="15"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('whatsapp') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">Nomor harus diawali 08 dan 10-15 digit</p>
                                    @error('whatsapp')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Keterangan -->
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Keterangan <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        @foreach($affiliations as $value => $label)
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors">
                                                <input type="radio" name="affiliation" value="{{ $value }}" 
                                                    {{ old('affiliation') == $value ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500" required>
                                                <span class="ml-3 text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('affiliation')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Program Studi (for Mahasiswa only) -->
                                <div id="study_program_field" class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Program Studi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="study_program" id="study_program_select"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('study_program') border-red-500 @enderror">
                                        <option value="">Pilih Program Studi</option>
                                        @foreach($studyPrograms as $program)
                                            <option value="{{ $program }}" {{ old('study_program') == $program ? 'selected' : '' }}>{{ $program }}</option>
                                        @endforeach
                                    </select>
                                    @error('study_program')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Program Studi Lainnya (conditional) -->
                                <div id="study_program_other_field" class="md:col-span-2 hidden">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Program Studi Lainnya <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="study_program_other" id="study_program_other_input" value="{{ old('study_program_other') }}"
                                        placeholder="Tuliskan program studi Anda"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                                    @error('study_program_other')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Keperluan & Jadwal -->
                        <div class="p-6 lg:p-8 border-b border-gray-100 bg-gray-50">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">2</span>
                                Keperluan & Jadwal
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Keperluan -->
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Keperluan Penggunaan Data <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach($purposes as $value => $label)
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors bg-white">
                                                <input type="radio" name="purpose" value="{{ $value }}" 
                                                    {{ old('purpose') == $value ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500 purpose-radio">
                                                <span class="ml-3 text-gray-700 text-sm">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('purpose')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Purpose Other (conditional) -->
                                <div id="purpose_other_field" class="md:col-span-2 hidden">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Keperluan Lainnya <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="purpose_other" value="{{ old('purpose_other') }}"
                                        placeholder="Tuliskan keperluan Anda"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                                    @error('purpose_other')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Lecturer Name (conditional) -->
                                <div id="lecturer_name_field" class="md:col-span-2 hidden">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Penelitian/Project dengan Dosen <span class="text-red-500">*</span>
                                        <span class="text-gray-500 font-normal text-xs block">*Tuliskan nama dosen</span>
                                    </label>
                                    <input type="text" name="lecturer_name" value="{{ old('lecturer_name') }}"
                                        placeholder="Nama dosen pembimbing/kolaborasi"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                                    @error('lecturer_name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Tanggal Pemakaian -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Tanggal Pemakaian <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="usage_date" id="usage_date_input" value="{{ old('usage_date') }}" required
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white @error('usage_date') border-red-500 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">⚠️ Pemakaian tidak tersedia pada hari Minggu</p>
                                    <!-- Sunday Warning -->
                                    <div id="sunday-warning" class="hidden mt-2 bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-sm">
                                        ⚠️ Hari Minggu tidak tersedia untuk pemakaian data Refinitiv. Silakan pilih tanggal lain.
                                    </div>
                                    @error('usage_date')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Sesi/Pukul -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Pukul <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        @foreach($sessions as $value => $label)
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors bg-white">
                                                <input type="radio" name="session" value="{{ $value }}" 
                                                    {{ old('session') == $value ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                                <span class="ml-3 text-gray-700 text-sm">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('session')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Variabel -->
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Variabel yang dibutuhkan? <span class="text-red-500">*</span>
                                        <span class="text-gray-500 font-normal text-xs block">Format Penulisan Variabel: (Variabel 1, Variabel 2, Variabel 3, dst)</span>
                                    </label>
                                    <textarea name="variables" rows="3" required
                                        placeholder="Contoh: Harga Saham, Volume Trading, Market Cap, ROE, ROA"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white @error('variables') border-red-500 @enderror">{{ old('variables') }}</textarea>
                                    @error('variables')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Dokumen & Persetujuan -->
                        <div class="p-6 lg:p-8">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">3</span>
                                Dokumen & Persetujuan
                            </h2>
                            
                            <div class="space-y-6">
                                <!-- KTM Upload (conditional for students) -->
                                <div id="ktm_field">
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Upload KTM <span class="text-red-500" id="ktm_required">*</span>
                                        <span class="text-gray-500 font-normal text-xs block">*wajib bagi mahasiswa. Maks 10 MB (PDF/JPG/PNG)</span>
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 transition-colors">
                                        <input type="file" name="ktm_file" id="ktm_file" accept=".pdf,.jpg,.jpeg,.png"
                                            class="w-full text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                    @error('ktm_file')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Surat Pernyataan Upload -->
                                <div>
                                    <label class="block text-gray-700 text-sm font-semibold mb-2">
                                        Surat Pernyataan Kesanggupan <span class="text-red-500">*</span>
                                        <span class="text-gray-500 font-normal text-xs block">*format surat dapat diunduh di atas. Maks 10 MB (PDF/JPG/PNG)</span>
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-blue-400 transition-colors">
                                        <input type="file" name="statement_file" id="statement_file" accept=".pdf,.jpg,.jpeg,.png" required
                                            class="w-full text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                    @error('statement_file')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Agreement Checkbox -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox" name="agreement" value="1" required
                                            {{ old('agreement') ? 'checked' : '' }}
                                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 mt-0.5">
                                        <span class="ml-3 text-gray-700 text-sm">
                                            Dengan ini saya bersedia untuk mematuhi aturan pengunaan data Refinitiv Laboratorium Komputer FEB Universitas Diponegoro. Bahwa saya tidak akan memperjualbelikan atau menyebarluaskan data yang saya gunakan dari Laboratorium Komputer FEB Universitas Diponegoro.
                                            <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                    @error('agreement')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Understood Checkbox -->
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox" name="understood" value="1" required
                                            {{ old('understood') ? 'checked' : '' }}
                                            class="w-5 h-5 text-yellow-600 rounded focus:ring-yellow-500 mt-0.5">
                                        <span class="ml-3 text-gray-700 text-sm">
                                            <strong>Setelah mengirim form</strong>, silahkan untuk langsung menuju Gd. Lab lantai 3 di ruangan <strong>EL. 3.05</strong> untuk melakukan konfirmasi kepada asisten lab <strong>sesuai jadwal pemakaian yang diajukan</strong>, untuk diarahkan lebih lanjut, terima kasih.
                                            <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                    @error('understood')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Submit Button -->
                                <div class="pt-4">
                                    <button type="submit" id="submitBtn"
                                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        Kirim Permohonan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} Lab Digital FEB UNDIP. All rights reserved.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeMahasiswa = document.getElementById('type_mahasiswa');
            const typeDosen = document.getElementById('type_dosen');
            const nimField = document.getElementById('nim_field');
            const nipField = document.getElementById('nip_field');
            const nimInput = document.getElementById('nim_input');
            const nipInput = document.getElementById('nip_input');
            const studyProgramField = document.getElementById('study_program_field');
            const studyProgramSelect = document.getElementById('study_program_select');
            const studyProgramOtherField = document.getElementById('study_program_other_field');
            const studyProgramOtherInput = document.getElementById('study_program_other_input');
            const ktmField = document.getElementById('ktm_field');
            const ktmRequired = document.getElementById('ktm_required');
            const ktmFileInput = document.getElementById('ktm_file');
            
            const purposeRadios = document.querySelectorAll('.purpose-radio');
            const purposeOtherField = document.getElementById('purpose_other_field');
            const lecturerNameField = document.getElementById('lecturer_name_field');

            function updateFormForApplicantType() {
                const isDosen = typeDosen.checked;
                
                if (isDosen) {
                    // Dosen selected
                    nimField.classList.add('hidden');
                    nipField.classList.remove('hidden');
                    nimInput.removeAttribute('required');
                    nipInput.setAttribute('required', 'required');
                    
                    studyProgramField.classList.add('hidden');
                    studyProgramSelect.removeAttribute('required');
                    studyProgramSelect.value = '';
                    studyProgramOtherField.classList.add('hidden');
                    studyProgramOtherInput.value = '';
                    
                    ktmField.classList.add('hidden');
                    ktmFileInput.removeAttribute('required');
                    ktmRequired.classList.add('hidden');
                } else {
                    // Mahasiswa selected
                    nimField.classList.remove('hidden');
                    nipField.classList.add('hidden');
                    nimInput.setAttribute('required', 'required');
                    nipInput.removeAttribute('required');
                    
                    studyProgramField.classList.remove('hidden');
                    studyProgramSelect.setAttribute('required', 'required');
                    updateStudyProgramOther();
                    
                    ktmField.classList.remove('hidden');
                    ktmFileInput.setAttribute('required', 'required');
                    ktmRequired.classList.remove('hidden');
                }
            }

            // Listen to applicant type changes
            typeMahasiswa.addEventListener('change', updateFormForApplicantType);
            typeDosen.addEventListener('change', updateFormForApplicantType);

            // Purpose radio changes
            purposeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Show/hide purpose other field
                    if (this.value === 'lainnya') {
                        purposeOtherField.classList.remove('hidden');
                    } else {
                        purposeOtherField.classList.add('hidden');
                    }
                    
                    // Show/hide lecturer name field
                    if (this.value === 'penelitian_dosen') {
                        lecturerNameField.classList.remove('hidden');
                    } else {
                        lecturerNameField.classList.add('hidden');
                    }
                });
            });

            // Study Program select changes
            function updateStudyProgramOther() {
                if (studyProgramSelect.value === 'Lainnya') {
                    studyProgramOtherField.classList.remove('hidden');
                    studyProgramOtherInput.setAttribute('required', 'required');
                } else {
                    studyProgramOtherField.classList.add('hidden');
                    studyProgramOtherInput.removeAttribute('required');
                }
            }
            
            studyProgramSelect.addEventListener('change', updateStudyProgramOther);

            // Initialize state on page load
            updateFormForApplicantType();

            // Initialize purpose fields
            const checkedPurpose = document.querySelector('.purpose-radio:checked');
            if (checkedPurpose) {
                if (checkedPurpose.value === 'lainnya') {
                    purposeOtherField.classList.remove('hidden');
                }
                if (checkedPurpose.value === 'penelitian_dosen') {
                    lecturerNameField.classList.remove('hidden');
                }
            }

            // Sunday validation for usage_date
            const usageDateInput = document.getElementById('usage_date_input');
            const sundayWarning = document.getElementById('sunday-warning');
            const submitBtn = document.getElementById('submitBtn');
            
            function validateSunday() {
                if (usageDateInput.value) {
                    const date = new Date(usageDateInput.value);
                    const isSunday = date.getDay() === 0; // 0 = Sunday
                    
                    if (isSunday) {
                        sundayWarning.classList.remove('hidden');
                        usageDateInput.classList.add('border-red-500');
                    } else {
                        sundayWarning.classList.add('hidden');
                        usageDateInput.classList.remove('border-red-500');
                    }
                } else {
                    sundayWarning.classList.add('hidden');
                    usageDateInput.classList.remove('border-red-500');
                }
            }
            
            usageDateInput.addEventListener('change', validateSunday);
            validateSunday(); // Run on load in case old value was Sunday

            // Form submission with validation
            document.getElementById('refinitivForm').addEventListener('submit', function(e) {
                const isDosen = typeDosen.checked;
                const whatsapp = document.getElementById('whatsapp_input').value.trim();
                
                // Check for Sunday
                if (usageDateInput.value) {
                    const date = new Date(usageDateInput.value);
                    if (date.getDay() === 0) {
                        e.preventDefault();
                        alert('Pemakaian data Refinitiv tidak tersedia pada hari Minggu. Silakan pilih tanggal lain.');
                        usageDateInput.focus();
                        return false;
                    }
                }
                
                // Validate WhatsApp
                if (!/^08[0-9]{8,13}$/.test(whatsapp)) {
                    e.preventDefault();
                    alert('Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit angka');
                    document.getElementById('whatsapp_input').focus();
                    return false;
                }
                
                if (isDosen) {
                    const nip = nipInput.value.trim();
                    if (!/^[0-9]{18}$/.test(nip)) {
                        e.preventDefault();
                        alert('NIP harus 18 digit angka');
                        nipInput.focus();
                        return false;
                    }
                } else {
                    const nim = nimInput.value.trim();
                    if (!/^[0-9]{14}$/.test(nim)) {
                        e.preventDefault();
                        alert('NIM harus 14 digit angka');
                        nimInput.focus();
                        return false;
                    }
                }
                
                // Loading state
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...';
            });
        });
    </script>
</body>
</html>
