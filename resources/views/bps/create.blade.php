<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Permohonan Data BPS - Lab Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }
        .step-item.completed::after {
            background: #22c55e;
        }
        .step-item.active .step-number {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        .step-item.completed .step-number {
            background: #22c55e;
            color: white;
            border-color: #22c55e;
        }
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e5e7eb;
            color: #6b7280;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .dataset-checkbox:checked + label {
            background-color: #dbeafe;
            border-color: #3b82f6;
        }
        .sub-data-item {
            transition: all 0.2s ease;
        }
        .sub-data-item:hover {
            background-color: #f0f9ff;
        }
        .variable-input-container {
            display: none;
        }
        .variable-input-container.show {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 md:px-6 py-3 md:py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('landing') }}">
                        <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-10 md:h-16 w-auto object-contain">
                    </a>
                </div>
                <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 md:px-6 py-2 rounded-lg font-bold transition-all shadow-sm hover:shadow-md text-sm md:text-base whitespace-nowrap">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 md:px-6 py-4 md:py-12 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-6 md:mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl md:text-4xl font-bold text-gray-800 mb-2 md:mb-3">Permohonan Data BPS</h1>
            <p class="text-sm md:text-base text-gray-600 px-4">Formulir Permohonan Akses Penggunaan Data BPS yang Dikelola Oleh Laboratorium Komputer FEB Universitas Diponegoro</p>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 md:p-6 mb-6">
            <h3 class="font-bold text-blue-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Tata Tertib Penggunaan Data BPS
            </h3>
            <p class="text-sm text-blue-700 mb-3">
                Tidak memperjualbelikan atau menyebarluaskan data yang telah diberikan tanpa ijin FEB-UNDIP
            </p>
            <div class="text-sm text-blue-600 space-y-1">
                <p>• Data akan dikirimkan dalam bentuk link G-Drive dalam 1-3 hari kerja</p>
                <p>• Setelah 3 hari data akan dihapus dari link G-Drive</p>
                <p>• Untuk kode variabel, lihat <a href="https://bit.ly/LegendaDataBPS" target="_blank" class="underline font-semibold hover:text-blue-800">Legenda Data BPS</a></p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-8">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-red-800 font-semibold">Terdapat kesalahan:</h3>
                    </div>
                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Step Indicator -->
            <div class="step-indicator mb-6 md:mb-8 px-2 md:px-0">
                <div class="step-item active" id="step-indicator-1">
                    <div class="step-number">1</div>
                    <div class="text-xs md:text-sm font-medium">Identitas</div>
                </div>
                <div class="step-item" id="step-indicator-2">
                    <div class="step-number">2</div>
                    <div class="text-xs md:text-sm font-medium">Data</div>
                </div>
                <div class="step-item" id="step-indicator-3">
                    <div class="step-number">3</div>
                    <div class="text-xs md:text-sm font-medium">Dokumen</div>
                </div>
            </div>

            <form action="{{ route('bps.store') }}" method="POST" enctype="multipart/form-data" id="bpsForm">
                @csrf

                <!-- Step 1: Identitas -->
                <div id="step-1" class="step-content">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-3">1</span>
                        Data Identitas Pemohon
                    </h2>

                    <!-- Applicant Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Status Pemohon <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative">
                                <input type="radio" name="applicant_type" value="mahasiswa" class="peer sr-only" {{ old('applicant_type', 'mahasiswa') === 'mahasiswa' ? 'checked' : '' }} required>
                                <div class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-8 h-8 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                        </svg>
                                    </div>
                                    <p class="text-center font-semibold text-gray-800">Mahasiswa</p>
                                </div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="applicant_type" value="dosen" class="peer sr-only" {{ old('applicant_type') === 'dosen' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-xl cursor-pointer transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50">
                                    <div class="flex items-center justify-center mb-2">
                                        <svg class="w-8 h-8 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-center font-semibold text-gray-800">Dosen</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama lengkap">
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Alamat Surel (Email) <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                            title="Masukkan alamat email yang valid"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="email@gmail.com (bukan SSO)">
                        <p class="text-xs text-gray-500 mt-1">*Gunakan email pribadi (Gmail, Yahoo, dll), bukan SSO</p>
                        <p id="email-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- NIM (for Mahasiswa) -->
                    <div id="nim-field" class="mb-4">
                        <label for="nim" class="block text-sm font-semibold text-gray-700 mb-2">
                            NIM <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nim" name="nim" value="{{ old('nim') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan NIM (14 digit)"
                            pattern="[0-9]{14}"
                            maxlength="14"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)">
                        <p class="text-xs text-gray-500 mt-1">NIM harus 14 digit angka</p>
                        <p id="nim-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- NIP (for Dosen) -->
                    <div id="nip-field" class="mb-4 hidden">
                        <label for="nip" class="block text-sm font-semibold text-gray-700 mb-2">
                            NIP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan NIP (18 digit)"
                            pattern="[0-9]{18}"
                            maxlength="18"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)">
                        <p class="text-xs text-gray-500 mt-1">NIP harus 18 digit angka</p>
                        <p id="nip-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nomor WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="08xxxxxxxxxx"
                            pattern="^08[0-9]{8,13}$"
                            minlength="10"
                            maxlength="15"
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)">
                        <p class="text-xs text-gray-500 mt-1">Nomor harus diawali 08 dan 10-15 digit</p>
                        <p id="phone-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- Study Program (for Mahasiswa) -->
                    <div id="study-program-field" class="mb-4">
                        <label for="study_program" class="block text-sm font-semibold text-gray-700 mb-2">
                            Program Studi <span class="text-red-500">*</span>
                        </label>
                        <select id="study_program" name="study_program"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Program Studi</option>
                            <option value="S1- Ekonomi" {{ old('study_program') == 'S1- Ekonomi' ? 'selected' : '' }}>S1- Ekonomi</option>
                            <option value="S1- Manajemen" {{ old('study_program') == 'S1- Manajemen' ? 'selected' : '' }}>S1- Manajemen</option>
                            <option value="S1- Akuntansi" {{ old('study_program') == 'S1- Akuntansi' ? 'selected' : '' }}>S1- Akuntansi</option>
                            <option value="S1- Ekonomi Islam" {{ old('study_program') == 'S1- Ekonomi Islam' ? 'selected' : '' }}>S1- Ekonomi Islam</option>
                            <option value="S1- Bisnis Digital" {{ old('study_program') == 'S1- Bisnis Digital' ? 'selected' : '' }}>S1- Bisnis Digital</option>
                            <option value="S2- Ekonomi" {{ old('study_program') == 'S2- Ekonomi' ? 'selected' : '' }}>S2- Ekonomi</option>
                            <option value="S2- Manajemen" {{ old('study_program') == 'S2- Manajemen' ? 'selected' : '' }}>S2- Manajemen</option>
                            <option value="S2- Akuntansi" {{ old('study_program') == 'S2- Akuntansi' ? 'selected' : '' }}>S2- Akuntansi</option>
                            <option value="Sekolah Vokasi" {{ old('study_program') == 'Sekolah Vokasi' ? 'selected' : '' }}>Sekolah Vokasi</option>
                            <option value="S3- PDIE Ilmu Ekonomi" {{ old('study_program') == 'S3- PDIE Ilmu Ekonomi' ? 'selected' : '' }}>S3- PDIE Ilmu Ekonomi</option>
                            <option value="S3- PDIE Akuntansi" {{ old('study_program') == 'S3- PDIE Akuntansi' ? 'selected' : '' }}>S3- PDIE Akuntansi</option>
                            <option value="S3- PDIE Manajemen" {{ old('study_program') == 'S3- PDIE Manajemen' ? 'selected' : '' }}>S3- PDIE Manajemen</option>
                            <option value="Lainnya" {{ old('study_program') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <!-- Purpose -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Keperluan Penggunaan Data <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            @foreach(['Skripsi', 'Thesis', 'Disertasi', 'Lomba', 'Tugas Mata Kuliah', 'Penelitian/Project Dengan Dosen', 'Riset', 'Lainnya'] as $purpose)
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="purpose" value="{{ $purpose }}" class="mr-3 text-blue-600 focus:ring-blue-500" {{ old('purpose') === $purpose ? 'checked' : '' }} required>
                                <span class="text-gray-700">{{ $purpose }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Purpose Other -->
                    <div id="purpose-other-field" class="mb-4 hidden">
                        <label for="purpose_other" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jelaskan Keperluan Lainnya <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="purpose_other" name="purpose_other" value="{{ old('purpose_other') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Jelaskan keperluan Anda">
                    </div>

                    <!-- Lecturer Collaboration -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3" id="collab-label">
                            Apakah Anda bekerja sama dengan dosen? <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 flex-1">
                                <input type="radio" name="has_lecturer_collaboration" value="1" class="mr-3 text-blue-600" {{ old('has_lecturer_collaboration') === '1' ? 'checked' : '' }} required>
                                <span class="text-gray-700">Ya</span>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 flex-1">
                                <input type="radio" name="has_lecturer_collaboration" value="0" class="mr-3 text-blue-600" {{ old('has_lecturer_collaboration') === '0' ? 'checked' : '' }}>
                                <span class="text-gray-700">Tidak</span>
                            </label>
                        </div>
                    </div>

                    <!-- Collaborating Lecturer Name -->
                    <div id="lecturer-name-field" class="mb-6 hidden">
                        <label for="collaborating_lecturer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Dosen <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="collaborating_lecturer_name" name="collaborating_lecturer_name" value="{{ old('collaborating_lecturer_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama dosen pembimbing/kolaborator">
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="nextStep(1)" class="px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                            Selanjutnya
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Pilih Data -->
                <div id="step-2" class="step-content hidden">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-3">2</span>
                        Pilih Data yang Diperlukan
                    </h2>

                    <p class="text-sm text-gray-600 mb-4">
                        Pilih dataset yang Anda perlukan, lalu masukkan kode variabel untuk masing-masing dataset.
                    </p>

                    <!-- Master Data Accordion -->
                    <div class="space-y-4 mb-6" id="master-data-container">
                        @foreach($masterData as $master)
                        <div class="border rounded-xl overflow-hidden">
                            <button type="button" class="w-full px-4 py-3 bg-gray-50 text-left font-semibold text-gray-800 flex items-center justify-between hover:bg-gray-100 transition-colors master-accordion-btn" data-master="{{ $master->id }}">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                                    </svg>
                                    {{ $master->name }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transform transition-transform accordion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div class="master-accordion-content hidden p-4 bg-white border-t">
                                @if($master->activeSubData->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($master->activeSubData as $subData)
                                        <div class="sub-data-item">
                                            <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
                                                <input type="checkbox" name="selected_data[]" value="{{ $subData->id }}" 
                                                    class="dataset-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded"
                                                    data-subdata-id="{{ $subData->id }}"
                                                    data-subdata-name="{{ $subData->name }}"
                                                    {{ in_array($subData->id, old('selected_data', [])) ? 'checked' : '' }}>
                                                <span class="text-gray-700">{{ $subData->name }}</span>
                                            </label>
                                            <!-- Variable input for this dataset -->
                                            <div class="variable-input-container ml-8 mt-2" id="var-container-{{ $subData->id }}">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Kode Variabel untuk {{ $subData->name }}:
                                                </label>
                                                <textarea name="variables[{{ $subData->id }}]" rows="2"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                    placeholder="Contoh: B41K10 B41K7 R1208">{{ old('variables.'.$subData->id) }}</textarea>
                                                <p class="text-xs text-gray-500 mt-1">*Pisahkan kode dengan spasi. Gunakan huruf kapital.</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 italic">Belum ada data tersedia untuk kategori ini</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Selected Data Summary -->
                    <div id="selected-data-summary" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 hidden">
                        <h4 class="font-semibold text-blue-800 mb-2">Data yang Dipilih:</h4>
                        <ul id="selected-data-list" class="text-sm text-blue-700 space-y-1"></ul>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" onclick="prevStep(2)" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Kembali
                        </button>
                        <button type="button" onclick="nextStep(2)" class="px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-colors flex items-center">
                            Selanjutnya
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Upload Dokumen -->
                <div id="step-3" class="step-content hidden">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-3">3</span>
                        Upload Dokumen & Persetujuan
                    </h2>

                    <!-- KTM Upload (for Mahasiswa) -->
                    <div id="ktm-upload-field" class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Upload KTM <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                            <input type="file" id="ktm" name="ktm" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="updateFileLabel('ktm', this)">
                            <label for="ktm" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-600 font-medium">Klik untuk upload KTM</p>
                                <p class="text-sm text-gray-500 mt-1">PDF, JPG, PNG (Maks. 5MB)</p>
                            </label>
                            <p id="ktm-filename" class="text-sm text-blue-600 mt-2 font-medium hidden"></p>
                        </div>
                    </div>

                    <!-- Statement Letter Upload -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Upload Surat Pernyataan Kesanggupan <span class="text-red-500">*</span>
                        </label>
                        <p class="text-sm text-gray-600 mb-3">
                            Download format surat: <a href="https://bit.ly/SuratPernyataanKesanggupanMenjagaInformasi" target="_blank" class="text-blue-600 underline font-semibold hover:text-blue-800">Klik di sini</a>
                        </p>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                            <input type="file" id="statement_letter" name="statement_letter" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="updateFileLabel('statement_letter', this)" required>
                            <label for="statement_letter" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-600 font-medium">Klik untuk upload Surat Pernyataan</p>
                                <p class="text-sm text-gray-500 mt-1">PDF, JPG, PNG (Maks. 5MB)</p>
                            </label>
                            <p id="statement_letter-filename" class="text-sm text-blue-600 mt-2 font-medium hidden"></p>
                        </div>
                    </div>

                    <!-- Agreement -->
                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" name="agreement_accepted" value="1" class="mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded" required>
                            <span class="text-sm text-gray-700">
                                <strong>Dengan ini saya bersedia untuk mematuhi aturan penggunaan data BPS Laboratorium Komputer FEB Universitas Diponegoro.</strong> 
                                Bahwa saya tidak akan memperjualbelikan atau menyebarluaskan data yang saya peroleh dari Laboratorium Komputer FEB Universitas Diponegoro.
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" onclick="prevStep(3)" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Kembali
                        </button>
                        <button type="submit" class="px-8 py-3 bg-green-500 text-white font-bold rounded-lg hover:bg-green-600 transition-colors flex items-center shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ajukan Permohonan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;

        // Helper function to show field errors
        function showFieldError(fieldId, message) {
            const errorEl = document.getElementById(fieldId + '-error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            } else {
                alert(message);
            }
        }

        // Helper function to clear field errors
        function clearFieldError(fieldId) {
            const errorEl = document.getElementById(fieldId + '-error');
            if (errorEl) {
                errorEl.classList.add('hidden');
            }
        }

        // Clear errors on input
        ['nim', 'nip', 'phone', 'email'].forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    clearFieldError(fieldId);
                });
            }
        });

        // Step navigation
        function showStep(step) {
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('step-' + step).classList.remove('hidden');

            // Update indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById('step-indicator-' + i);
                indicator.classList.remove('active', 'completed');
                if (i < step) {
                    indicator.classList.add('completed');
                } else if (i === step) {
                    indicator.classList.add('active');
                }
            }

            currentStep = step;
        }

        function nextStep(fromStep) {
            if (validateStep(fromStep)) {
                showStep(fromStep + 1);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function prevStep(fromStep) {
            showStep(fromStep - 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function validateStep(step) {
            if (step === 1) {
                const applicantType = document.querySelector('input[name="applicant_type"]:checked');
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const purpose = document.querySelector('input[name="purpose"]:checked');
                const hasCollaboration = document.querySelector('input[name="has_lecturer_collaboration"]:checked');

                if (!applicantType) {
                    alert('Pilih status pemohon');
                    return false;
                }
                if (!name) {
                    alert('Nama wajib diisi');
                    return false;
                }
                if (!email) {
                    alert('Email wajib diisi');
                    return false;
                }

                // Validate email format
                const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailPattern.test(email)) {
                    showFieldError('email', 'Format email tidak valid');
                    document.getElementById('email').focus();
                    return false;
                }

                if (!phone) {
                    alert('Nomor WhatsApp wajib diisi');
                    return false;
                }

                // Validate phone format
                if (!/^08[0-9]{8,13}$/.test(phone)) {
                    showFieldError('phone', 'Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit angka');
                    document.getElementById('phone').focus();
                    return false;
                }

                if (applicantType.value === 'mahasiswa') {
                    const nim = document.getElementById('nim').value.trim();
                    const studyProgram = document.getElementById('study_program').value;
                    if (!nim) {
                        showFieldError('nim', 'NIM wajib diisi untuk mahasiswa');
                        document.getElementById('nim').focus();
                        return false;
                    }
                    if (!/^[0-9]{14}$/.test(nim)) {
                        showFieldError('nim', 'NIM harus 14 digit angka');
                        document.getElementById('nim').focus();
                        return false;
                    }
                    if (!studyProgram) {
                        alert('Program studi wajib dipilih untuk mahasiswa');
                        document.getElementById('study_program').focus();
                        return false;
                    }
                } else {
                    const nip = document.getElementById('nip').value.trim();
                    if (!nip) {
                        showFieldError('nip', 'NIP wajib diisi untuk dosen');
                        document.getElementById('nip').focus();
                        return false;
                    }
                    if (!/^[0-9]{18}$/.test(nip)) {
                        showFieldError('nip', 'NIP harus 18 digit angka');
                        document.getElementById('nip').focus();
                        return false;
                    }
                }

                if (!purpose) {
                    alert('Pilih keperluan penggunaan data');
                    return false;
                }

                if (purpose.value === 'Lainnya') {
                    const purposeOther = document.getElementById('purpose_other').value.trim();
                    if (!purposeOther) {
                        alert('Jelaskan keperluan lainnya');
                        return false;
                    }
                }

                if (!hasCollaboration) {
                    alert('Pilih apakah bekerja sama dengan dosen');
                    return false;
                }

                if (hasCollaboration.value === '1') {
                    const lecturerName = document.getElementById('collaborating_lecturer_name').value.trim();
                    if (!lecturerName) {
                        alert('Nama dosen pembimbing wajib diisi');
                        return false;
                    }
                }
            }

            if (step === 2) {
                const selectedData = document.querySelectorAll('input[name="selected_data[]"]:checked');
                if (selectedData.length === 0) {
                    alert('Pilih minimal satu dataset');
                    return false;
                }

                // Check if variables are filled for selected datasets
                let hasVariables = false;
                selectedData.forEach(checkbox => {
                    const subDataId = checkbox.dataset.subdataId;
                    const variableInput = document.querySelector(`textarea[name="variables[${subDataId}]"]`);
                    if (variableInput && variableInput.value.trim()) {
                        hasVariables = true;
                    }
                });

                if (!hasVariables) {
                    alert('Isi kode variabel untuk minimal satu dataset yang dipilih');
                    return false;
                }
            }

            return true;
        }

        // Handle applicant type change
        document.querySelectorAll('input[name="applicant_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isMahasiswa = this.value === 'mahasiswa';
                
                document.getElementById('nim-field').classList.toggle('hidden', !isMahasiswa);
                document.getElementById('nip-field').classList.toggle('hidden', isMahasiswa);
                document.getElementById('study-program-field').classList.toggle('hidden', !isMahasiswa);
                document.getElementById('ktm-upload-field').classList.toggle('hidden', !isMahasiswa);

                // Update collaboration label
                const collabLabel = document.getElementById('collab-label');
                if (isMahasiswa) {
                    collabLabel.innerHTML = 'Apakah Anda bekerja sama dengan dosen? <span class="text-red-500">*</span>';
                } else {
                    collabLabel.innerHTML = 'Apakah Anda bekerja sama dengan dosen lain? <span class="text-red-500">*</span>';
                }

                // Update required attributes
                document.getElementById('nim').required = isMahasiswa;
                document.getElementById('nip').required = !isMahasiswa;
                document.getElementById('study_program').required = isMahasiswa;
                document.getElementById('ktm').required = isMahasiswa;
            });
        });

        // Handle purpose change
        document.querySelectorAll('input[name="purpose"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isOther = this.value === 'Lainnya';
                document.getElementById('purpose-other-field').classList.toggle('hidden', !isOther);
                document.getElementById('purpose_other').required = isOther;
            });
        });

        // Handle lecturer collaboration change
        document.querySelectorAll('input[name="has_lecturer_collaboration"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const hasCollab = this.value === '1';
                document.getElementById('lecturer-name-field').classList.toggle('hidden', !hasCollab);
                document.getElementById('collaborating_lecturer_name').required = hasCollab;
            });
        });

        // Handle accordion
        document.querySelectorAll('.master-accordion-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const arrow = this.querySelector('.accordion-arrow');
                
                content.classList.toggle('hidden');
                arrow.classList.toggle('rotate-180');
            });
        });

        // Handle dataset checkbox
        document.querySelectorAll('.dataset-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const subDataId = this.dataset.subdataId;
                const container = document.getElementById('var-container-' + subDataId);
                
                if (this.checked) {
                    container.classList.add('show');
                } else {
                    container.classList.remove('show');
                }

                updateSelectedSummary();
            });
        });

        function updateSelectedSummary() {
            const selectedCheckboxes = document.querySelectorAll('.dataset-checkbox:checked');
            const summary = document.getElementById('selected-data-summary');
            const list = document.getElementById('selected-data-list');
            
            if (selectedCheckboxes.length > 0) {
                summary.classList.remove('hidden');
                list.innerHTML = '';
                selectedCheckboxes.forEach(cb => {
                    const li = document.createElement('li');
                    li.textContent = '• ' + cb.dataset.subdataName;
                    list.appendChild(li);
                });
            } else {
                summary.classList.add('hidden');
            }
        }

        // File upload label update
        function updateFileLabel(fieldId, input) {
            const filename = document.getElementById(fieldId + '-filename');
            if (input.files && input.files[0]) {
                filename.textContent = '✓ ' + input.files[0].name;
                filename.classList.remove('hidden');
            } else {
                filename.classList.add('hidden');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission handler for final validation
            document.getElementById('bpsForm').addEventListener('submit', function(e) {
                // Validate step 4 before submission
                const isMahasiswa = document.querySelector('input[name="applicant_type"]:checked')?.value === 'mahasiswa';
                
                // Check required file uploads
                const statementLetter = document.getElementById('statement_letter');
                if (!statementLetter.files || statementLetter.files.length === 0) {
                    e.preventDefault();
                    alert('Silakan upload Surat Pernyataan Kesanggupan');
                    return false;
                }
                
                // KTM is required for mahasiswa
                if (isMahasiswa) {
                    const ktm = document.getElementById('ktm');
                    if (!ktm.files || ktm.files.length === 0) {
                        e.preventDefault();
                        alert('Silakan upload KTM untuk mahasiswa');
                        return false;
                    }
                }
                
                // Check agreement checkbox
                const agreement = document.querySelector('input[name="agreement_accepted"]');
                if (!agreement.checked) {
                    e.preventDefault();
                    alert('Anda harus menyetujui pernyataan kesanggupan menjaga informasi');
                    return false;
                }
                
                // Disable submit button to prevent double submission
                const submitBtn = e.target.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...';
                }
            });

            // Trigger change events for pre-filled values
            const checkedApplicant = document.querySelector('input[name="applicant_type"]:checked');
            if (checkedApplicant) {
                checkedApplicant.dispatchEvent(new Event('change'));
            }

            const checkedPurpose = document.querySelector('input[name="purpose"]:checked');
            if (checkedPurpose) {
                checkedPurpose.dispatchEvent(new Event('change'));
            }

            const checkedCollab = document.querySelector('input[name="has_lecturer_collaboration"]:checked');
            if (checkedCollab) {
                checkedCollab.dispatchEvent(new Event('change'));
            }

            // Show variable containers for pre-checked datasets
            document.querySelectorAll('.dataset-checkbox:checked').forEach(cb => {
                const subDataId = cb.dataset.subdataId;
                document.getElementById('var-container-' + subDataId).classList.add('show');
            });

            updateSelectedSummary();
        });
    </script>
</body>
</html>
