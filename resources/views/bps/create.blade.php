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
        .dataset-checkbox:checked + label,
        .master-checkbox:checked + label {
            background-color: #dbeafe;
            border-color: #3b82f6;
        }
        .sub-data-item {
            transition: all 0.2s ease;
        }
        .sub-data-item:hover {
            background-color: #f0f9ff;
        }
        .variable-input-container,
        .master-variable-container {
            display: none;
        }
        .variable-input-container.show,
        .master-variable-container.show {
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
                <a href="{{ route('data.index') }}" class="px-4 py-2 text-gray-600 hover:text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-all duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
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

        <!-- Tata Tertib Box -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 md:p-6 mb-4">
            <h3 class="font-bold text-red-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                TATA TERTIB PENGGUNAAN DATA BPS FEB Universitas Diponegoro
            </h3>
            <p class="text-sm text-red-700 font-medium">
                Tidak memperjualbelikan atau menyebarluaskan data yang telah diberikan tanpa ijin FEB-UNDIP
            </p>
        </div>

        <!-- Mekanisme Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 md:p-6 mb-4">
            <h3 class="font-bold text-blue-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                MEKANISME
            </h3>
            <ol class="text-sm text-blue-700 space-y-2 list-decimal list-inside">
                <li>Pemohon mengisi formulir permohonan akses penggunaan data</li>
                <li>Pemohon memilih jenis data dan kode variabel yang diperlukan</li>
                <li>Pihak Laboratorium Komputer FEB Universitas Diponegoro akan memproses permohonan</li>
                <li>Data akan dikirimkan dalam bentuk link G-Drive oleh pihak Laboratorium Komputer FEB-UNDIP kepada pemohon melalui surel pemohon dalam 1 - 3 hari kerja</li>
                <li>Pengguna harap segera mengunduh data, setelah 3 hari data akan kami hapus dari link G-Drive</li>
                <li>Pengguna membaca dan memahami master data (keterangan data), kuesioner data yang telah disediakan pada <a href="https://bit.ly/LegendaDataBPS" target="_blank" class="underline font-semibold hover:text-blue-800">https://bit.ly/LegendaDataBPS</a> untuk memudahkan dalam interpretasi data, pastikan scroll ke kanan atau bawah untuk memastikan master data telah terlihat secara menyeluruh</li>
            </ol>
        </div>

        <!-- Kode Data & Format Surat Box -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Kode Data BPS -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 md:p-6">
                <h3 class="font-bold text-green-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    KODE DATA BPS
                </h3>
                <p class="text-sm text-green-700 mb-3">
                    Untuk mempermudah proses filtering data, silahkan mengunduh legenda data dan memilih kode variabel yang diperlukan pada link berikut:
                </p>
                <a href="https://bit.ly/LegendaDataBPS" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Legenda Data BPS
                </a>
            </div>

            <!-- Format Surat -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 md:p-6">
                <h3 class="font-bold text-yellow-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                    SURAT PERNYATAAN KESANGGUPAN
                </h3>
                <p class="text-sm text-yellow-700 mb-3">
                    Format surat dapat diunduh pada link berikut:
                </p>
                <a href="https://bit.ly/SuratPernyataanKesanggupanMenjagaInformasi" target="_blank" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-lg hover:bg-yellow-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Surat Pernyataan Kesanggupan
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-8">
            <!-- Error Messages -->
            @if ($errors->any())
                <div id="error-box" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
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
                <script>
                    // Scroll to error box on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        const errorBox = document.getElementById('error-box');
                        if (errorBox) {
                            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                </script>
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
                        <p id="applicant_type-error" class="text-xs text-red-500 mt-1 hidden text-center"></p>
                    </div>

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama lengkap">
                        <p id="name-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Alamat Surel (Email) <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
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
                            pattern="08[0-9]{8,13}"
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
                        <p id="study_program-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- Study Program Other (for Lainnya) -->
                    <div id="study-program-other-field" class="mb-4 hidden">
                        <label for="study_program_other" class="block text-sm font-semibold text-gray-700 mb-2">
                            Program Studi Lainnya <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="study_program_other" name="study_program_other" value="{{ old('study_program_other') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Tuliskan program studi Anda">
                        <p id="study_program_other-error" class="text-xs text-red-500 mt-1 hidden"></p>
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
                        <p id="purpose-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- Purpose Other -->
                    <div id="purpose-other-field" class="mb-4 hidden">
                        <label for="purpose_other" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jelaskan Keperluan Lainnya <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="purpose_other" name="purpose_other" value="{{ old('purpose_other') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Jelaskan keperluan Anda">
                        <p id="purpose_other-error" class="text-xs text-red-500 mt-1 hidden"></p>
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
                        <p id="has_lecturer_collaboration-error" class="text-xs text-red-500 mt-1 hidden"></p>
                    </div>

                    <!-- Collaborating Lecturer Name -->
                    <div id="lecturer-name-field" class="mb-6 hidden">
                        <label for="collaborating_lecturer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nama Dosen <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="collaborating_lecturer_name" name="collaborating_lecturer_name" value="{{ old('collaborating_lecturer_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama dosen pembimbing/kolaborator">
                        <p id="collaborating_lecturer_name-error" class="text-xs text-red-500 mt-1 hidden"></p>
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
                    
                    <div id="step-2-error" class="hidden mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-start">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span id="step-2-error-text"></span>
                    </div>

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
                                @if($master->has_sub_data)
                                    {{-- Master data with sub-data: show sub-data checkboxes --}}
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
                                @else
                                    {{-- Single-level master data: direct variable input --}}
                                    <div class="sub-data-item">
                                        <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">
                                            <input type="checkbox" name="selected_master[]" value="{{ $master->id }}" 
                                                class="master-checkbox mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded"
                                                data-master-id="{{ $master->id }}"
                                                data-master-name="{{ $master->name }}"
                                                {{ in_array($master->id, old('selected_master', [])) ? 'checked' : '' }}>
                                            <span class="text-gray-700">Pilih {{ $master->name }}</span>
                                        </label>
                                        <!-- Variable input for this master data -->
                                        <div class="master-variable-container ml-8 mt-2" id="master-var-container-{{ $master->id }}">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Kode Variabel untuk {{ $master->name }}:
                                            </label>
                                            <textarea name="master_variables[{{ $master->id }}]" rows="2"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                placeholder="Contoh: B41K10 B41K7 R1208">{{ old('master_variables.'.$master->id) }}</textarea>
                                            <p class="text-xs text-gray-500 mt-1">*Pisahkan kode dengan spasi. Gunakan huruf kapital.</p>
                                        </div>
                                    </div>
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
                            <input type="file" id="ktm" name="ktm" accept=".pdf" class="hidden" onchange="updateFileLabel('ktm', this)">
                            <label for="ktm" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-600 font-medium">Klik untuk upload KTM</p>
                                <p class="text-sm text-gray-500 mt-1">PDF (Maks. 5MB)</p>
                            </label>
                            <p id="ktm-filename" class="text-sm text-blue-600 mt-2 font-medium hidden"></p>
                        </div>
                        <p id="ktm-error" class="text-xs text-red-500 mt-1 hidden"></p>
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
                            <input type="file" id="statement_letter" name="statement_letter" accept=".pdf" class="hidden" onchange="updateFileLabel('statement_letter', this)" required>
                            <label for="statement_letter" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-600 font-medium">Klik untuk upload Surat Pernyataan</p>
                                <p class="text-sm text-gray-500 mt-1">PDF (Maks. 5MB)</p>
                            </label>
                            <p id="statement_letter-filename" class="text-sm text-blue-600 mt-2 font-medium hidden"></p>
                        </div>
                        <p id="statement_letter-error" class="text-xs text-red-500 mt-1 hidden"></p>
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
                        <p id="agreement_accepted-error" class="text-xs text-red-500 mt-1 hidden ml-7"></p>
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
                errorEl.innerHTML = '<strong>* ' + message + '</strong>';
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
        ['nim', 'nip', 'phone', 'email', 'name', 'study_program_other', 'purpose_other', 'collaborating_lecturer_name'].forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    clearFieldError(fieldId);
                });
            }
        });

        // Clear radio/select errors
        ['applicant_type', 'purpose', 'has_lecturer_collaboration'].forEach(name => {
            document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                input.addEventListener('change', () => clearFieldError(name));
            });
        });

        ['study_program'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => clearFieldError(id));
        });
        
        // Clear checkbox errors
        document.querySelector('input[name="agreement_accepted"]')?.addEventListener('change', () => clearFieldError('agreement_accepted'));
        document.getElementById('ktm')?.addEventListener('change', () => clearFieldError('ktm'));
        document.getElementById('statement_letter')?.addEventListener('change', () => clearFieldError('statement_letter'));

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
            let isValid = true;
            
            if (step === 1) {
                const applicantType = document.querySelector('input[name="applicant_type"]:checked');
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const purpose = document.querySelector('input[name="purpose"]:checked');
                const hasCollaboration = document.querySelector('input[name="has_lecturer_collaboration"]:checked');

                if (!applicantType) {
                    showFieldError('applicant_type', 'Pilih status pemohon');
                    isValid = false;
                }
                if (!name) {
                    showFieldError('name', 'Nama wajib diisi');
                    isValid = false;
                }
                if (!email) {
                    showFieldError('email', 'Email wajib diisi');
                    isValid = false;
                } else {
                     // Validate email format
                    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    if (!emailPattern.test(email)) {
                        showFieldError('email', 'Format email tidak valid');
                        isValid = false;
                    }
                }

                if (!phone) {
                     showFieldError('phone', 'Nomor WhatsApp wajib diisi');
                     isValid = false;
                } else {
                    // Validate phone format
                    if (!/^08[0-9]{8,13}$/.test(phone)) {
                        showFieldError('phone', 'Nomor WhatsApp harus diawali 08 dan berisi 10-15 digit angka');
                        isValid = false;
                    }
                }

                if (applicantType && applicantType.value === 'mahasiswa') {
                    const nim = document.getElementById('nim').value.trim();
                    const studyProgram = document.getElementById('study_program').value;
                    if (!nim) {
                        showFieldError('nim', 'NIM wajib diisi untuk mahasiswa');
                        isValid = false;
                    } else if (!/^[0-9]{14}$/.test(nim)) {
                        showFieldError('nim', 'NIM harus 14 digit angka');
                        isValid = false;
                    }
                    if (!studyProgram) {
                        showFieldError('study_program', 'Program studi wajib dipilih untuk mahasiswa');
                        isValid = false;
                    } else if (studyProgram === 'Lainnya') {
                         const spOther = document.getElementById('study_program_other').value.trim();
                         if (!spOther) {
                             showFieldError('study_program_other', 'Tuliskan program studi Anda');
                             isValid = false;
                         }
                    }
                } else if (applicantType && applicantType.value === 'dosen') {
                    const nip = document.getElementById('nip').value.trim();
                    if (!nip) {
                        showFieldError('nip', 'NIP wajib diisi untuk dosen');
                        isValid = false;
                    } else if (!/^[0-9]{18}$/.test(nip)) {
                        showFieldError('nip', 'NIP harus 18 digit angka');
                        isValid = false;
                    }
                }

                if (!purpose) {
                    showFieldError('purpose', 'Pilih keperluan penggunaan data');
                    isValid = false;
                } else if (purpose.value === 'Lainnya') {
                    const purposeOther = document.getElementById('purpose_other').value.trim();
                    if (!purposeOther) {
                        showFieldError('purpose_other', 'Jelaskan keperluan lainnya');
                        isValid = false;
                    }
                }

                if (!hasCollaboration) {
                    showFieldError('has_lecturer_collaboration', 'Pilih apakah bekerja sama dengan dosen');
                    isValid = false;
                } else if (hasCollaboration.value === '1') {
                    const lecturerName = document.getElementById('collaborating_lecturer_name').value.trim();
                    if (!lecturerName) {
                         showFieldError('collaborating_lecturer_name', 'Nama dosen pembimbing wajib diisi');
                         isValid = false;
                    }
                }
            }

            if (step === 2) {
                const selectedSubData = document.querySelectorAll('input[name="selected_data[]"]:checked');
                const selectedMaster = document.querySelectorAll('input[name="selected_master[]"]:checked');
                const errorBox = document.getElementById('step-2-error');
                const errorText = document.getElementById('step-2-error-text');
                
                // Reset error
                errorBox.classList.add('hidden');

                if (selectedSubData.length === 0 && selectedMaster.length === 0) {
                    errorText.textContent = 'Pilih minimal satu dataset';
                    errorBox.classList.remove('hidden');
                    return false;
                }

                // Check if variables are filled for selected sub-data
                let hasVariables = false;
                selectedSubData.forEach(checkbox => {
                    const subDataId = checkbox.dataset.subdataId;
                    const variableInput = document.querySelector(`textarea[name="variables[${subDataId}]"]`);
                    if (variableInput && variableInput.value.trim()) {
                        hasVariables = true;
                    }
                });

                // Check if variables are filled for selected single-level master data
                selectedMaster.forEach(checkbox => {
                    const masterId = checkbox.dataset.masterId;
                    const variableInput = document.querySelector(`textarea[name="master_variables[${masterId}]"]`);
                    if (variableInput && variableInput.value.trim()) {
                        hasVariables = true;
                    }
                });

                if (!hasVariables) {
                    errorText.textContent = 'Isi kode variabel untuk minimal satu dataset yang dipilih';
                    errorBox.classList.remove('hidden');
                    return false;
                }
            }

            if (!isValid) {
                 // Scroll to first error?
                 const firstError = document.querySelector('.text-red-500:not(.hidden)');
                 if (firstError) {
                     firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                 }
            }

            return isValid;
        }

        // Handle applicant type change
        document.querySelectorAll('input[name="applicant_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isMahasiswa = this.value === 'mahasiswa';
                
                document.getElementById('nim-field').classList.toggle('hidden', !isMahasiswa);
                document.getElementById('nip-field').classList.toggle('hidden', isMahasiswa);
                document.getElementById('study-program-field').classList.toggle('hidden', !isMahasiswa);
                document.getElementById('study-program-other-field').classList.toggle('hidden', !isMahasiswa || document.getElementById('study_program').value !== 'Lainnya');
                document.getElementById('ktm-upload-field').classList.toggle('hidden', !isMahasiswa);

                // Update study program other field when applicant type changes
                if (!isMahasiswa) {
                    document.getElementById('study_program_other').required = false;
                }

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

        // Handle study program change
        document.getElementById('study_program').addEventListener('change', function() {
            const isOther = this.value === 'Lainnya';
            document.getElementById('study-program-other-field').classList.toggle('hidden', !isOther);
            document.getElementById('study_program_other').required = isOther;
        });

        // Initialize study program other visibility on page load
        (function() {
            const studyProgramSelect = document.getElementById('study_program');
            const isOther = studyProgramSelect.value === 'Lainnya';
            document.getElementById('study-program-other-field').classList.toggle('hidden', !isOther);
            document.getElementById('study_program_other').required = isOther;
        })();

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

        // Handle dataset checkbox (sub-data)
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
                // Clear step 2 error if any data selected
                const errorBox = document.getElementById('step-2-error');
                if (!errorBox.classList.contains('hidden')) {
                     errorBox.classList.add('hidden');
                }
            });
        });

        // Handle master checkbox (single-level data)
        document.querySelectorAll('.master-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const masterId = this.dataset.masterId;
                const container = document.getElementById('master-var-container-' + masterId);
                
                if (this.checked) {
                    container.classList.add('show');
                } else {
                    container.classList.remove('show');
                }

                updateSelectedSummary();
                 // Clear step 2 error if any data selected
                const errorBox = document.getElementById('step-2-error');
                if (!errorBox.classList.contains('hidden')) {
                     errorBox.classList.add('hidden');
                }
            });
        });

        function updateSelectedSummary() {
            const selectedSubData = document.querySelectorAll('.dataset-checkbox:checked');
            const selectedMaster = document.querySelectorAll('.master-checkbox:checked');
            const summary = document.getElementById('selected-data-summary');
            const list = document.getElementById('selected-data-list');
            
            if (selectedSubData.length > 0 || selectedMaster.length > 0) {
                summary.classList.remove('hidden');
                list.innerHTML = '';
                
                // Add sub-data items
                selectedSubData.forEach(cb => {
                    const li = document.createElement('li');
                    li.textContent = '• ' + cb.dataset.subdataName;
                    list.appendChild(li);
                });
                
                // Add single-level master items
                selectedMaster.forEach(cb => {
                    const li = document.createElement('li');
                    li.textContent = '• ' + cb.dataset.masterName;
                    list.appendChild(li);
                });
            } else {
                summary.classList.add('hidden');
            }
        }

        // File upload label update with instant validation
        function updateFileLabel(fieldId, input) {
            const filename = document.getElementById(fieldId + '-filename');
            const uploadBox = input.closest('.border-dashed');
            
            // Remove previous validation error
            const existingError = uploadBox.parentElement.querySelector('.file-validation-error');
            if (existingError) existingError.remove();
            uploadBox.classList.remove('border-red-400', 'bg-red-50', 'border-green-400', 'bg-green-50');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedExt = file.name.toLowerCase().endsWith('.pdf');
                const allowedType = file.type === 'application/pdf';
                let errorMsg = '';
                
                if (!allowedType && !allowedExt) {
                    errorMsg = '⚠️ Format file harus PDF. File yang dipilih: ' + file.name.split('.').pop().toUpperCase();
                } else if (file.size > maxSize) {
                    errorMsg = '⚠️ Ukuran file maksimal 5MB. File yang dipilih: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
                }
                
                if (errorMsg) {
                    // Show custom error modal
                    showFileErrorModal(errorMsg);

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'file-validation-error mt-2 bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg text-sm flex items-center';
                    errorDiv.innerHTML = '<svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>' + errorMsg;
                    uploadBox.parentElement.appendChild(errorDiv);
                    uploadBox.classList.add('border-red-400', 'bg-red-50');
                    
                    input.value = '';
                    filename.classList.add('hidden');
                    return;
                }
                
                const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                filename.textContent = '✓ ' + file.name + ' (' + fileSizeMB + ' MB)';
                filename.classList.remove('hidden');
                uploadBox.classList.add('border-green-400', 'bg-green-50');
                clearFieldError(fieldId);
            } else {
                filename.classList.add('hidden');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission handler for final validation
            document.getElementById('bpsForm').addEventListener('submit', function(e) {
                // Validate step 3 before submission
                const isMahasiswa = document.querySelector('input[name="applicant_type"]:checked')?.value === 'mahasiswa';
                let isValid = true;
                
                // Check required file uploads
                const statementLetter = document.getElementById('statement_letter');
                if (!statementLetter.files || statementLetter.files.length === 0) {
                    showFieldError('statement_letter', 'Silakan upload Surat Pernyataan Kesanggupan');
                    isValid = false;
                }
                
                // KTM is required for mahasiswa
                if (isMahasiswa) {
                    const ktm = document.getElementById('ktm');
                    if (!ktm.files || ktm.files.length === 0) {
                        showFieldError('ktm', 'Silakan upload KTM untuk mahasiswa');
                        isValid = false;
                    }
                }
                
                // Check agreement checkbox
                const agreement = document.querySelector('input[name="agreement_accepted"]');
                if (!agreement.checked) {
                     showFieldError('agreement_accepted', 'Anda harus menyetujui pernyataan kesanggupan menjaga informasi');
                     isValid = false;
                }
                
                if (!isValid) {
                     e.preventDefault();
                     return false;
                }
                
                // Disable submit button to prevent double submission
                const submitBtn = e.target.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...';
                }
            });

            // Determine initial step based on validation errors
            @if($errors->any())
                // Check which step has errors
                const step1Fields = ['applicant_type', 'name', 'email', 'phone', 'nim', 'nip', 'study_program', 'study_program_other', 'purpose', 'purpose_other', 'has_lecturer_collaboration', 'collaborating_lecturer_name'];
                const step2Fields = ['selected_data', 'selected_master', 'variables', 'master_variables'];
                const step3Fields = ['ktm', 'statement_letter', 'agreement_accepted'];
                
                const errors = @json($errors->keys());
                let targetStep = 1;
                
                for (const field of errors) {
                    if (step3Fields.some(f => field.startsWith(f))) {
                        targetStep = Math.max(targetStep, 3);
                    } else if (step2Fields.some(f => field.startsWith(f))) {
                        targetStep = Math.max(targetStep, 2);
                    }
                }
                
                if (targetStep > 1) {
                    showStep(targetStep);
                }
            @endif


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

            // Show variable containers for pre-checked single-level master data
            document.querySelectorAll('.master-checkbox:checked').forEach(cb => {
                const masterId = cb.dataset.masterId;
                const container = document.getElementById('master-var-container-' + masterId);
                if (container) container.classList.add('show');
            });

            updateSelectedSummary();
        });
    </script>

@include('components.file-error-modal')
</body>
</html>
