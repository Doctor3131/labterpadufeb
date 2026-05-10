<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Peminjaman - Laboratorium dan Fasilitas Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .step-disabled {
            opacity: 0.5;
            pointer-events: none;
        }
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
        /* Mobile Progress Line */
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px; /* Center of the 40px circle */
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }
        .step-item.completed::after {
            background: #22c55e;
        }
        /* Active/Completed States */
        .step-item.active .step-number {
            background: #eab308;
            color: white;
            border-color: #eab308;
        }
        .step-item.completed .step-number {
            background: #22c55e;
            color: white;
            border-color: #22c55e;
        }
        .step-number {
            width: 32px;
            height: 32px;
            md:width: 40px;
            md:height: 40px;
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
        @media (min-width: 768px) {
            .step-number {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
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
                <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 md:px-6 py-2 rounded-lg font-bold transition-all shadow-sm hover:shadow-md text-sm md:text-base whitespace-nowrap">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 md:px-6 py-4 md:py-12 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-6 md:mb-10">
            <h1 class="text-2xl md:text-4xl font-bold text-gray-800 mb-2 md:mb-3">Ajukan Peminjaman Lab</h1>
            <p class="text-sm md:text-base text-gray-600 px-4">Lengkapi formulir untuk mengajukan peminjaman laboratorium</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-8">
            <!-- Time Conflict Warning (More Prominent) -->
            @if ($errors->has('time_conflict'))
                <div class="mb-6 bg-red-100 border-2 border-red-500 rounded-lg p-5">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-red-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-red-900 font-bold text-lg mb-2">⚠️ Konflik Jadwal!</h3>
                            <p class="text-red-800 font-semibold">{{ $errors->first('time_conflict') }}</p>
                            <p class="text-red-700 text-sm mt-2">💡 Sepertinya baru saja ada peminjaman lain yang mengajukan di waktu dan lab yang sama, sehingga terjadi bentrok. Silakan pilih waktu atau ruangan lain.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any() && !$errors->has('time_conflict'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-red-800 font-semibold">Terdapat kesalahan:</h3>
                    </div>
                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            @if (!str_contains($error, 'tidak tersedia pada waktu'))
                                <li>{{ $error }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Step Indicator -->
            <div class="step-indicator mb-6 md:mb-8 px-2 md:px-0">
                <div class="step-item active" id="step-indicator-1">
                    <div class="step-number">1</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Tipe</div>
                    <div class="text-xs font-medium md:hidden">1</div>
                </div>
                <div class="step-item" id="step-indicator-2">
                    <div class="step-number">2</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Data Diri</div>
                    <div class="text-xs font-medium md:hidden">2</div>
                </div>
                <div class="step-item" id="step-indicator-3">
                    <div class="step-number">3</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Detail</div>
                    <div class="text-xs font-medium md:hidden">3</div>
                </div>
                <div class="step-item" id="step-indicator-4">
                    <div class="step-number">4</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Submit</div>
                    <div class="text-xs font-medium md:hidden">4</div>
                </div>
            </div>

            <form action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data" id="bookingForm">
                @csrf

                <!-- STEP 1: Tipe Peminjaman -->
                <div id="step-1" class="step-section">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">1</span>
                        Pilih Tipe Peminjaman
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="perkuliahan_tetap" class="hidden peer" required {{ old('booking_type') == 'perkuliahan_tetap' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Perkuliahan Tetap</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Jadwal rutin</div>
                            </div>
                        </label>
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="perkuliahan_tidak_tetap" class="hidden peer" required {{ old('booking_type') == 'perkuliahan_tidak_tetap' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Perkuliahan Tidak Tetap</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Sekali waktu</div>
                            </div>
                        </label>
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="non_perkuliahan" class="hidden peer" required {{ old('booking_type') == 'non_perkuliahan' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Non-Perkuliahan</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Kegiatan lain (Ormawa, Pelatihan, etc)</div>
                            </div>
                        </label>
                         <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="pribadi" class="hidden peer" required {{ old('booking_type') == 'pribadi' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Pribadi</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Keperluan Pribadi</div>
                            </div>
                        </label>
                    </div>

                    <div id="unit-selection" class="mb-8">
                        <label class="block text-gray-700 font-bold mb-4 text-lg">Unit</label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-yellow-50 transition-colors group">
                                <input type="radio" name="unit_type" value="s1_tembalang" class="w-5 h-5 text-yellow-600 focus:ring-yellow-500" required {{ old('unit_type') == 's1_tembalang' ? 'checked' : '' }}>
                                <span class="ml-3 text-gray-700 font-medium group-hover:text-gray-900">S1 Tembalang</span>
                            </label>
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-yellow-50 transition-colors group">
                                <input type="radio" name="unit_type" value="pascasarjana_pleburan" class="w-5 h-5 text-yellow-600 focus:ring-yellow-500" required {{ old('unit_type') == 'pascasarjana_pleburan' ? 'checked' : '' }}>
                                <span class="ml-3 text-gray-700 font-medium group-hover:text-gray-900">Pascasarjana Pleburan</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="btn-next-1" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base w-full md:w-auto" disabled>
                            Lanjut →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Data Pribadi -->
                <div id="step-2" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">2</span>
                        Data Pribadi
                    </h3>

                    <!-- Status Fields for Pribadi (shown first for pribadi booking) -->
                    <div id="pribadi-status-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Pilih Status Terlebih Dahulu</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Status <span class="text-red-500">*</span></label>
                                <select name="applicant_status" id="applicant_status"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="">Pilih Status</option>
                                    <option value="Mahasiswa" {{ old('applicant_status') == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                    <option value="Dosen" {{ old('applicant_status') == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                                    <option value="Pegawai" {{ old('applicant_status') == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                    <option value="Lainnya" {{ old('applicant_status') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
                            <div id="custom-status-field" style="display: none;">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Status Lainnya <span class="text-red-500">*</span></label>
                                <input type="text" data-name="custom_status" id="custom_status" value="{{ old('custom_status') }}"
                                    placeholder="Masukkan status Anda"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="pic_name" id="pic_name" value="{{ old('pic_name') }}" required
                                pattern="[a-zA-Z\s\.']+"
                                title="Hanya huruf, spasi, titik, dan apostrof yang diperbolehkan"
                                oninput="this.value = this.value.replace(/[^a-zA-Z\s\.']/g, '')"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Masukkan nama lengkap (huruf saja)</p>
                            <p id="pic_name-error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <div id="study-program-field">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Program Studi <span class="text-red-500">*</span></label>
                            <select name="study_program" id="study_program" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="" disabled selected>Pilih Program Studi</option>

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

                        <div id="custom-study-program-field" style="display: none;">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Program Studi Lainnya <span class="text-red-500">*</span></label>
                            <input type="text" id="custom_study_program" value="{{ old('custom_study_program') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div id="nim-field">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">NIM <span class="text-red-500">*</span> <span class="text-xs text-gray-500">(14 digit)</span></label>
                            <input type="text" name="nim" id="nim" value="{{ old('nim') }}" required
                                maxlength="14" pattern="[0-9]{14}"
                                placeholder="Contoh: 12010120130001"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">NIM harus 14 digit angka</p>
                            <p id="nim-error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <div id="nip-field" style="display: none;">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">NIP <span class="text-red-500">*</span> <span class="text-xs text-gray-500">(18 digit)</span></label>
                            <input type="text" data-name="nip" id="nip" value="{{ old('nip') }}"
                                maxlength="18" pattern="[0-9]{18}"
                                placeholder="Contoh: 198505102010121001"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">NIP harus 18 digit angka</p>
                            <p id="nip-error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor Telepon <span class="text-red-500">*</span> <span class="text-xs text-gray-500">(10-15 digit)</span></label>
                            <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" required
                                minlength="10" maxlength="15" pattern="^08[0-9]{8,13}$"
                                placeholder="Contoh: 081234567890"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Nomor harus diawali 08 dan 10-15 digit</p>
                            <p id="phone_number-error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>
                    </div>

                    <!-- Dynamic Fields Based on Booking Type -->
                    <div id="perkuliahan-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Data Perkuliahan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Mata Kuliah <span class="text-red-500">*</span></label>
                                <input type="text" name="course_name" id="course_name" value="{{ old('course_name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Dosen Pengampu <span class="text-red-500">*</span></label>
                                <input type="text" name="lecturer_name" id="lecturer_name" value="{{ old('lecturer_name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">NIP Dosen <span class="text-red-500">*</span></label>
                                <input type="text" name="lecturer_nip" id="lecturer_nip" value="{{ old('lecturer_nip') }}"
                                    maxlength="18" pattern="[0-9]{18}"
                                    placeholder="18 digit angka"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Software yang Digunakan</label>
                                <input type="text" name="software_needs" id="software_needs" value="{{ old('software_needs') }}"
                                    placeholder="Contoh: SPSS, Microsoft Office, dll"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div id="non-perkuliahan-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Data Kegiatan Non-Perkuliahan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Kegiatan <span class="text-red-500">*</span></label>
                                <input type="text" name="activity_name" id="activity_name" value="{{ old('activity_name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            {{-- Bimbingan Dosen Checkbox --}}
                            <div class="md:col-span-2">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-colors group {{ old('is_bimbingan_dosen') ? 'bg-blue-50 border-blue-400' : '' }}">
                                    <input type="hidden" name="is_bimbingan_dosen" value="0">
                                    <input type="checkbox" name="is_bimbingan_dosen" id="is_bimbingan_dosen" value="1"
                                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300"
                                        {{ old('is_bimbingan_dosen') ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <span class="text-gray-800 font-semibold group-hover:text-blue-700">Bimbingan bersama Dosen?</span>
                                        <p class="text-xs text-gray-500 mt-0.5">Centang jika kegiatan ini merupakan bimbingan skripsi atau kegiatan bersama dosen</p>
                                    </div>
                                </label>
                            </div>

                            {{-- Fields yang tampil jika BUKAN bimbingan (default) --}}
                            <div id="non-bimbingan-fields" class="md:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-semibold mb-2">Jenis Kegiatan <span class="text-red-500">*</span></label>
                                        <select name="activity_type" id="activity_type"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                            <option value="">-- Pilih Jenis Kegiatan --</option>
                                            <option value="Seminar" {{ old('activity_type') == 'Seminar' ? 'selected' : '' }}>Seminar</option>
                                            <option value="Workshop" {{ old('activity_type') == 'Workshop' ? 'selected' : '' }}>Workshop</option>
                                            <option value="Pelatihan" {{ old('activity_type') == 'Pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                            <option value="Rapat" {{ old('activity_type') == 'Rapat' ? 'selected' : '' }}>Rapat</option>
                                            <option value="Ujian" {{ old('activity_type') == 'Ujian' ? 'selected' : '' }}>Ujian</option>
                                            <option value="Lainnya" {{ old('activity_type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-semibold mb-2">Posisi Peminjam <span class="text-red-500">*</span></label>
                                        <input type="text" name="position" id="position" value="{{ old('position') }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            {{-- Fields yang tampil jika bimbingan dosen --}}
                            <div id="bimbingan-dosen-fields" class="hidden md:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Dosen <span class="text-red-500">*</span></label>
                                        <input type="text" id="bimbingan_lecturer_name" value="{{ old('lecturer_name') }}"
                                            placeholder="Nama lengkap dosen pembimbing"
                                            oninput="this.value = this.value.replace(/[^a-zA-Z\s\.']/g, '')"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-sm font-semibold mb-2">NIP Dosen <span class="text-red-500">*</span></label>
                                        <input type="text" id="bimbingan_lecturer_nip" value="{{ old('lecturer_nip') }}"
                                            maxlength="18" pattern="[0-9]{18}"
                                            placeholder="18 digit angka"
                                            inputmode="numeric"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Kebutuhan Peralatan</label>
                                <textarea name="equipment_needs" id="equipment_needs" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('equipment_needs') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div id="pribadi-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Data Peminjaman Pribadi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div id="class-year-field" style="display: none;">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Angkatan <span class="text-red-500">*</span></label>
                                <input type="text" data-name="class_year" id="class_year" value="{{ old('class_year') }}"
                                    placeholder="Contoh: 2023" maxlength="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Keperluan <span class="text-red-500">*</span></label>
                                <input type="text" name="purpose" id="purpose" value="{{ old('purpose') }}"
                                    placeholder="Contoh: Ujian, Mengerjakan tugas pribadi"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse md:flex-row justify-between gap-3 md:gap-0 mt-8">
                        <button type="button" id="btn-prev-2" class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors text-sm md:text-base">
                            ← Kembali
                        </button>
                        <button type="button" id="btn-next-2" class="w-full md:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base" disabled>
                            Lanjut →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Detail Peminjaman -->
                <div id="step-3" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">3</span>
                        Detail Peminjaman Laboratorium
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Peminjaman <span class="text-red-500">*</span></label>
                            <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date') }}" required
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">⚠️ Peminjaman tidak tersedia pada hari Minggu</p>
                            <!-- Sunday Warning -->
                            <div id="sunday-warning" class="hidden mt-2 bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-sm">
                                ⚠️ Hari Minggu tidak tersedia untuk peminjaman lab. Silakan pilih tanggal lain.
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Jumlah Peserta <span class="text-red-500">*</span></label>
                            <input type="number" name="participant_count" id="participant_count" value="{{ old('participant_count') }}" required
                                min="1" placeholder="Contoh: 30"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="hidden" name="start_time" id="start_time" value="{{ old('start_time') }}" required>
                            <div class="flex gap-2">
                                <select id="start_hour" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                    <option value="" disabled selected>Jam</option>
                                    @foreach(range(0, 23) as $h)
                                        <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                    @endforeach
                                </select>
                                <span class="self-center font-bold text-gray-400">:</span>
                                <select id="start_minute" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                    <option value="" disabled selected>Menit</option>
                                    @foreach(range(0, 55, 5) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="hidden" name="end_time" id="end_time" value="{{ old('end_time') }}" required>
                            <div class="flex gap-2">
                                <select id="end_hour" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                    <option value="" disabled selected>Jam</option>
                                    @foreach(range(0, 23) as $h)
                                        <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                    @endforeach
                                </select>
                                <span class="self-center font-bold text-gray-400">:</span>
                                <select id="end_minute" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                    <option value="" disabled selected>Menit</option>
                                    @foreach(range(0, 55, 5) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Time Validation Error -->
                        <div class="md:col-span-2">
                            <p id="time-error" class="text-sm text-red-500 hidden"><strong>* Jam Selesai harus setelah Jam Mulai</strong></p>
                        </div>

                        <!-- Lab Selection Container - Hidden for pribadi bookings -->
                        <div id="lab-selection-container" class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Pilih Laboratorium <span class="text-red-500">*</span></label>
                            <select name="lab_id" id="labSelect" required disabled
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent disabled:bg-gray-100">
                                <option value="">Isi data di atas untuk melihat lab yang tersedia</option>
                            </select>
                            <p class="text-sm text-gray-500 mt-2">
                                Lab akan muncul setelah Anda mengisi tanggal, waktu, dan jumlah peserta.
                                <span class="text-red-500 font-medium">Jika lab tidak tersedia maka lab sedang dibooking.</span>
                            </p>

                            <!-- Conflict Warning Box (Hidden by default) -->
                            <div id="conflictWarning" class="hidden mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                                <div class="flex items-start">
                                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="font-bold text-red-800">⚠️ Lab Tidak Tersedia</p>
                                        <p class="text-sm text-red-700 mt-1">Lab ini sudah ada booking yang diajukan di jam dan di lab ini. Silakan pilih jadwal atau lab lain.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Capacity Warning Box (Hidden by default) -->
                            <div id="capacityWarning" class="hidden mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                                <div class="flex items-start">
                                    <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <p class="font-bold text-yellow-800">⚠️ Kapasitas Tidak Memadai</p>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            Kapasitas lab ini (<span id="labCapacityDisplay" class="font-bold"></span>) lebih kecil dari jumlah peserta (<span id="participantCountDisplay" class="font-bold"></span>).
                                        </p>
                                        <p class="text-xs text-yellow-800 mt-2 italic font-semibold">
                                            Konsekuensi: Fasilitas mungkin tidak mencukupi untuk setiap peserta dan ketidaknyamanan ditanggung sendiri.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notice Peminjaman Berulang untuk Perkuliahan Tetap - Generic (ketika belum lengkap) -->
                    <div id="recurring-booking-notice-generic" class="hidden mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-bold text-blue-800">ℹ️ Peminjaman Berulang</p>
                                <p class="text-sm text-blue-700 mt-1">
                                    Peminjaman akan berulang sesuai hari, jam, dan lab yang dipilih setiap minggu.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notice Peminjaman Berulang untuk Perkuliahan Tetap - Specific (ketika sudah lengkap) -->
                    <div id="recurring-booking-notice-specific" class="hidden mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-bold text-blue-800">ℹ️ Peminjaman Berulang</p>
                                <p class="text-sm text-blue-700 mt-1">
                                    Peminjaman akan berulang setiap hari <span id="recurring-day-name" class="font-semibold"></span>
                                    di jam <span id="recurring-time-range" class="font-semibold"></span>
                                    di lab <span id="recurring-lab-name" class="font-semibold"></span>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse md:flex-row justify-between gap-3 md:gap-0 mt-8">
                        <button type="button" id="btn-prev-3" class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors text-sm md:text-base">
                            ← Kembali
                        </button>
                        <button type="button" id="btn-next-3" class="w-full md:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base" disabled>
                            Lanjut →
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Upload Dokumen & Submit -->
                <div id="step-4" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">4</span>
                        Upload Dokumen & Konfirmasi
                    </h3>

                    <div class="mb-6" id="upload-document-section">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Upload Dokumen Pendukung (KTM, Surat Lainnya) <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-yellow-500 transition-colors">
                            <input type="file" name="document" id="document" accept=".pdf" class="hidden" required>
                            <label for="document" class="cursor-pointer">
                                <div class="text-4xl mb-2">📄</div>
                                <div class="text-gray-700 font-semibold mb-1">Klik untuk upload dokumen</div>
                                <div class="text-sm text-gray-500 mb-1">PDF maksimal 5MB</div>
                                <div class="text-xs text-gray-400">Jika file terlalu besar, silakan compress terlebih dahulu</div>
                                <div id="file-name" class="text-sm text-yellow-600 font-medium mt-2"></div>
                            </label>
                        </div>
                        @error('document')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Capacity Warning (Red Text) -->
                    <div id="capacity-warning-text" class="hidden mb-6 bg-red-50 border-2 border-red-500 rounded-lg p-5">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-red-900 font-bold text-lg mb-2">⚠️ PERINGATAN KAPASITAS!</h3>
                                <p class="text-red-800 font-semibold mb-2">
                                    Jumlah peserta (<span id="warning-participant-count" class="font-bold"></span> orang)
                                    <span class="text-red-900">MELEBIHI</span>
                                    kapasitas lab (<span id="warning-lab-capacity" class="font-bold"></span> orang)
                                    sebanyak <span id="warning-overflow" class="font-bold text-red-900"></span> orang.
                                </p>
                                <div class="bg-red-100 border-l-4 border-red-700 p-3 mt-3">
                                    <p class="text-red-900 font-bold text-sm mb-1">Konsekuensi:</p>
                                    <ul class="text-red-800 text-sm space-y-1 ml-4 list-disc">
                                        <li>Fasilitas mungkin tidak mencukupi untuk setiap peserta</li>
                                        <li>Ketidaknyamanan ditanggung sendiri</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Ringkasan Peminjaman</h4>
                        <div id="booking-summary" class="space-y-2 text-sm"></div>
                    </div>

                    <div class="flex flex-col-reverse md:flex-row justify-between gap-3 md:gap-0 mt-8">
                        <button type="button" id="btn-prev-4" class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors text-sm md:text-base">
                            ← Kembali
                        </button>
                        <button type="submit" id="btn-submit" disabled class="w-full md:w-auto bg-green-500 hover:bg-green-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base">
                            ✓ Ajukan Peminjaman
                        </button>
                    </div>
                </div>

            </form>

            <!-- Personal Borrowing Section (separate from main booking form) -->
            <div id="personal-borrowing-section" class="hidden">
                <div class="mb-6">
                    <button type="button" id="btn-back-to-step1" class="inline-flex items-center text-gray-600 hover:text-yellow-600 transition-colors mb-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        ← Kembali Pilih Tipe
                    </button>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 mb-2">Peminjaman Pribadi</h3>
                <p class="text-gray-600 mb-6">Pilih kategori peminjam</p>

                <!-- Borrower Type Selection -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <button type="button" id="btn-mahasiswa" class="border-2 border-gray-300 rounded-xl p-4 md:p-6 text-center hover:border-blue-500 transition-all">
                        <div class="mb-2">
                            <svg class="w-8 h-8 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                        <div class="font-bold text-gray-800">Mahasiswa</div>
                        <div class="text-xs text-gray-500 mt-1">Mahasiswa FEB UNDIP</div>
                    </button>
                    <button type="button" id="btn-non-mahasiswa" class="border-2 border-gray-300 rounded-xl p-4 md:p-6 text-center hover:border-purple-500 transition-all">
                        <div class="mb-2">
                            <svg class="w-8 h-8 mx-auto text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="font-bold text-gray-800">Non-Mahasiswa</div>
                        <div class="text-xs text-gray-500 mt-1">Dosen / Pegawai / Lainnya</div>
                    </button>
                </div>

                <!-- Mahasiswa Form -->
                <form id="mahasiswaForm" action="{{ route('booking.store') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="booking_type" value="pribadi">
                    <input type="hidden" name="pribadi_sub_type" value="mahasiswa">

                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                        <h4 class="font-bold text-blue-800 mb-4">Masukkan NIM Anda</h4>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">NIM <span class="text-red-500">*</span></label>
                            <div class="flex gap-3">
                                <input type="text" name="nim" id="pb-nim" required
                                    maxlength="20"
                                    placeholder="Masukkan NIM Anda"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 20)"
                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button type="button" id="btn-validate-nim" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold transition-colors whitespace-nowrap">
                                    Cari
                                </button>
                            </div>
                            <p id="nim-validation-msg" class="text-xs mt-2 hidden"></p>
                        </div>

                        <!-- NIM Result (hidden initially) -->
                        <div id="nim-result" class="hidden mt-4 bg-white rounded-lg p-4 border border-green-200">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-semibold text-green-700">NIM valid — data mahasiswa ditemukan</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="btn-submit-mahasiswa" disabled
                        class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed">
                        Ajukan Peminjaman
                    </button>
                </form>

                <!-- Non-Mahasiswa Form -->
                <form id="nonMahasiswaForm" action="{{ route('booking.store') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="booking_type" value="pribadi">
                    <input type="hidden" name="pribadi_sub_type" value="non_mahasiswa">

                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 mb-6">
                        <h4 class="font-bold text-purple-800 mb-4">Data Non-Mahasiswa</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="pic_name" required
                                    pattern="[a-zA-Z\s\.']+"
                                    oninput="this.value = this.value.replace(/[^a-zA-Z\s\.']/g, '')"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Masukkan nama lengkap">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">NIP <span class="text-red-500">*</span></label>
                                <input type="text" name="nip" required
                                    maxlength="30"
                                    placeholder="Masukkan NIP"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 30)"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor Telepon <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone_number" required
                                    minlength="10" maxlength="15" pattern="^08[0-9]{8,13}$"
                                    placeholder="Contoh: 081234567890"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Nomor telepon diawali 08</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-xl font-bold transition-colors">
                        Ajukan Peminjaman
                    </button>
                </form>
            </div>

        </div>

        <!-- Back to Home -->
        <div class="text-center mt-8">
            <a href="{{ route('landing') }}" class="text-gray-600 hover:text-gray-800 font-medium">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let selectedBookingType = '';

        // Helper function to show field errors
        function showFieldError(fieldId, message) {
            const errorEl = document.getElementById(fieldId + '-error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
        }

        // Helper function to clear field errors
        function clearFieldError(fieldId) {
            const errorEl = document.getElementById(fieldId + '-error');
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }
        }

        // Clear errors on input for numeric fields
        ['nim', 'nip', 'phone_number'].forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    clearFieldError(fieldId);
                });
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateStepIndicator();
            setupBookingTypeListener();
            setupStep2Validation();
            setupStep3Validation();
            setupNavigationButtons();
            setupFileUpload();
            setupApplicantStatusListener();
            setupStudyProgramListener();
            setupBimbinganDosenToggle();

            setupRealtimeValidation();
            preventEnterSubmit();
        });

        // Bimbingan Dosen Toggle
        function setupBimbinganDosenToggle() {
            const checkbox = document.getElementById('is_bimbingan_dosen');
            if (!checkbox) return;

            function toggleBimbinganFields() {
                const isChecked = checkbox.checked;
                const nonBimbinganFields = document.getElementById('non-bimbingan-fields');
                const bimbinganFields = document.getElementById('bimbingan-dosen-fields');
                const activityTypeSelect = document.getElementById('activity_type');
                const positionInput = document.getElementById('position');
                const lecturerNameInput = document.getElementById('bimbingan_lecturer_name');
                const lecturerNipInput = document.getElementById('bimbingan_lecturer_nip');
                const checkboxLabel = checkbox.closest('label');

                if (isChecked) {
                    // Hide jenis kegiatan & posisi, show nama dosen & nip
                    nonBimbinganFields.classList.add('hidden');
                    bimbinganFields.classList.remove('hidden');
                    // Disable hidden fields so they don't submit
                    activityTypeSelect.removeAttribute('required');
                    activityTypeSelect.value = '';
                    positionInput.removeAttribute('required');
                    positionInput.value = '';
                    // Enable bimbingan fields with name attributes
                    lecturerNameInput.setAttribute('name', 'lecturer_name');
                    lecturerNameInput.setAttribute('required', 'required');
                    lecturerNipInput.setAttribute('name', 'lecturer_nip');
                    lecturerNipInput.setAttribute('required', 'required');
                    // Visual feedback
                    checkboxLabel.classList.add('bg-blue-50', 'border-blue-400');
                    checkboxLabel.classList.remove('border-gray-200');
                } else {
                    // Show jenis kegiatan & posisi, hide nama dosen & nip
                    nonBimbinganFields.classList.remove('hidden');
                    bimbinganFields.classList.add('hidden');
                    // Re-enable regular fields
                    activityTypeSelect.setAttribute('required', 'required');
                    positionInput.setAttribute('required', 'required');
                    // Remove names from bimbingan fields so they don't submit
                    lecturerNameInput.removeAttribute('name');
                    lecturerNameInput.removeAttribute('required');
                    lecturerNipInput.removeAttribute('name');
                    lecturerNipInput.removeAttribute('required');
                    // Visual feedback
                    checkboxLabel.classList.remove('bg-blue-50', 'border-blue-400');
                    checkboxLabel.classList.add('border-gray-200');
                }
                // Re-validate step 2
                validateStep2();
            }

            checkbox.addEventListener('change', toggleBimbinganFields);
            // Also listen to bimbingan dosen input fields for validation
            const lecturerNameInput = document.getElementById('bimbingan_lecturer_name');
            const lecturerNipInput = document.getElementById('bimbingan_lecturer_nip');
            if (lecturerNameInput) lecturerNameInput.addEventListener('input', validateStep2);
            if (lecturerNipInput) lecturerNipInput.addEventListener('input', validateStep2);
            // Run on load for old() values
            toggleBimbinganFields();
        }

        // Prevent Enter key from submitting form, use it for navigation instead
        function preventEnterSubmit() {
            const form = document.getElementById('bookingForm');
            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.type !== 'submit' && e.target.type !== 'textarea') {
                    e.preventDefault();

                    // Navigate to next step if current step button is enabled
                    if (currentStep === 1 && !document.getElementById('btn-next-1').disabled) {
                        goToStep(2);
                    } else if (currentStep === 2 && !document.getElementById('btn-next-2').disabled) {
                        goToStep(3);
                    } else if (currentStep === 3 && !document.getElementById('btn-next-3').disabled) {
                        generateSummary();
                        goToStep(4);
                    }
                    // Step 4: let it submit normally
                }
            });
        }

        // Booking Type & Unit Selection
        function setupBookingTypeListener() {
            const bookingTypeInputs = document.querySelectorAll('input[name="booking_type"]');
            const unitTypeInputs = document.querySelectorAll('input[name="unit_type"]');

            function checkStep1Validity() {
                const bookingType = document.querySelector('input[name="booking_type"]:checked');
                const unitType = document.querySelector('input[name="unit_type"]:checked');

                if (bookingType && bookingType.value === 'pribadi') {
                    // For pribadi, unit type is not required
                     document.getElementById('btn-next-1').disabled = false;
                     // Hide and disable unit type inputs
                     document.getElementById('unit-selection').classList.add('hidden');
                     unitTypeInputs.forEach(input => {
                         input.disabled = true;
                         input.checked = false;
                     });
                     // Hide entire step indicator bar since pribadi goes to separate section
                     document.querySelector('.step-indicator').classList.add('hidden');

                } else {
                     // For others, unit type is required
                     document.getElementById('unit-selection').classList.remove('hidden');
                     unitTypeInputs.forEach(input => input.disabled = false);
                     document.getElementById('btn-next-1').disabled = !(bookingType && unitType);
                     // Show step indicator bar
                     document.querySelector('.step-indicator').classList.remove('hidden');
                }

                if (bookingType) {
                    selectedBookingType = bookingType.value;
                    // Show/hide appropriate fields for step 2
                    if (selectedBookingType === 'non_perkuliahan') {
                        document.getElementById('perkuliahan-fields').classList.add('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.remove('hidden');

                        setRequiredFields('perkuliahan-fields', false);
                        setRequiredFields('non-perkuliahan-fields', true);
                    } else if (selectedBookingType === 'pribadi') {
                        // Pribadi uses a separate section, hide regular step 2 fields
                        document.getElementById('perkuliahan-fields').classList.add('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.add('hidden');

                        setRequiredFields('perkuliahan-fields', false);
                        setRequiredFields('non-perkuliahan-fields', false);
                    } else {
                        document.getElementById('perkuliahan-fields').classList.remove('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.add('hidden');

                        setRequiredFields('perkuliahan-fields', true);
                        setRequiredFields('non-perkuliahan-fields', false);
                    }

                    // Toggle lab selection based on booking type
                    toggleLabSelection();

                    // Update recurring booking notice based on booking type
                    updateRecurringBookingNotice();
                }
            }

            bookingTypeInputs.forEach(input => input.addEventListener('change', checkStep1Validity));
            unitTypeInputs.forEach(input => input.addEventListener('change', checkStep1Validity));

            // Run on load to set initial state (e.g. from old inputs)
            checkStep1Validity();
        }

        // Setup listener untuk program studi dropdown
        let studyProgramToggleCustomField; // Make it accessible
        function setupStudyProgramListener() {
            const studyProgramSelect = document.getElementById('study_program');
            const studyProgramField = document.getElementById('study-program-field');
            const customStudyProgramField = document.getElementById('custom-study-program-field');
            const customStudyProgramInput = document.getElementById('custom_study_program');

            if (studyProgramSelect && customStudyProgramField) {
                studyProgramToggleCustomField = function() {
                    // Only show custom field if parent study program field is visible AND value is 'Lainnya'
                    const isStudyProgramVisible = studyProgramField && studyProgramField.style.display !== 'none';

                    if (isStudyProgramVisible && studyProgramSelect.value === 'Lainnya') {
                        customStudyProgramField.style.display = 'block';
                        customStudyProgramInput.setAttribute('name', 'custom_study_program');
                        customStudyProgramInput.setAttribute('required', 'required');
                    } else {
                        customStudyProgramField.style.display = 'none';
                        customStudyProgramInput.removeAttribute('name');
                        customStudyProgramInput.removeAttribute('required');
                        customStudyProgramInput.value = '';
                    }
                };

                studyProgramSelect.addEventListener('change', studyProgramToggleCustomField);
                studyProgramToggleCustomField(); // Run on load
            }
        }

        // Setup listener untuk status peminjam (Mahasiswa/Dosen/Pegawai/Lainnya)
        function setupApplicantStatusListener() {
            const statusSelect = document.getElementById('applicant_status');
            const classYearField = document.getElementById('class-year-field');
            const classYearInput = document.getElementById('class_year');
            const customStatusField = document.getElementById('custom-status-field');
            const customStatusInput = document.getElementById('custom_status');
            const studyProgramField = document.getElementById('study-program-field');
            const studyProgramInput = document.getElementById('study_program');
            const customStudyProgramField = document.getElementById('custom-study-program-field');
            const customStudyProgramInput = document.getElementById('custom_study_program');
            const nimField = document.getElementById('nim-field');
            const nimInput = document.getElementById('nim');
            const nipField = document.getElementById('nip-field');
            const nipInput = document.getElementById('nip');

            if (statusSelect && classYearField) {
                // Function to toggle class year field and custom status field visibility
                function toggleFields() {
                    const status = statusSelect.value;

                    // Toggle custom status field
                    if (status === 'Lainnya') {
                        customStatusField.style.display = 'block';
                        customStatusInput.setAttribute('name', 'custom_status');
                        customStatusInput.setAttribute('required', 'required');
                    } else {
                        customStatusField.style.display = 'none';
                        customStatusInput.removeAttribute('name');
                        customStatusInput.removeAttribute('required');
                        customStatusInput.value = ''; // Clear value
                    }

                    // Toggle class year field (only for Mahasiswa)
                    if (status === 'Mahasiswa') {
                        // Show angkatan field and make it required
                        classYearField.style.display = 'block';
                        classYearInput.setAttribute('name', 'class_year');
                        classYearInput.setAttribute('required', 'required');
                    } else {
                        // Hide angkatan field and remove required
                        classYearField.style.display = 'none';
                        classYearInput.removeAttribute('name');
                        classYearInput.removeAttribute('required');
                        classYearInput.value = ''; // Clear value
                    }

                    // Toggle Program Studi, NIM, and NIP fields based on status
                    if (status === 'Dosen' || status === 'Pegawai') {
                        // Hide Program Studi and NIM
                        if (studyProgramField) {
                            studyProgramField.style.display = 'none';
                            studyProgramInput.removeAttribute('required');
                            studyProgramInput.value = '';
                        }
                        // Hide custom study program field
                        if (customStudyProgramField) {
                            customStudyProgramField.style.display = 'none';
                            customStudyProgramInput.removeAttribute('name');
                            customStudyProgramInput.removeAttribute('required');
                            customStudyProgramInput.value = '';
                        }
                        if (nimField) {
                            nimField.style.display = 'none';
                            nimInput.removeAttribute('required');
                            nimInput.value = '';
                        }
                        // Show NIP
                        if (nipField) {
                            nipField.style.display = 'block';
                            nipInput.setAttribute('name', 'nip');
                            nipInput.setAttribute('required', 'required');
                        }
                    } else if (status === 'Lainnya') {
                        // Hide ALL: Program Studi, NIM, and NIP for Lainnya
                        if (studyProgramField) {
                            studyProgramField.style.display = 'none';
                            studyProgramInput.removeAttribute('required');
                            studyProgramInput.value = '';
                        }
                        // Hide custom study program field
                        if (customStudyProgramField) {
                            customStudyProgramField.style.display = 'none';
                            customStudyProgramInput.removeAttribute('name');
                            customStudyProgramInput.removeAttribute('required');
                            customStudyProgramInput.value = '';
                        }
                        if (nimField) {
                            nimField.style.display = 'none';
                            nimInput.removeAttribute('required');
                            nimInput.value = '';
                        }
                        if (nipField) {
                            nipField.style.display = 'none';
                            nipInput.removeAttribute('name');
                            nipInput.removeAttribute('required');
                            nipInput.value = '';
                        }
                    } else {
                        // Show Program Studi and NIM for Mahasiswa
                        if (studyProgramField) {
                            studyProgramField.style.display = 'block';
                            studyProgramInput.setAttribute('required', 'required');
                        }
                        if (nimField) {
                            nimField.style.display = 'block';
                            nimInput.setAttribute('required', 'required');
                        }
                        // Hide NIP
                        if (nipField) {
                            nipField.style.display = 'none';
                            nipInput.removeAttribute('name');
                            nipInput.removeAttribute('required');
                            nipInput.value = '';
                        }
                    }

                    // Re-validate step 2
                    validateStep2();

                    // Also trigger custom study program toggle to hide it when program studi is hidden
                    if (typeof studyProgramToggleCustomField === 'function') {
                        studyProgramToggleCustomField();
                    }
                }

                // Initial check on page load
                toggleFields();

                // Listen to status changes
                statusSelect.addEventListener('change', toggleFields);

                // Also listen to custom status input
                if (customStatusInput) {
                    customStatusInput.addEventListener('input', validateStep2);
                }

                // Also listen to NIP input
                if (nipInput) {
                    nipInput.addEventListener('input', validateStep2);
                }
            }
        }

        function setRequiredFields(containerId, required) {
            const container = document.getElementById(containerId);
            const inputs = container.querySelectorAll('input, textarea, select');
            const optionalFields = ['software_needs', 'equipment_needs', 'is_bimbingan_dosen']; // Fields that are always optional
            const conditionalFields = ['class_year', 'custom_status', 'applicant_status', 'nip', 'nim', 'study_program']; // Fields handled by toggleFields() or conditional logic

            inputs.forEach(input => {
                // Skip fields yang tidak punya name attribute (conditional fields)
                if (!input.name && !input.getAttribute('data-name')) {
                    return;
                }

                // Skip conditional fields (dihandle oleh toggleFields)
                if (conditionalFields.includes(input.name) || conditionalFields.includes(input.getAttribute('data-name'))) {
                    return;
                }

                // Skip optional fields
                if (optionalFields.includes(input.name)) {
                    input.removeAttribute('required');
                    if (!required) {
                        input.setAttribute('disabled', 'disabled');
                    } else {
                        input.removeAttribute('disabled');
                    }
                    return;
                }

                // Handle required/optional based on booking type
                if (required) {
                    input.setAttribute('required', 'required');
                    input.removeAttribute('disabled');
                } else {
                    input.removeAttribute('required');
                    input.setAttribute('disabled', 'disabled');
                }
            });
        }

        // Step 2 Validation
        function setupStep2Validation() {
            const requiredFields = ['pic_name', 'study_program', 'nim', 'phone_number', 'nip'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', validateStep2);
                }
            });

            // Also listen to conditional fields
            ['course_name', 'lecturer_name', 'lecturer_nip', 'activity_name', 'activity_type', 'position', 'applicant_status', 'class_year', 'purpose', 'custom_status', 'custom_study_program'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    // Use 'change' for select elements, 'input' for text/textarea
                    const eventType = field.tagName === 'SELECT' ? 'change' : 'input';
                    field.addEventListener(eventType, validateStep2);
                }
            });
        }

        function validateStep2() {
            const nama = document.getElementById('pic_name').value.trim();
            const telpon = document.getElementById('phone_number').value.trim();

            // Phone validation: must start with 08 and be 10-15 digits
            const phonePattern = /^08[0-9]{8,13}$/;
            const phoneValid = phonePattern.test(telpon);

            let isValid = nama && phoneValid;

            // Check conditional fields based on booking type
            if (selectedBookingType === 'perkuliahan_tetap' || selectedBookingType === 'perkuliahan_tidak_tetap') {
                const prodi = document.getElementById('study_program').value.trim();
                const nim = document.getElementById('nim').value.trim();
                const mataKuliah = document.getElementById('course_name').value.trim();
                const dosen = document.getElementById('lecturer_name').value.trim();
                const nip = document.getElementById('lecturer_nip').value.trim();

                // Check custom study program if 'Lainnya' is selected
                if (prodi === 'Lainnya') {
                    const customProdi = document.getElementById('custom_study_program');
                    isValid = isValid && customProdi && customProdi.value.trim();
                }

                isValid = isValid && prodi && nim.length === 14 && mataKuliah && dosen && nip;
            } else if (selectedBookingType === 'non_perkuliahan') {
                const prodi = document.getElementById('study_program').value.trim();
                const nim = document.getElementById('nim').value.trim();
                const namaKegiatan = document.getElementById('activity_name').value.trim();
                const isBimbingan = document.getElementById('is_bimbingan_dosen') && document.getElementById('is_bimbingan_dosen').checked;

                // Check custom study program if 'Lainnya' is selected
                if (prodi === 'Lainnya') {
                    const customProdi = document.getElementById('custom_study_program');
                    isValid = isValid && customProdi && customProdi.value.trim();
                }

                if (isBimbingan) {
                    // Bimbingan dosen: need lecturer name & nip instead of activity_type & position
                    const dosenName = document.getElementById('bimbingan_lecturer_name').value.trim();
                    const dosenNip = document.getElementById('bimbingan_lecturer_nip').value.trim();
                    isValid = isValid && prodi && nim.length === 14 && namaKegiatan && dosenName && dosenNip;
                } else {
                    // Regular non-perkuliahan
                    const jenisKegiatan = document.getElementById('activity_type').value.trim();
                    const jabatan = document.getElementById('position').value.trim();
                    isValid = isValid && prodi && nim.length === 14 && namaKegiatan && jenisKegiatan && jabatan;
                }
            } else if (selectedBookingType === 'pribadi') {
                 const status = document.getElementById('applicant_status').value.trim();
                 const keperluan = document.getElementById('purpose').value.trim();
                 isValid = isValid && status && keperluan;

                 // Check based on status
                 if (status === 'Mahasiswa') {
                     // Mahasiswa need Program Studi and NIM
                     const prodi = document.getElementById('study_program').value.trim();
                     const nim = document.getElementById('nim').value.trim();

                     // Check custom study program if 'Lainnya' is selected
                     if (prodi === 'Lainnya') {
                         const customProdi = document.getElementById('custom_study_program');
                         isValid = isValid && customProdi && customProdi.value.trim();
                     }

                     isValid = isValid && prodi && nim.length === 14;
                 } else if (status === 'Dosen' || status === 'Pegawai') {
                     // Dosen and Pegawai need NIP (18 digits)
                     const nip = document.getElementById('nip').value.trim();
                     isValid = isValid && nip.length === 18;
                 }
                 // Lainnya does not need Program Studi, NIM, or NIP

                 // Status custom wajib jika pilih Lainnya
                 if (status === 'Lainnya') {
                     const customStatus = document.getElementById('custom_status').value.trim();
                     isValid = isValid && customStatus;
                 }

                 // Angkatan hanya wajib untuk mahasiswa
                 if (status === 'Mahasiswa') {
                     const angkatan = document.getElementById('class_year').value.trim();
                     isValid = isValid && angkatan;
                 }
            }

            document.getElementById('btn-next-2').disabled = !isValid;
        }

        // Step 3 Validation
        // Update recurring booking notice for perkuliahan tetap
        function updateRecurringBookingNotice() {
            const recurringNoticeGeneric = document.getElementById('recurring-booking-notice-generic');
            const recurringNoticeSpecific = document.getElementById('recurring-booking-notice-specific');
            const recurringDayName = document.getElementById('recurring-day-name');
            const recurringTimeRange = document.getElementById('recurring-time-range');
            const recurringLabName = document.getElementById('recurring-lab-name');

            const bookingDate = document.getElementById('booking_date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const labSelect = document.getElementById('labSelect');
            const selectedLabOption = labSelect.options[labSelect.selectedIndex];

            // Only show for perkuliahan_tetap
            if (selectedBookingType === 'perkuliahan_tetap') {
                // Check if all fields are filled
                if (bookingDate && startTime && endTime && labSelect.value) {
                    // Show specific notice with actual values
                    const date = new Date(bookingDate);
                    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const dayName = dayNames[date.getDay()];

                    // Get lab name from selected option text (e.g. "EL. 309 (Kap: 30)" -> "EL. 309")
                    const labFullText = selectedLabOption.textContent;
                    const labName = labFullText.split(' (')[0];

                    recurringDayName.textContent = dayName;
                    recurringTimeRange.textContent = startTime + ' - ' + endTime;
                    recurringLabName.textContent = labName;

                    recurringNoticeGeneric.classList.add('hidden');
                    recurringNoticeSpecific.classList.remove('hidden');
                } else {
                    // Show generic notice
                    recurringNoticeGeneric.classList.remove('hidden');
                    recurringNoticeSpecific.classList.add('hidden');
                }
            } else {
                // Hide both notices for non perkuliahan_tetap
                recurringNoticeGeneric.classList.add('hidden');
                recurringNoticeSpecific.classList.add('hidden');
            }
        }

        function setupStep3Validation() {
            const bookingDate = document.getElementById('booking_date');
            const participantCount = document.getElementById('participant_count');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            const labSelect = document.getElementById('labSelect');

            [bookingDate, participantCount, startTime, endTime].forEach(field => {
                field.addEventListener('change', function() {
                    // Only fetch labs for non-pribadi bookings
                    if (selectedBookingType !== 'pribadi') {
                        fetchAvailableLabs();
                    }
                    validateStep3();
                    updateRecurringBookingNotice();
                });
            });

            labSelect.addEventListener('change', function() {
                validateStep3();
                checkCapacityWarning();
                updateRecurringBookingNotice();
            });

            // No need to check conflict when date/time changes
            // because fetchAvailableLabs() already filters out conflicting labs
        }
        function validateStep3() {
            const bookingDate = document.getElementById('booking_date').value;
            const participantCount = document.getElementById('participant_count').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const lab = document.getElementById('labSelect').value;
            const sundayWarning = document.getElementById('sunday-warning');
            const timeError = document.getElementById('time-error');

            // Check if selected date is a Sunday
            let isSunday = false;
            if (bookingDate) {
                const date = new Date(bookingDate);
                isSunday = date.getDay() === 0; // 0 = Sunday

                if (isSunday) {
                    sundayWarning.classList.remove('hidden');
                } else {
                    sundayWarning.classList.add('hidden');
                }
            } else {
                sundayWarning.classList.add('hidden');
            }

            // Validate time - end time must be after start time
            let isTimeValid = true;
            if (startTime && endTime) {
                const [startH, startM] = startTime.split(':').map(Number);
                const [endH, endM] = endTime.split(':').map(Number);
                const startMinutes = startH * 60 + startM;
                const endMinutes = endH * 60 + endM;

                if (endMinutes <= startMinutes) {
                    isTimeValid = false;
                    timeError.classList.remove('hidden');
                } else {
                    timeError.classList.add('hidden');
                }
            } else {
                timeError.classList.add('hidden');
            }

            // For pribadi bookings, lab is not required
            const isPribadi = selectedBookingType === 'pribadi';
            const isValid = bookingDate && participantCount && startTime && endTime && (isPribadi || lab) && !isSunday && isTimeValid;
            document.getElementById('btn-next-3').disabled = !isValid;
        }

        // Toggle lab selection visibility based on booking type
        function toggleLabSelection() {
            const labContainer = document.getElementById('lab-selection-container');
            const labSelect = document.getElementById('labSelect');

            if (selectedBookingType === 'pribadi') {
                // Hide lab selection for pribadi
                labContainer.classList.add('hidden');
                // Disable and clear lab select
                labSelect.disabled = true;
                labSelect.value = '';
                labSelect.removeAttribute('required');
            } else {
                // Show lab selection for other types
                labContainer.classList.remove('hidden');
                labSelect.setAttribute('required', 'required');
            }
        }

        // Fetch Available Labs
        function fetchAvailableLabs() {
            // Skip for pribadi bookings - they don't need lab selection
            if (selectedBookingType === 'pribadi') {
                return;
            }

            const bookingDate = document.getElementById('booking_date').value;
            const participantCount = document.getElementById('participant_count').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            if (!bookingDate || !participantCount || !startTime || !endTime) return;

            const labSelect = document.getElementById('labSelect');
            labSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch('{{ route("booking.available-labs") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    booking_date: bookingDate,
                    participant_count: participantCount,
                    start_time: startTime,
                    end_time: endTime
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(labs => {
                labSelect.innerHTML = '';
                if (labs.length === 0) {
                    labSelect.innerHTML = '<option value="">Tidak ada lab tersedia</option>';
                    labSelect.disabled = true;
                } else {
                    labSelect.innerHTML = '<option value="">-- Pilih Laboratorium --</option>';
                    labs.forEach(lab => {
                        const option = document.createElement('option');
                        option.value = lab.id;
                        // Store capacity in data attribute
                        option.dataset.capacity = lab.capacity;

                        const isUnderCapacity = lab.capacity < parseInt(participantCount);
                        const warningIcon = isUnderCapacity ? '⚠️ ' : '';
                        const warningText = isUnderCapacity ? ' (Kapasitas Kurang)' : '';

                        option.textContent = `${warningIcon}${lab.name} (Kap: ${lab.capacity})${warningText}`;
                        labSelect.appendChild(option);
                    });
                    labSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                labSelect.innerHTML = '<option value="">Gagal memuat data lab</option>';
                alert('Terjadi kesalahan saat memuat data laboratorium. Silakan cek konsol atau coba lagi.');
            });
        }


        // Check Capacity Warning
        function checkCapacityWarning() {
            const labSelect = document.getElementById('labSelect');
            const participantCount = parseInt(document.getElementById('participant_count').value);
            const warningBox = document.getElementById('capacityWarning');

            if (!labSelect.value || !participantCount) {
                warningBox.classList.add('hidden');
                return;
            }

            const selectedOption = labSelect.options[labSelect.selectedIndex];
            const labCapacity = parseInt(selectedOption.dataset.capacity);

            if (labCapacity < participantCount) {
                // Show warning
                document.getElementById('labCapacityDisplay').textContent = labCapacity;
                document.getElementById('participantCountDisplay').textContent = participantCount;
                warningBox.classList.remove('hidden');
            } else {
                // Hide warning
                warningBox.classList.add('hidden');
            }
        }

        // Navigation
        function setupNavigationButtons() {
            document.getElementById('btn-next-1').addEventListener('click', () => {
                if (selectedBookingType === 'pribadi') {
                    // Show personal borrowing section instead of regular step 2
                    document.getElementById('step-1').classList.add('hidden');
                    document.getElementById('personal-borrowing-section').classList.remove('hidden');
                    document.querySelector('.step-indicator').classList.add('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    goToStep(2);
                }
            });
            document.getElementById('btn-next-2').addEventListener('click', () => goToStep(3));
            document.getElementById('btn-next-3').addEventListener('click', () => {
                generateSummary();
                goToStep(4);
            });

            document.getElementById('btn-prev-2').addEventListener('click', () => goToStep(1));
            document.getElementById('btn-prev-3').addEventListener('click', () => goToStep(2));
            document.getElementById('btn-prev-4').addEventListener('click', () => goToStep(3));
        }

        function updateUploadVisibility() {
            const uploadSection = document.getElementById('upload-document-section');
            const fileInput = document.getElementById('document');

            // Default: Show and Required
            let showUpload = true;
            let requiredUpload = true;

            if (selectedBookingType === 'pribadi') {
                 // Pribadi: Hide and Not Required for all
                 showUpload = false;
                 requiredUpload = false;
            }

            if (showUpload) {
                uploadSection.classList.remove('hidden');
                fileInput.setAttribute('required', 'required');
            } else {
                uploadSection.classList.add('hidden');
                fileInput.removeAttribute('required');
                fileInput.value = ''; // Clear file if hidden
                document.getElementById('file-name').textContent = '';
            }
            // Trigger validation for step 4 status
            validateStep4();
        }

        function goToStep(step) {
            // Check visibility if entering step 4
            if (step === 4) {
                updateUploadVisibility();
            }

            // Hide current step
            document.getElementById(`step-${currentStep}`).classList.add('hidden');

            // Show new step
            document.getElementById(`step-${step}`).classList.remove('hidden');
            document.getElementById(`step-${step}`).classList.remove('step-disabled');

            currentStep = step;
            updateStepIndicator();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function updateStepIndicator() {
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step-indicator-${i}`);
                if (i < currentStep) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('active');
                } else if (i === currentStep) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else {
                    indicator.classList.remove('active', 'completed');
                }
            }
        }

        // Generate Summary
        function generateSummary() {
            const bookingTypeLabels = {
                'perkuliahan_tetap': 'Perkuliahan Tetap',
                'perkuliahan_tidak_tetap': 'Perkuliahan Tidak Tetap',
                'non_perkuliahan': 'Non-Perkuliahan',
                'pribadi': 'Pribadi'
            };

            const summary = [];
            summary.push(`<div><strong>Tipe:</strong> ${bookingTypeLabels[selectedBookingType]}</div>`);

            // Add Unit if not pribadi
             if (selectedBookingType !== 'pribadi') {
                 const unitLabels = {
                     's1_tembalang': 'S1 Tembalang',
                     'pascasarjana_pleburan': 'Pascasarjana Pleburan'
                 };
                 const unit = document.querySelector('input[name="unit_type"]:checked').value;
                 summary.push(`<div><strong>Unit:</strong> ${unitLabels[unit]}</div>`);
             }

            summary.push(`<div><strong>Nama:</strong> ${document.getElementById('pic_name').value}</div>`);

            // Display NIP or NIM based on what's filled
            const nimValue = document.getElementById('nim').value;
            const nipValue = document.getElementById('nip').value;

            if (nipValue) {
                summary.push(`<div><strong>NIP:</strong> ${nipValue}</div>`);
            } else if (nimValue) {
                summary.push(`<div><strong>NIM:</strong> ${nimValue}</div>`);
            }

            // Specific fields based on type
             if (selectedBookingType === 'pribadi') {
                const status = document.getElementById('applicant_status').value;
                let statusDisplay = status;

                // Jika status adalah Lainnya, gunakan custom status
                if (status === 'Lainnya') {
                    statusDisplay = document.getElementById('custom_status').value;
                }

                summary.push(`<div><strong>Status:</strong> ${statusDisplay}</div>`);
                if (status === 'Mahasiswa') {
                    summary.push(`<div><strong>Angkatan:</strong> ${document.getElementById('class_year').value}</div>`);
                }
                summary.push(`<div><strong>Keperluan:</strong> ${document.getElementById('purpose').value}</div>`);
            } else if (selectedBookingType === 'non_perkuliahan') {
                summary.push(`<div><strong>Kegiatan:</strong> ${document.getElementById('activity_name').value}</div>`);
                const isBimbingan = document.getElementById('is_bimbingan_dosen') && document.getElementById('is_bimbingan_dosen').checked;
                if (isBimbingan) {
                    summary.push(`<div><strong>Bimbingan Dosen:</strong> Ya</div>`);
                    summary.push(`<div><strong>Nama Dosen:</strong> ${document.getElementById('bimbingan_lecturer_name').value}</div>`);
                    summary.push(`<div><strong>NIP Dosen:</strong> ${document.getElementById('bimbingan_lecturer_nip').value}</div>`);
                }
            } else {
                 summary.push(`<div><strong>Mata Kuliah:</strong> ${document.getElementById('course_name').value}</div>`);
            }

            summary.push(`<div><strong>Tanggal:</strong> ${document.getElementById('booking_date').value}</div>`);
            summary.push(`<div><strong>Waktu:</strong> ${document.getElementById('start_time').value} - ${document.getElementById('end_time').value}</div>`);
            summary.push(`<div><strong>Peserta:</strong> ${document.getElementById('participant_count').value} orang</div>`);

            // Lab and capacity warning only shown for non-pribadi bookings
            const warningBox = document.getElementById('capacity-warning-text');
            if (selectedBookingType !== 'pribadi') {
                const labSelect = document.getElementById('labSelect');
                const labName = labSelect.options[labSelect.selectedIndex].text;
                summary.push(`<div><strong>Lab:</strong> ${labName}</div>`);

                document.getElementById('booking-summary').innerHTML = summary.join('');

                // Check and display capacity warning
                const participantCount = parseInt(document.getElementById('participant_count').value);
                const labCapacity = parseInt(labSelect.options[labSelect.selectedIndex].dataset.capacity);

                if (labCapacity < participantCount) {
                    // Show warning
                    document.getElementById('warning-participant-count').textContent = participantCount;
                    document.getElementById('warning-lab-capacity').textContent = labCapacity;
                    document.getElementById('warning-overflow').textContent = participantCount - labCapacity;
                    warningBox.classList.remove('hidden');
                } else {
                    // Hide warning
                    warningBox.classList.add('hidden');
                }
            } else {
                document.getElementById('booking-summary').innerHTML = summary.join('');
                // Hide capacity warning for pribadi bookings
                warningBox.classList.add('hidden');
            }
        }

        // File Upload
        function setupFileUpload() {
            const docInput = document.getElementById('document');
            if (!docInput || docInput._fileUploadBound) return; // Guard against double-binding
            docInput._fileUploadBound = true;

            docInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const fileNameDisplay = document.getElementById('file-name');
                const uploadBox = this.closest('.border-dashed');

                // Remove previous error
                const existingError = uploadBox.parentElement.querySelector('.file-validation-error');
                if (existingError) existingError.remove();
                uploadBox.classList.remove('border-red-400', 'bg-red-50');

                if (file) {
                    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
                    const allowedType = 'application/pdf';
                    const allowedExt = file.name.toLowerCase().endsWith('.pdf');
                    let errorMsg = '';

                    if (file.type !== allowedType && !allowedExt) {
                        errorMsg = '⚠️ Format file harus PDF. File yang dipilih: ' + file.name.split('.').pop().toUpperCase();
                    } else if (file.size > maxSize) {
                        errorMsg = '⚠️ Ukuran file maksimal 5MB. File yang dipilih: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
                    }

                    if (errorMsg) {
                        // Show custom error modal
                        showFileErrorModal(errorMsg);

                        // Show inline error
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'file-validation-error mt-2 bg-red-50 border border-red-300 text-red-700 px-4 py-2 rounded-lg text-sm flex items-center';
                        errorDiv.innerHTML = '<svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>' + errorMsg;
                        uploadBox.parentElement.appendChild(errorDiv);
                        uploadBox.classList.add('border-red-400', 'bg-red-50');

                        this.value = '';
                        fileNameDisplay.textContent = '';
                        fileNameDisplay.classList.remove('text-green-600');
                        validateStep4();
                        return;
                    }

                    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                    fileNameDisplay.textContent = `✓ Terpilih: ${file.name} (${fileSizeMB} MB)`;
                    fileNameDisplay.classList.add('text-green-600');
                    uploadBox.classList.add('border-green-400', 'bg-green-50');
                } else {
                    fileNameDisplay.textContent = '';
                    fileNameDisplay.classList.remove('text-green-600');
                    uploadBox.classList.remove('border-green-400', 'bg-green-50');
                }
                validateStep4();
            });
        }

        function validateStep4() {
             const documentInput = document.getElementById('document');
             const submitBtn = document.getElementById('btn-submit');
             const docSection = document.getElementById('upload-document-section');

             // If section is hidden, it's valid (no upload needed)
             if (docSection.classList.contains('hidden')) {
                 submitBtn.disabled = false;
                 return;
             }

             // If visible, check if file is selected
             if (documentInput.files.length > 0) {
                 submitBtn.disabled = false;
             } else {
                 submitBtn.disabled = true;
             }
        }

        // Add conditional required attributes
        document.addEventListener('DOMContentLoaded', function() {
            // Mark conditional fields
            const conditionalFields = [
                'course_name', 'lecturer_name', 'lecturer_nip',
                'activity_name', 'activity_type'
            ];
            conditionalFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.setAttribute('data-conditional-required', 'true');
                }
                if (field) {
                    field.setAttribute('data-conditional-required', 'true');
                }
            });
        });

        // Time Dropdown Logic
        function setupTimeDropdowns() {
            const timeInputs = ['start', 'end'];

            timeInputs.forEach(prefix => {
                const hourSelect = document.getElementById(prefix + '_hour');
                const minuteSelect = document.getElementById(prefix + '_minute');
                const hiddenInput = document.getElementById(prefix + '_time');

                function updateHiddenInput() {
                    if (hourSelect.value && minuteSelect.value) {
                        hiddenInput.value = `${hourSelect.value}:${minuteSelect.value}`;

                        // Trigger change event manually for fetchAvailableLabs and validation
                        hiddenInput.dispatchEvent(new Event('change'));
                    } else {
                        hiddenInput.value = '';
                    }
                }

                // Initialize from hidden input (e.g. old value)
                if (hiddenInput.value) {
                    const [h, m] = hiddenInput.value.split(':');
                    if (h) hourSelect.value = h;
                    if (m) minuteSelect.value = m;
                }

                hourSelect.addEventListener('change', updateHiddenInput);
                minuteSelect.addEventListener('change', updateHiddenInput);
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupTimeDropdowns(); // Add this line
            updateStepIndicator();
            setupBookingTypeListener();
            setupStep2Validation();
            setupStep3Validation();
            setupNavigationButtons();
            setupFileUpload();
            setupApplicantStatusListener();
            setupStudyProgramListener();

            setupRealtimeValidation();
            preventEnterSubmit();
        });

        // Realtime Validation Logic
        function setupRealtimeValidation() {
            const inputs = document.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                // Validate on blur
                input.addEventListener('blur', () => {
                    validateSingleField(input);
                });

                // Clear error on input if valid
                input.addEventListener('input', () => {
                    if (input.classList.contains('border-red-500')) {
                        validateSingleField(input);
                    }
                });

                // For selects
                input.addEventListener('change', () => {
                    validateSingleField(input);
                });
            });
        }

        function validateSingleField(input) {
            // Skip fields that are functionally hidden or disabled
            if (input.disabled || input.offsetParent === null) return;

            // Skip optional fields that are empty (valid)
            if (!input.required && input.value.trim() === '') {
                clearError(input);
                return;
            }

            if (!input.checkValidity()) {
                let msg = input.validationMessage;
                if (input.validity.valueMissing) msg = 'Field ini wajib diisi';
                if (input.validity.patternMismatch) msg = 'Format input tidak sesuai';
                if (input.validity.tooShort) msg = `Minimal ${input.minLength} karakter`;

                showError(input, msg);
            } else {
                clearError(input);
            }
        }

        function showError(input, message) {
            // Add red border
            input.classList.remove('border-gray-300', 'focus:ring-yellow-500', 'focus:border-transparent');
            input.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');

            // Add error message text
            let parent = input.parentElement;
            let errorText = parent.querySelector('.validation-error-msg');

            if (!errorText) {
                errorText = document.createElement('p');
                errorText.className = 'validation-error-msg text-red-500 text-xs mt-1 italic';
                parent.appendChild(errorText);
            }
            errorText.textContent = `⚠ ${message}`;
        }

        function clearError(input) {
            // Restore normal border
            input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            input.classList.add('border-gray-300', 'focus:ring-yellow-500', 'focus:border-transparent');

            // Remove error message
            let parent = input.parentElement;
            let errorText = parent.querySelector('.validation-error-msg');
            if (errorText) {
                errorText.remove();
            }
        }
    </script>
    <script>
        // Enhanced Custom Dropdown for Booking Page
        class CustomSelectEncoded {
            constructor(originalSelect) {
                this.originalSelect = originalSelect;
                this.originalSelect.style.display = 'none'; // Hide original

                // Create wrapper
                this.wrapper = document.createElement('div');
                this.wrapper.className = 'relative custom-select-wrapper w-full';
                this.originalSelect.parentNode.insertBefore(this.wrapper, this.originalSelect);
                this.wrapper.appendChild(this.originalSelect); // Move original inside

                // Create Trigger Element
                this.trigger = document.createElement('button');
                this.trigger.type = 'button';
                // Match the style of existing inputs in create.blade.php (px-4 py-3 border border-gray-300 rounded-lg)
                this.trigger.className = 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-base text-left bg-white flex justify-between items-center transition-shadow duration-200';

                // Content span
                this.triggerLabel = document.createElement('span');
                this.triggerLabel.className = 'block truncate text-gray-700';

                // Chevron icon
                const chevron = document.createElement('div');
                chevron.innerHTML = `<svg class="w-5 h-5 text-gray-400 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
                this.chevronIcon = chevron.firstElementChild;

                this.trigger.appendChild(this.triggerLabel);
                this.trigger.appendChild(chevron);
                this.wrapper.appendChild(this.trigger);

                // Create Options Container
                this.optionsContainer = document.createElement('div');
                this.optionsContainer.className = 'absolute z-50 w-full bg-white shadow-xl max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm mt-1 hidden scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 option-container-anim';
                this.wrapper.appendChild(this.optionsContainer);

                // Initialize
                this.initOptions();
                this.updateTrigger();

                // Event Listeners
                this.trigger.addEventListener('click', (e) => {
                    if (this.trigger.hasAttribute('disabled')) return;
                    e.stopPropagation();
                    this.toggleDropdown();
                });

                // Close when clicking outside
                document.addEventListener('click', (e) => {
                    if (!this.wrapper.contains(e.target)) {
                        this.closeDropdown();
                    }
                });

                // Listen for changes (value)
                this.originalSelect.addEventListener('change', () => {
                   this.updateTrigger();
                   this.initOptions();
                });

                // Mutation Observer for dynamic changes (attributes AND childList/options)
                this.observer = new MutationObserver((mutations) => {
                    let shouldUpdateTrigger = false;
                    let shouldInitOptions = false;

                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                            shouldUpdateTrigger = true;
                        }
                        if (mutation.type === 'childList') {
                            shouldInitOptions = true;
                            shouldUpdateTrigger = true; // Trigger text might change if options change
                        }
                    });

                    if (shouldInitOptions) this.initOptions();
                    if (shouldUpdateTrigger) this.updateTrigger();
                });

                this.observer.observe(this.originalSelect, {
                    attributes: true,
                    childList: true,
                    subtree: true // needed for option text changes if any
                });
            }

            initOptions() {
                this.optionsContainer.innerHTML = '';
                const options = Array.from(this.originalSelect.options);

                if (options.length === 0) {
                    // No options
                    return;
                }

                options.forEach(option => {
                    // Skip hidden placeholders if desired, but here usually show all that are not hidden
                    if (option.hidden) return;

                    const optionDiv = document.createElement('div');
                    optionDiv.className = `text-gray-900 cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-yellow-50 transition-colors duration-150 border-b border-gray-50 last:border-0`;

                    // Allow html content for warnings (lab capacity icons)
                    // But usually option.text is plain.
                    // In fetchAvailableLabs, we put emoji in textContent. So textContent is safe.
                    optionDiv.textContent = option.text;

                    // Handle disabled options (like headers "Pilih ...")
                    if (option.disabled && option.value === "") {
                        optionDiv.className = 'text-gray-400 select-none relative py-2 pl-4 pr-4 bg-gray-50 cursor-default font-semibold text-sm';
                    }

                    if (option.selected) {
                        optionDiv.classList.add('bg-blue-50', 'text-blue-900', 'font-medium');
                        const check = document.createElement('span');
                        check.className = 'absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600';
                        check.innerHTML = `<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`;
                        optionDiv.appendChild(check);
                    }

                    if (!option.disabled) {
                        optionDiv.addEventListener('click', (e) => {
                            e.stopPropagation();
                            this.originalSelect.value = option.value;
                            this.originalSelect.dispatchEvent(new Event('change'));
                            this.closeDropdown();
                        });
                    }

                    this.optionsContainer.appendChild(optionDiv);
                });
            }

            updateTrigger() {
                const selectedOption = this.originalSelect.options[this.originalSelect.selectedIndex];
                this.triggerLabel.textContent = selectedOption ? selectedOption.text : 'Pilih...';

                if (this.originalSelect.disabled) {
                    this.trigger.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-60');
                    this.trigger.setAttribute('disabled', 'disabled');
                } else {
                    this.trigger.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-60');
                    this.trigger.removeAttribute('disabled');
                }
            }

            toggleDropdown() {
                const isHidden = this.optionsContainer.classList.contains('hidden');
                // Close others
                document.querySelectorAll('.custom-select-wrapper .options-container').forEach(el => {
                    if (!el.classList.contains('hidden') && el !== this.optionsContainer) {
                        el.classList.add('hidden');
                        const otherChevron = el.parentElement.querySelector('svg');
                        if(otherChevron) otherChevron.classList.remove('rotate-180');
                    }
                });

                if (isHidden) {
                    this.optionsContainer.classList.remove('hidden');
                    this.chevronIcon.classList.add('rotate-180');
                } else {
                    this.closeDropdown();
                }
            }

            closeDropdown() {
                this.optionsContainer.classList.add('hidden');
                this.chevronIcon.classList.remove('rotate-180');
            }
        }

        // Initialize for Booking Page
        document.addEventListener('DOMContentLoaded', function() {
            // Target selects
            const selects = ['activity_type', 'applicant_status', 'labSelect'];

            selects.forEach(id => {
                const selectElement = document.getElementById(id) || document.querySelector(`select[name="${id}"]`);
                if (selectElement) {
                    new CustomSelectEncoded(selectElement);
                }
            });
        });

        // ===== PERSONAL BORROWING SECTION JS =====
        document.addEventListener('DOMContentLoaded', function() {
            const btnBackToStep1 = document.getElementById('btn-back-to-step1');
            const btnMahasiswa = document.getElementById('btn-mahasiswa');
            const btnNonMahasiswa = document.getElementById('btn-non-mahasiswa');
            const mahasiswaForm = document.getElementById('mahasiswaForm');
            const nonMahasiswaForm = document.getElementById('nonMahasiswaForm');
            const btnValidateNim = document.getElementById('btn-validate-nim');
            const pbNimInput = document.getElementById('pb-nim');
            const nimResult = document.getElementById('nim-result');
            const nimValidationMsg = document.getElementById('nim-validation-msg');
            const btnSubmitMahasiswa = document.getElementById('btn-submit-mahasiswa');

            // Back to step 1
            if (btnBackToStep1) {
                btnBackToStep1.addEventListener('click', function() {
                    document.getElementById('personal-borrowing-section').classList.add('hidden');
                    document.getElementById('step-1').classList.remove('hidden');
                    document.querySelector('.step-indicator').classList.remove('hidden');
                    // Reset personal borrowing forms
                    if (mahasiswaForm) mahasiswaForm.classList.add('hidden');
                    if (nonMahasiswaForm) nonMahasiswaForm.classList.add('hidden');
                    if (nimResult) nimResult.classList.add('hidden');
                    if (nimValidationMsg) nimValidationMsg.classList.add('hidden');
                    if (pbNimInput) pbNimInput.value = '';
                    if (btnSubmitMahasiswa) btnSubmitMahasiswa.disabled = true;
                    // Reset button styles
                    btnMahasiswa.classList.remove('border-blue-500', 'bg-blue-50');
                    btnNonMahasiswa.classList.remove('border-purple-500', 'bg-purple-50');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            // Mahasiswa button
            if (btnMahasiswa) {
                btnMahasiswa.addEventListener('click', function() {
                    btnMahasiswa.classList.add('border-blue-500', 'bg-blue-50');
                    btnNonMahasiswa.classList.remove('border-purple-500', 'bg-purple-50');
                    mahasiswaForm.classList.remove('hidden');
                    nonMahasiswaForm.classList.add('hidden');
                });
            }

            // Non-Mahasiswa button
            if (btnNonMahasiswa) {
                btnNonMahasiswa.addEventListener('click', function() {
                    btnNonMahasiswa.classList.add('border-purple-500', 'bg-purple-50');
                    btnMahasiswa.classList.remove('border-blue-500', 'bg-blue-50');
                    nonMahasiswaForm.classList.remove('hidden');
                    mahasiswaForm.classList.add('hidden');
                    // Reset mahasiswa form state
                    if (nimResult) nimResult.classList.add('hidden');
                    if (nimValidationMsg) nimValidationMsg.classList.add('hidden');
                    if (pbNimInput) pbNimInput.value = '';
                    if (btnSubmitMahasiswa) btnSubmitMahasiswa.disabled = true;
                });
            }

            // NIM Validation via AJAX
            if (btnValidateNim) {
                btnValidateNim.addEventListener('click', function() {
                    const nim = pbNimInput.value.trim();
                    if (!nim) {
                        nimValidationMsg.textContent = 'Masukkan NIM terlebih dahulu.';
                        nimValidationMsg.className = 'text-xs mt-2 text-red-500';
                        nimValidationMsg.classList.remove('hidden');
                        return;
                    }

                    // Show loading state
                    btnValidateNim.disabled = true;
                    btnValidateNim.textContent = 'Mencari...';
                    nimValidationMsg.classList.add('hidden');
                    nimResult.classList.add('hidden');
                    btnSubmitMahasiswa.disabled = true;

                    fetch('{{ route("personal-borrowing.validate-nim") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ nim: nim })
                    })
                    .then(response => response.json())
                    .then(data => {
                        btnValidateNim.disabled = false;
                        btnValidateNim.textContent = 'Cari';

                        if (data.found) {
                            // Show simple valid confirmation (no personal data displayed)
                            nimResult.classList.remove('hidden');
                            nimValidationMsg.classList.add('hidden');
                            btnSubmitMahasiswa.disabled = false;
                        } else {
                            nimValidationMsg.textContent = data.message || 'NIM tidak ditemukan dalam database mahasiswa FEB.';
                            nimValidationMsg.className = 'text-xs mt-2 text-red-500';
                            nimValidationMsg.classList.remove('hidden');
                            nimResult.classList.add('hidden');
                            btnSubmitMahasiswa.disabled = true;
                        }
                    })
                    .catch(error => {
                        btnValidateNim.disabled = false;
                        btnValidateNim.textContent = 'Cari';
                        nimValidationMsg.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                        nimValidationMsg.className = 'text-xs mt-2 text-red-500';
                        nimValidationMsg.classList.remove('hidden');
                    });
                });
            }

            // Allow Enter key on NIM input to trigger search
            if (pbNimInput) {
                pbNimInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        btnValidateNim.click();
                    }
                });
            }
        });
    </script>

@include('components.file-error-modal')
</body>
</html>
