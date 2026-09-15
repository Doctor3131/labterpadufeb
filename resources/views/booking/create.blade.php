<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Peminjaman - Laboratorium dan Fasilitas Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 booking-page">
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

    <main class="container mx-auto px-4 md:px-6 py-4 md:py-12 max-w-5xl"
          data-error-step="{{ $errors->has('time_conflict') || $errors->hasAny(['booking_date', 'start_time', 'end_time', 'lab_id', 'participant_count', 'repeat_type', 'schedule_frequency', 'repeat_count', 'recurrence_days', 'repeat_end_date']) ? 3 : ($errors->has('document') ? 4 : ($errors->hasAny(['booking_type', 'unit_type']) ? 1 : ($errors->any() ? 2 : 1))) }}"
          data-personal-sub-type="{{ old('pribadi_sub_type') }}">
        <!-- Header -->
        <div class="booking-hero text-center mb-6 md:mb-9">
            <p class="booking-eyebrow">LABORATORIUM TERPADU FEB</p>
            <h1 class="booking-title">Ajukan peminjaman lab</h1>
            <p class="booking-subtitle">Isi data secara bertahap. Kolom bertanda <span class="text-red-600">*</span> wajib dilengkapi.</p>
        </div>

        <!-- Form Card -->
        <div class="booking-card p-4 md:p-8">
            <!-- Time Conflict Warning (More Prominent) -->
            @if ($errors->has('time_conflict'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-4" role="alert" tabindex="-1" id="booking-error-summary">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-red-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-red-900 font-bold text-lg mb-2">Konflik jadwal</h3>
                            <p class="text-red-800 font-semibold">{{ $errors->first('time_conflict') }}</p>
                            <p class="text-red-700 text-sm mt-2">Pengajuan lain baru saja masuk pada lab dan waktu yang sama. Silakan pilih waktu atau ruangan lain.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any() && !$errors->has('time_conflict'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-4" role="alert" tabindex="-1" id="booking-error-summary">
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
            <div class="step-indicator mb-6 md:mb-8" aria-label="Progres pengajuan">
                <div class="step-item active" id="step-indicator-1" aria-current="step">
                    <div class="step-number">1</div>
                    <div class="step-label">Kebutuhan</div>
                </div>
                <div class="step-item" id="step-indicator-2">
                    <div class="step-number">2</div>
                    <div class="step-label">Data pengaju</div>
                </div>
                <div class="step-item" id="step-indicator-3">
                    <div class="step-number">3</div>
                    <div class="step-label">Jadwal</div>
                </div>
                <div class="step-item" id="step-indicator-4">
                    <div class="step-number">4</div>
                    <div class="step-label">Tinjau</div>
                </div>
            </div>
            <p id="step-progress-status" class="step-status text-center -mt-4 mb-6" aria-live="polite">Langkah 1 dari 4</p>

            <form action="{{ route('booking.store') }}" method="POST" enctype="multipart/form-data" id="bookingForm">
                @csrf

                <!-- STEP 1: Tipe Peminjaman -->
                <div id="step-1" class="step-section">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">1</span>
                        Mulai Pengajuan
                    </h3>
                    <p class="section-intro">Pilih jenis penggunaan laboratorium dan lokasi unit yang sesuai.</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="perkuliahan_tetap" class="sr-only peer" required {{ old('booking_type') == 'perkuliahan_tetap' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Perkuliahan Tetap</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Jadwal rutin</div>
                            </div>
                        </label>
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="perkuliahan_tidak_tetap" class="sr-only peer" required {{ old('booking_type') == 'perkuliahan_tidak_tetap' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Perkuliahan Tidak Tetap</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Sekali waktu</div>
                            </div>
                        </label>
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="non_perkuliahan" class="sr-only peer" required {{ old('booking_type') == 'non_perkuliahan' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Non-Perkuliahan</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Kegiatan lain (Ormawa, Pelatihan, etc)</div>
                            </div>
                        </label>
                         <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="pribadi" class="sr-only peer" required {{ old('booking_type') == 'pribadi' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-300 rounded-lg p-3 md:p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all h-full flex flex-col justify-center min-h-[100px]">
                                <div class="font-bold text-gray-800 text-sm md:text-lg">Pribadi</div>
                                <div class="text-xs md:text-sm text-gray-500 mt-1 md:mt-2">Keperluan Pribadi</div>
                            </div>
                        </label>
                    </div>

                    <div id="unit-selection" class="mb-8">
                        <label class="block text-gray-700 font-bold mb-1 text-lg">Unit <span class="text-red-500">*</span></label>
                        <p class="field-help mb-3">Pilih lokasi unit yang akan menggunakan laboratorium.</p>
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

                    <div class="booking-action-bar flex justify-end">
                        <button type="button" id="btn-next-1" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base w-full md:w-auto" disabled>
                            Lanjutkan
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Data Pengaju -->
                <div id="step-2" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">2</span>
                        Data Pengaju & Kegiatan
                    </h3>
                    <p class="section-intro">Masukkan identitas dan informasi kegiatan. Kolom yang tampil menyesuaikan tipe pengajuan.</p>


                    <fieldset id="lecturer-involvement-field" class="md:col-span-2">
                        <legend class="block text-gray-800 text-sm font-bold mb-2">Keterlibatan dosen <span class="font-normal text-gray-500">(opsional, pilih satu)</span></legend>
                        <p class="text-xs text-gray-500 mb-3">Pilihan ini hanya berlaku untuk kegiatan non-perkuliahan.</p>
                        <div role="radiogroup" aria-label="Keterlibatan dosen" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label data-lecturer-option="none" class="booking-choice-card flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer">
                                <input type="radio" name="lecturer_involvement" value="none" class="mt-1 w-4 h-4 text-yellow-700 focus:ring-yellow-500"
                                    {{ ! old('is_on_behalf_lecturer') && ! old('is_bimbingan_dosen') ? 'checked' : '' }}>
                                <span class="ml-3">
                                    <span class="block text-gray-800 font-semibold">Tidak melibatkan dosen</span>
                                    <span class="block text-xs text-gray-500 mt-1">Pengajuan atas nama peminjam.</span>
                                </span>
                            </label>
                            <label data-lecturer-option="on_behalf" class="booking-choice-card flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer">
                                <input type="radio" name="lecturer_involvement" value="on_behalf" class="mt-1 w-4 h-4 text-yellow-700 focus:ring-yellow-500"
                                    {{ old('is_on_behalf_lecturer') ? 'checked' : '' }}>
                                <span class="ml-3">
                                    <span class="block text-gray-800 font-semibold">Atas nama dosen</span>
                                    <span class="block text-xs text-gray-500 mt-1">Gunakan NIP dosen, bukan NIM.</span>
                                </span>
                            </label>
                            <label data-lecturer-option="bimbingan" class="booking-choice-card flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer">
                                <input type="radio" name="lecturer_involvement" value="bimbingan" class="mt-1 w-4 h-4 text-yellow-700 focus:ring-yellow-500"
                                    {{ old('is_bimbingan_dosen') ? 'checked' : '' }}>
                                <span class="ml-3">
                                    <span class="block text-gray-800 font-semibold">Bimbingan bersama dosen</span>
                                    <span class="block text-xs text-gray-500 mt-1">Isi nama dan NIP dosen pembimbing.</span>
                                </span>
                            </label>
                        </div>
                        <input type="hidden" name="is_on_behalf_lecturer" id="is_on_behalf_lecturer" value="{{ old('is_on_behalf_lecturer') ? '1' : '0' }}">
                        <input type="hidden" name="is_bimbingan_dosen" id="is_bimbingan_dosen" value="{{ old('is_bimbingan_dosen') ? '1' : '0' }}">
                    </fieldset>
                    <div class="h-5" aria-hidden="true"></div>

                    <!-- Status Fields for Pribadi (shown first for pribadi booking) -->
                    <div id="pribadi-status-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Pilih Status Terlebih Dahulu</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="applicant_status" class="block text-gray-700 text-sm font-semibold mb-2">Status <span class="text-red-500">*</span></label>
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
                                <label for="custom_status" class="block text-gray-700 text-sm font-semibold mb-2">Status Lainnya <span class="text-red-500">*</span></label>
                                <input type="text" data-name="custom_status" id="custom_status" value="{{ old('custom_status') }}"
                                    placeholder="Masukkan status Anda"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="pic_name" class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="pic_name" id="pic_name" value="{{ old('pic_name') }}" required
                                pattern="[a-zA-Z\s\.']+"
                                title="Hanya huruf, spasi, titik, dan apostrof yang diperbolehkan"
                                oninput="this.value = this.value.replace(/[^a-zA-Z\s\.']/g, '')"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Masukkan nama lengkap (huruf saja)</p>
                            <p id="pic_name-error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <div id="study-program-field">
                            <label for="study_program" class="block text-gray-700 text-sm font-semibold mb-2">Program Studi <span class="text-red-500">*</span></label>
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
                            <label for="custom_study_program" class="block text-gray-700 text-sm font-semibold mb-2">Program Studi Lainnya <span class="text-red-500">*</span></label>
                            <input type="text" id="custom_study_program" value="{{ old('custom_study_program') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div id="nim-field">
                            <label for="nim" class="block text-gray-700 text-sm font-semibold mb-2"><span id="nim-label-text">NIM</span> <span class="text-red-500">*</span> <span class="text-xs text-gray-500">(14 digit)</span></label>
                            <input type="text" name="nim" id="nim" value="{{ old('nim') }}" required
                                maxlength="14" pattern="[0-9]{14}"
                                placeholder="Contoh: 12010120130001"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p id="nim-hint" class="text-xs text-gray-500 mt-1">NIM harus 14 digit angka</p>
                            <p id="nim-error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <div id="nip-field" style="display: none;">
                            <label for="nip" class="block text-gray-700 text-sm font-semibold mb-2"><span id="nip-label-text">NIP</span> <span class="text-red-500">*</span> <span class="text-xs text-gray-500">(18 digit)</span></label>
                            <input type="text" data-name="nip" id="nip" value="{{ old('nip') }}"
                                maxlength="18" pattern="[0-9]{18}"
                                placeholder="Contoh: 198505102010121001"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p id="nip-hint" class="text-xs text-gray-500 mt-1">NIP harus 18 digit angka</p>
                            <p id="nip-error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <div>
                            <label for="phone_number" class="block text-gray-700 text-sm font-semibold mb-2">Nomor Telepon <span class="text-red-500">*</span> <span class="text-xs text-gray-500">(10-15 digit)</span></label>
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
                                <label for="course_name" class="block text-gray-700 text-sm font-semibold mb-2">Mata Kuliah <span class="text-red-500">*</span></label>
                                <input type="text" name="course_name" id="course_name" value="{{ old('course_name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="lecturer_name" class="block text-gray-700 text-sm font-semibold mb-2">Dosen Pengampu <span class="text-red-500">*</span></label>
                                <input type="text" name="lecturer_name" id="lecturer_name" value="{{ old('lecturer_name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="lecturer_nip" class="block text-gray-700 text-sm font-semibold mb-2">NIP Dosen <span class="text-red-500">*</span></label>
                                <input type="text" name="lecturer_nip" id="lecturer_nip" value="{{ old('lecturer_nip') }}"
                                    maxlength="18" pattern="[0-9]{18}"
                                    placeholder="18 digit angka"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div class="md:col-span-2">
                                <label for="software_needs" class="block text-gray-700 text-sm font-semibold mb-2">Software yang Digunakan</label>
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
                                <label for="activity_name" class="block text-gray-700 text-sm font-semibold mb-2">Nama Kegiatan <span class="text-red-500">*</span></label>
                                <input type="text" name="activity_name" id="activity_name" value="{{ old('activity_name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            {{-- Fields yang tampil jika BUKAN bimbingan (default) --}}
                            <div id="non-bimbingan-fields" class="md:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="activity_type" class="block text-gray-700 text-sm font-semibold mb-2">Jenis Kegiatan <span class="text-red-500">*</span></label>
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
                                        <label for="position" class="block text-gray-700 text-sm font-semibold mb-2">Posisi Peminjam <span class="text-red-500">*</span></label>
                                        <input type="text" name="position" id="position" value="{{ old('position') }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            {{-- Fields yang tampil jika bimbingan dosen --}}
                            <div id="bimbingan-dosen-fields" class="hidden md:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="bimbingan_lecturer_name" class="block text-gray-700 text-sm font-semibold mb-2">Nama Dosen <span class="text-red-500">*</span></label>
                                        <input type="text" id="bimbingan_lecturer_name" value="{{ old('lecturer_name') }}"
                                            placeholder="Nama lengkap dosen pembimbing"
                                            oninput="this.value = this.value.replace(/[^a-zA-Z\s\.']/g, '')"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label for="bimbingan_lecturer_nip" class="block text-gray-700 text-sm font-semibold mb-2">NIP Dosen <span class="text-red-500">*</span></label>
                                        <input type="text" id="bimbingan_lecturer_nip" value="{{ old('lecturer_nip') }}"
                                            maxlength="18" pattern="[0-9]{18}"
                                            placeholder="18 digit angka"
                                            inputmode="numeric"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18)"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="equipment_needs" class="block text-gray-700 text-sm font-semibold mb-2">Kebutuhan Peralatan</label>
                                <textarea name="equipment_needs" id="equipment_needs" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('equipment_needs') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div id="pribadi-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Data Peminjaman Pribadi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div id="class-year-field" style="display: none;">
                                <label for="class_year" class="block text-gray-700 text-sm font-semibold mb-2">Angkatan <span class="text-red-500">*</span></label>
                                <input type="text" data-name="class_year" id="class_year" value="{{ old('class_year') }}"
                                    placeholder="Contoh: 2023" maxlength="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div class="md:col-span-2">
                                <label for="purpose" class="block text-gray-700 text-sm font-semibold mb-2">Keperluan <span class="text-red-500">*</span></label>
                                <input type="text" name="purpose" id="purpose" value="{{ old('purpose') }}"
                                    placeholder="Contoh: Ujian, Mengerjakan tugas pribadi"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="booking-action-bar flex flex-col-reverse md:flex-row justify-between gap-3 md:gap-0">
                        <button type="button" id="btn-prev-2" class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors text-sm md:text-base">
                            Kembali
                        </button>
                        <button type="button" id="btn-next-2" class="w-full md:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base" disabled>
                            Lanjutkan
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Detail Peminjaman -->
                <div id="step-3" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">3</span>
                        Pilih Jadwal & Laboratorium
                    </h3>
                    <p class="section-intro">Gunakan kalender untuk melihat slot yang sudah terisi. Anda juga dapat mengisi tanggal dan jam secara manual.</p>

                    <div id="lab-selection-container" class="mb-6">
                        <label for="labSelect" class="block text-gray-700 text-sm font-semibold mb-2">Pilih Laboratorium <span class="text-red-500">*</span></label>
                        <select name="lab_id" id="labSelect" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent disabled:bg-gray-100">
                            <option value="">-- Pilih Laboratorium --</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}" data-capacity="{{ $lab->capacity }}" {{ (string) old('lab_id') === (string) $lab->id ? 'selected' : '' }}>{{ $lab->name }} (Kapasitas {{ $lab->capacity }})</option>
                            @endforeach
                        </select>
                        <p id="lab-availability-status" class="text-sm text-gray-500 mt-2" aria-live="polite">
                            Pilih lab yang ingin digunakan. Setelah tanggal, jam, dan jumlah peserta diisi, sistem akan memeriksa ketersediaannya.
                        </p>

                        <div id="capacityWarning" role="status" class="hidden mt-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333-.192-3 1.732-3z"/>
                                </svg>
                                <div>
                                    <p class="font-bold text-yellow-800">Kapasitas mungkin tidak mencukupi</p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Kapasitas lab ini (<span id="labCapacityDisplay" class="font-bold"></span>) lebih kecil dari jumlah peserta (<span id="participantCountDisplay" class="font-bold"></span>).
                                    </p>
                                    <p class="text-xs text-yellow-800 mt-2 font-semibold">
                                        Anda tetap dapat mengajukan, tetapi fasilitas mungkin tidak mencukupi untuk seluruh peserta.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="booking-calendar-panel" class="schedule-picker-panel mb-6 border rounded-xl p-4 md:p-5">
                        <div class="flex flex-col md:flex-row md:items-center gap-2 mb-4">
                            <div>
                                <h4 class="font-bold text-gray-800">Kalender ketersediaan</h4>
                                <p class="field-help">Klik atau tarik slot kosong untuk mengisi tanggal dan jam pengajuan.</p>
                            </div>
                            <p id="booking-calendar-status" class="field-help md:ml-auto md:text-right" aria-live="polite">Pilih laboratorium terlebih dahulu.</p>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                            <div data-booking-calendar
                                 data-availability-url="{{ route('booking.calendar-availability') }}"
                                 class="min-w-[680px] p-2"></div>
                        </div>
                        <div class="mt-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 field-help">
                            <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-sm bg-red-200 border border-red-400"></span>Terisi / menunggu persetujuan</span>
                            <span>Slot waktu: 07.00–21.00 WIB</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="booking_date" class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Peminjaman <span class="text-red-500">*</span></label>
                            <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date') }}" required
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Peminjaman laboratorium tidak tersedia pada hari Minggu.</p>
                            <!-- Sunday Warning -->
                            <div id="sunday-warning" role="alert" class="hidden mt-2 bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded-lg text-sm">
                                Hari Minggu tidak tersedia untuk peminjaman lab. Silakan pilih tanggal lain.
                            </div>
                        </div>

                        <div>
                            <label for="participant_count" class="block text-gray-700 text-sm font-semibold mb-2">Jumlah Peserta <span class="text-red-500">*</span></label>
                            <input type="number" name="participant_count" id="participant_count" value="{{ old('participant_count') }}" required
                                min="1" placeholder="Contoh: 30"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="start_hour" class="block text-gray-700 text-sm font-semibold mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="hidden" name="start_time" id="start_time" value="{{ old('start_time') }}">
                            <div class="flex gap-2">
                                <select id="start_hour" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                    <option value="" disabled selected>Jam</option>
                                    @foreach(range(7, 21) as $h)
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
                            <label for="end_hour" class="block text-gray-700 text-sm font-semibold mb-2">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="hidden" name="end_time" id="end_time" value="{{ old('end_time') }}">
                            <div class="flex gap-2">
                                <select id="end_hour" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                    <option value="" disabled selected>Jam</option>
                                    @foreach(range(7, 21) as $h)
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
                            <p id="time-error" role="alert" class="text-sm text-red-500 hidden"><strong>* Jam selesai harus setelah jam mulai.</strong></p>
                        </div>

                    </div>

                    <!-- Notice Peminjaman Berulang untuk Perkuliahan Tetap - Generic (ketika belum lengkap) -->
                    <div id="recurring-booking-notice-generic" class="hidden mb-6 bg-amber-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-700 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-bold text-amber-900">Peminjaman berulang</p>
                                <p class="text-sm text-amber-800 mt-1">
                                    Peminjaman akan berulang sesuai hari, jam, dan lab yang dipilih setiap minggu.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notice Peminjaman Berulang untuk Perkuliahan Tetap - Specific (ketika sudah lengkap) -->
                    <div id="recurring-booking-notice-specific" class="hidden mb-6 bg-amber-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-700 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-bold text-amber-900">Peminjaman berulang</p>
                                <p class="text-sm text-amber-800 mt-1">
                                    Peminjaman akan berulang setiap minggu pada <span id="recurring-day-name" class="font-semibold"></span>
                                    di jam <span id="recurring-time-range" class="font-semibold"></span>
                                    di lab <span id="recurring-lab-name" class="font-semibold"></span>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Akhir Pengulangan untuk Perkuliahan Tetap -->
                    <div id="recurring-end-section" class="hidden mb-6 bg-white border border-yellow-200 rounded-xl p-4">
                        <span class="block text-gray-700 text-sm font-semibold mb-3">Berakhir ...</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="repeat_type" id="repeat_count_option" value="count" checked
                                       class="w-4 h-4 text-yellow-500 focus:ring-yellow-500">
                                <label for="repeat_count_option" class="text-sm text-gray-700">Setelah</label>
                                <input type="number" name="repeat_count" id="fixed_repeat_count" min="2" max="60" value="{{ old('repeat_count', 16) }}"
                                       placeholder="N"
                                       class="w-20 px-2 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-sm">
                                <span class="text-sm text-gray-600">kali pertemuan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="radio" name="repeat_type" id="repeat_date" value="date"
                                       class="w-4 h-4 text-yellow-500 focus:ring-yellow-500">
                                <label for="repeat_date" class="text-sm text-gray-700">Hingga tanggal</label>
                                <input type="date" name="repeat_end_date" id="repeat_end_date" min="{{ date('Y-m-d') }}"
                                       class="px-2 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-sm">
                            </div>
                        </div>
                        <p id="recurring-end-hint" class="text-xs text-gray-500 mt-2"></p>
                    </div>

                    <!-- Custom weekly recurrence for perkuliahan tidak tetap -->
                    <div id="nonfixed-frequency-section" class="hidden mb-6 bg-white border border-yellow-200 rounded-xl p-4">
                        <fieldset>
                            <legend class="block text-gray-800 text-sm font-bold mb-2">Frekuensi peminjaman</legend>
                            <p class="text-xs text-gray-500 mb-3">Tentukan apakah kelas ini hanya sekali atau berulang secara mingguan.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="booking-choice-card flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer">
                                    <input type="radio" name="schedule_frequency" id="schedule-frequency-once" value="once"
                                        class="mt-1 w-4 h-4 text-yellow-700 focus:ring-yellow-500"
                                        {{ old('schedule_frequency', 'once') === 'once' ? 'checked' : '' }}>
                                    <span class="ml-3">
                                        <span class="block text-gray-800 font-semibold">Sekali</span>
                                        <span class="block text-xs text-gray-500 mt-1">Hanya memakai tanggal dan waktu yang dipilih.</span>
                                    </span>
                                </label>
                                <label class="booking-choice-card flex items-start p-4 border-2 border-gray-200 rounded-xl cursor-pointer">
                                    <input type="radio" name="schedule_frequency" id="schedule-frequency-multiple" value="multiple"
                                        class="mt-1 w-4 h-4 text-yellow-700 focus:ring-yellow-500"
                                        {{ old('schedule_frequency') === 'multiple' ? 'checked' : '' }}>
                                    <span class="ml-3">
                                        <span class="block text-gray-800 font-semibold">Berulang setiap minggu</span>
                                        <span class="block text-xs text-gray-500 mt-1">Pilih hari dan jumlah total pertemuan.</span>
                                    </span>
                                </label>
                            </div>
                        </fieldset>

                        <div id="recurrence-days-section" class="hidden mt-5 border-t border-gray-100 pt-5">
                            <fieldset>
                                <legend class="block text-gray-800 text-sm font-bold mb-2">Hari pertemuan</legend>
                                <p class="text-xs text-gray-500 mb-3">Boleh memilih lebih dari satu hari. Tanggal mulai harus termasuk salah satu hari yang dipilih.</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                                        <label class="booking-choice-card flex items-center justify-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer text-sm">
                                            <input type="checkbox" name="recurrence_days[]" value="{{ $day }}" data-day="{{ $day }}"
                                                class="w-4 h-4 text-yellow-700 rounded focus:ring-yellow-500"
                                                {{ in_array($day, (array) old('recurrence_days', []), true) ? 'checked' : '' }} disabled>
                                            <span class="ml-2 text-gray-700 font-medium">{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p id="recurrence-days-hint" class="text-xs text-gray-500 mt-2"></p>
                            </fieldset>

                            <div id="nonfixed-repeat-count-section" class="hidden mt-5">
                                <label for="nonfixed_repeat_count" class="block text-gray-800 text-sm font-bold mb-2">Jumlah total pertemuan</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="repeat_count" id="nonfixed_repeat_count" min="2" max="60"
                                        value="{{ old('repeat_count', 6) }}" disabled
                                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <span class="text-sm text-gray-600">pertemuan (maksimal 60)</span>
                                </div>
                                <p id="nonfixed-frequency-hint" class="text-xs text-gray-500 mt-2"></p>
                            </div>
                        </div>
                    </div>

                    <div class="booking-action-bar flex flex-col-reverse md:flex-row justify-between gap-3 md:gap-0">
                        <button type="button" id="btn-prev-3" class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors text-sm md:text-base">
                            Kembali
                        </button>
                        <button type="button" id="btn-next-3" class="w-full md:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base" disabled>
                            Lanjutkan
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Upload Dokumen & Submit -->
                <div id="step-4" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">4</span>
                        Tinjau & Kirim Pengajuan
                    </h3>
                    <p class="section-intro">Pastikan semua data sudah benar. Setelah dikirim, pengajuan akan menunggu pemeriksaan admin.</p>

                    <div class="mb-6" id="upload-document-section">
                            <span class="block text-gray-700 text-sm font-semibold mb-2">
                                Upload Dokumen Pendukung (KTM, Surat Lainnya) <span class="text-red-500">*</span>
                            </span>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-yellow-500 transition-colors">
                            <input type="file" name="document" id="document" accept=".pdf" class="sr-only" required>
                            <label for="document" class="cursor-pointer">
                                <svg class="mx-auto mb-2 h-9 w-9 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A3.375 3.375 0 0011.25 11.625v5.25A2.625 2.625 0 0013.875 19.5h1.5A4.125 4.125 0 0019.5 15.375v-1.125zM8.25 15.75V5.625A3.375 3.375 0 0111.625 2.25h1.5A3.375 3.375 0 0116.5 5.625v.75" /></svg>
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
                    <div id="capacity-warning-text" role="alert" class="hidden mb-6 bg-red-50 border-2 border-red-500 rounded-lg p-5">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-red-900 font-bold text-lg mb-2">Peringatan kapasitas</h3>
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
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 md:p-6 mb-6">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <h4 class="font-bold text-gray-800">Ringkasan pengajuan</h4>
                                <p class="field-help mt-1">Periksa tanggal, waktu, lab, dan identitas sebelum mengirim.</p>
                            </div>
                            <span class="hidden sm:inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800">Siap ditinjau</span>
                        </div>
                        <div id="booking-summary" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm"></div>
                    </div>

                    <div class="booking-action-bar flex flex-col-reverse md:flex-row justify-between gap-3 md:gap-0">
                        <button type="button" id="btn-prev-4" class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors text-sm md:text-base">
                            Kembali
                        </button>
                        <button type="submit" id="btn-submit" disabled class="w-full md:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-6 md:px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm md:text-base">
                            Ajukan peminjaman
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
                        Kembali ke pilihan tipe
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
                            <label for="pb-nim" class="block text-gray-700 text-sm font-semibold mb-2">NIM <span class="text-red-500">*</span></label>
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
                                <label for="personal-pic-name" class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="pic_name" id="personal-pic-name" required
                                    pattern="[a-zA-Z\s\.']+"
                                    oninput="this.value = this.value.replace(/[^a-zA-Z\s\.']/g, '')"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Masukkan nama lengkap">
                            </div>
                            <div>
                                <label for="personal-nip" class="block text-gray-700 text-sm font-semibold mb-2">NIP <span class="text-red-500">*</span></label>
                                <input type="text" name="nip" id="personal-nip" required
                                    maxlength="30"
                                    placeholder="Masukkan NIP"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 30)"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="personal-phone-number" class="block text-gray-700 text-sm font-semibold mb-2">Nomor Telepon <span class="text-red-500">*</span></label>
                                <input type="tel" name="phone_number" id="personal-phone-number" required
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

        <details class="booking-card mt-5 p-4 md:p-5 text-sm text-slate-600">
            <summary class="cursor-pointer font-semibold text-slate-800 focus:outline-none">Butuh bantuan mengisi formulir?</summary>
            <div class="mt-3 space-y-2 leading-relaxed">
                <p><strong>Perkuliahan Tetap</strong> digunakan untuk jadwal rutin mingguan. Tentukan jumlah pertemuan atau tanggal berakhirnya.</p>
                <p><strong>Perkuliahan Tidak Tetap</strong> dapat diajukan sekali atau berulang mingguan dengan beberapa hari dan jumlah pertemuan.</p>
                <p>Pilih slot kosong di kalender atau isi tanggal dan jam melalui kolom manual. Kalender menampilkan slot terisi tanpa membuka detail peminjam lain.</p>
                <p>Setelah dikirim, simpan token pelacakan pada halaman konfirmasi untuk memeriksa status pengajuan.</p>
            </div>
        </details>

        <!-- Back to Home -->
        <div class="text-center mt-8">
            <a href="{{ route('landing') }}" class="text-gray-600 hover:text-gray-800 font-medium">
                Kembali ke beranda
            </a>
        </div>
    </main>

    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let selectedBookingType = '';
        let selectedLabId = '';
        let availabilityController = null;
        let availabilityRequestId = 0;

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
            setupOnBehalfLecturerToggle();

            setupRealtimeValidation();
            preventEnterSubmit();
            setupSubmitFeedback();
            setupInitialFormState();
        });

        function setupSubmitFeedback() {
            const forms = [
                document.getElementById('bookingForm'),
                document.getElementById('mahasiswaForm'),
                document.getElementById('nonMahasiswaForm')
            ].filter(Boolean);

            forms.forEach(form => form.addEventListener('submit', () => {
                const submitButton = form.querySelector('button[type="submit"]');
                if (!submitButton) return;

                submitButton.disabled = true;
                submitButton.setAttribute('aria-busy', 'true');
                submitButton.dataset.originalLabel = submitButton.textContent.trim();
                submitButton.textContent = 'Mengirim pengajuan...';
            }));
        }

        function setupInitialFormState() {
            const main = document.querySelector('main[data-error-step]');
            const errorSummary = document.getElementById('booking-error-summary');

            if (!main) return;

            // Restore the relevant step after a server-side validation redirect.
            if (selectedBookingType === 'pribadi' && errorSummary) {
                document.getElementById('step-1').classList.add('hidden');
                document.getElementById('personal-borrowing-section').classList.remove('hidden');
                document.querySelector('.step-indicator')?.classList.add('hidden');
                document.getElementById('step-progress-status')?.classList.add('hidden');

                const personalType = main.dataset.personalSubType;
                if (personalType === 'mahasiswa') {
                    document.getElementById('mahasiswaForm')?.classList.remove('hidden');
                    document.getElementById('btn-mahasiswa')?.classList.add('border-blue-500', 'bg-blue-50');
                } else if (personalType === 'non_mahasiswa') {
                    document.getElementById('nonMahasiswaForm')?.classList.remove('hidden');
                    document.getElementById('btn-non-mahasiswa')?.classList.add('border-purple-500', 'bg-purple-50');
                }
            } else if (errorSummary) {
                const errorStep = Number(main.dataset.errorStep || 1);
                if (errorStep > 1 && errorStep <= totalSteps) {
                    goToStep(errorStep, { scroll: false, focus: false });
                }
            }

            errorSummary?.focus({ preventScroll: true });
        }

        function getLecturerInvolvement() {
            return document.querySelector('input[name="lecturer_involvement"]:checked')?.value || 'none';
        }

        function syncLecturerFlags() {
            const involvement = selectedBookingType === 'non_perkuliahan' ? getLecturerInvolvement() : 'none';
            const onBehalfInput = document.getElementById('is_on_behalf_lecturer');
            const bimbinganInput = document.getElementById('is_bimbingan_dosen');

            if (onBehalfInput) onBehalfInput.value = involvement === 'on_behalf' ? '1' : '0';
            if (bimbinganInput) bimbinganInput.value = involvement === 'bimbingan' ? '1' : '0';

            return involvement;
        }

        // Bimbingan Dosen Toggle
        function setupBimbinganDosenToggle() {
            const bimbinganInput = document.getElementById('is_bimbingan_dosen');
            const radios = document.querySelectorAll('input[name="lecturer_involvement"]');
            if (!bimbinganInput || !radios.length || bimbinganInput._bimbinganToggleBound) return;
            bimbinganInput._bimbinganToggleBound = true;

            function toggleBimbinganFields() {
                const isChecked = syncLecturerFlags() === 'bimbingan';
                const nonBimbinganFields = document.getElementById('non-bimbingan-fields');
                const bimbinganFields = document.getElementById('bimbingan-dosen-fields');
                const activityTypeSelect = document.getElementById('activity_type');
                const positionInput = document.getElementById('position');
                const lecturerNameInput = document.getElementById('bimbingan_lecturer_name');
                const lecturerNipInput = document.getElementById('bimbingan_lecturer_nip');

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
                }
                // Re-validate step 2
                validateStep2();
            }

            radios.forEach(radio => radio.addEventListener('change', toggleBimbinganFields));
            // Also listen to bimbingan dosen input fields for validation
            const lecturerNameInput = document.getElementById('bimbingan_lecturer_name');
            const lecturerNipInput = document.getElementById('bimbingan_lecturer_nip');
            if (lecturerNameInput) lecturerNameInput.addEventListener('input', validateStep2);
            if (lecturerNipInput) lecturerNipInput.addEventListener('input', validateStep2);
            // Run on load for old() values
            toggleBimbinganFields();
            window.applyBimbinganDosenState = toggleBimbinganFields;
        }

        function setupOnBehalfLecturerToggle() {
            const onBehalfInput = document.getElementById('is_on_behalf_lecturer');
            const radios = document.querySelectorAll('input[name="lecturer_involvement"]');
            if (!onBehalfInput || !radios.length || onBehalfInput._onBehalfLecturerBound) return;
            onBehalfInput._onBehalfLecturerBound = true;

            const nimField = document.getElementById('nim-field');
            const nimInput = document.getElementById('nim');
            const nipField = document.getElementById('nip-field');
            const nipInput = document.getElementById('nip');
            const nimLabel = document.getElementById('nim-label-text');
            const nimHint = document.getElementById('nim-hint');
            const nipLabel = document.getElementById('nip-label-text');
            const nipHint = document.getElementById('nip-hint');

            function toggleOnBehalfLecturer() {
                const isOnBehalfLecturer = selectedBookingType === 'non_perkuliahan'
                    && syncLecturerFlags() === 'on_behalf';

                if (isOnBehalfLecturer) {
                    nimField.style.display = 'none';
                    nimInput.removeAttribute('required');
                    nimInput.value = '';

                    nipField.style.display = 'block';
                    nipInput.setAttribute('name', 'nip');
                    nipInput.setAttribute('required', 'required');

                    if (nimLabel) nimLabel.textContent = 'NIM Mahasiswa';
                    if (nimHint) nimHint.textContent = 'NIM tidak diperlukan saat peminjaman atas nama dosen.';
                    if (nipLabel) nipLabel.textContent = 'NIP Dosen Pengaju';
                    if (nipHint) nipHint.textContent = 'Masukkan NIP dosen (18 digit angka).';

                } else {
                    nimField.style.display = 'block';
                    nimInput.setAttribute('required', 'required');

                    nipField.style.display = 'none';
                    nipInput.removeAttribute('name');
                    nipInput.removeAttribute('required');
                    nipInput.value = '';

                    if (nimLabel) nimLabel.textContent = 'NIM';
                    if (nimHint) nimHint.textContent = 'NIM harus 14 digit angka';
                    if (nipLabel) nipLabel.textContent = 'NIP';
                    if (nipHint) nipHint.textContent = 'NIP harus 18 digit angka';

                }

                validateStep2();
            }

            radios.forEach(radio => radio.addEventListener('change', toggleOnBehalfLecturer));
            if (nimInput) nimInput.addEventListener('input', validateStep2);
            if (nipInput) nipInput.addEventListener('input', validateStep2);

            toggleOnBehalfLecturer();
            window.applyOnBehalfLecturerState = toggleOnBehalfLecturer;
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
                     document.getElementById('step-progress-status')?.classList.add('hidden');

                } else {
                     // For others, unit type is required
                     document.getElementById('unit-selection').classList.remove('hidden');
                     unitTypeInputs.forEach(input => input.disabled = false);
                     document.getElementById('btn-next-1').disabled = !(bookingType && unitType);
                     // Show step indicator bar
                     document.querySelector('.step-indicator').classList.remove('hidden');
                     document.getElementById('step-progress-status')?.classList.remove('hidden');
                }

                if (bookingType) {
                    selectedBookingType = bookingType.value;
                    const lecturerField = document.getElementById('lecturer-involvement-field');
                    const noneLecturerRadio = document.querySelector('input[name="lecturer_involvement"][value="none"]');

                    if (selectedBookingType === 'non_perkuliahan') {
                        lecturerField?.classList.remove('hidden');
                    } else {
                        lecturerField?.classList.add('hidden');
                        if (noneLecturerRadio) noneLecturerRadio.checked = true;
                    }
                    syncLecturerFlags();

                    // Show/hide appropriate fields for step 2
                    if (selectedBookingType === 'non_perkuliahan') {
                        document.getElementById('perkuliahan-fields').classList.add('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.remove('hidden');

                        setRequiredFields('perkuliahan-fields', false);
                        setRequiredFields('non-perkuliahan-fields', true);
                        if (typeof window.applyOnBehalfLecturerState === 'function') {
                            window.applyOnBehalfLecturerState();
                        }
                        if (typeof window.applyBimbinganDosenState === 'function') {
                            window.applyBimbinganDosenState();
                        }
                    } else if (selectedBookingType === 'pribadi') {
                        // Pribadi uses a separate section, hide regular step 2 fields
                        document.getElementById('perkuliahan-fields').classList.add('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.add('hidden');

                        setRequiredFields('perkuliahan-fields', false);
                        setRequiredFields('non-perkuliahan-fields', false);
                        if (typeof window.applyOnBehalfLecturerState === 'function') {
                            window.applyOnBehalfLecturerState();
                        }
                        if (typeof window.applyBimbinganDosenState === 'function') {
                            window.applyBimbinganDosenState();
                        }
                    } else {
                        document.getElementById('perkuliahan-fields').classList.remove('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.add('hidden');

                        setRequiredFields('perkuliahan-fields', true);
                        setRequiredFields('non-perkuliahan-fields', false);
                        if (typeof window.applyOnBehalfLecturerState === 'function') {
                            window.applyOnBehalfLecturerState();
                        }
                        if (typeof window.applyBimbinganDosenState === 'function') {
                            window.applyBimbinganDosenState();
                        }
                    }

                    // Toggle lab selection based on booking type
                    toggleLabSelection();

                    // Update recurring booking notice based on booking type
                    updateRecurrenceControls();
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
            const optionalFields = ['software_needs', 'equipment_needs', 'is_bimbingan_dosen', 'is_on_behalf_lecturer']; // Fields that are always optional
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

                isValid = isValid && prodi && nim.length === 14 && mataKuliah && dosen && nip.length === 18;
            } else if (selectedBookingType === 'non_perkuliahan') {
                const prodi = document.getElementById('study_program').value.trim();
                const nim = document.getElementById('nim').value.trim();
                const nip = document.getElementById('nip').value.trim();
                const namaKegiatan = document.getElementById('activity_name').value.trim();
                const lecturerInvolvement = getLecturerInvolvement();
                const isBimbingan = lecturerInvolvement === 'bimbingan';
                const isOnBehalfLecturer = lecturerInvolvement === 'on_behalf';

                // Check custom study program if 'Lainnya' is selected
                if (prodi === 'Lainnya') {
                    const customProdi = document.getElementById('custom_study_program');
                    isValid = isValid && customProdi && customProdi.value.trim();
                }

                if (isBimbingan) {
                    // Bimbingan dosen: need lecturer name & nip instead of activity_type & position
                    const dosenName = document.getElementById('bimbingan_lecturer_name').value.trim();
                    const dosenNip = document.getElementById('bimbingan_lecturer_nip').value.trim();
                    const identityValid = isOnBehalfLecturer ? nip.length === 18 : nim.length === 14;
                    isValid = isValid && prodi && identityValid && namaKegiatan && dosenName && dosenNip.length === 18;
                } else {
                    // Regular non-perkuliahan
                    const jenisKegiatan = document.getElementById('activity_type').value.trim();
                    const jabatan = document.getElementById('position').value.trim();
                    const identityValid = isOnBehalfLecturer ? nip.length === 18 : nim.length === 14;
                    isValid = isValid && prodi && identityValid && namaKegiatan && jenisKegiatan && jabatan;
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
        function getTimeFieldValue(prefix) {
            const hiddenInput = document.getElementById(`${prefix}_time`);
            const hourSelect = document.getElementById(`${prefix}_hour`);
            const minuteSelect = document.getElementById(`${prefix}_minute`);

            if (hourSelect?.value && minuteSelect?.value) {
                return `${hourSelect.value}:${minuteSelect.value}`;
            }

            if (hiddenInput?.value) return hiddenInput.value;

            return '';
        }

        function syncTimeFieldValue(prefix) {
            const value = getTimeFieldValue(prefix);
            const hiddenInput = document.getElementById(`${prefix}_time`);
            if (hiddenInput && value) {
                hiddenInput.value = value;
                hiddenInput.defaultValue = value;
                hiddenInput.setAttribute('value', value);
            }
            return value;
        }

        function syncBookingTimeFields() {
            return {
                start: syncTimeFieldValue('start'),
                end: syncTimeFieldValue('end'),
            };
        }

        function getSelectedRecurrenceDays() {
            return Array.from(document.querySelectorAll('input[name="recurrence_days[]"]:checked'))
                .map(input => input.value);
        }

        function getActiveRepeatCountInput() {
            return selectedBookingType === 'perkuliahan_tidak_tetap'
                ? document.getElementById('nonfixed_repeat_count')
                : document.getElementById('fixed_repeat_count');
        }

        function updateRecurringBookingNotice() {
            const recurringNoticeGeneric = document.getElementById('recurring-booking-notice-generic');
            const recurringNoticeSpecific = document.getElementById('recurring-booking-notice-specific');
            const recurringDayName = document.getElementById('recurring-day-name');
            const recurringTimeRange = document.getElementById('recurring-time-range');
            const recurringLabName = document.getElementById('recurring-lab-name');
            const bookingDate = document.getElementById('booking_date').value;
            const { start: startTime, end: endTime } = syncBookingTimeFields();
            const labSelect = document.getElementById('labSelect');
            const isNonFixedRecurring = selectedBookingType === 'perkuliahan_tidak_tetap'
                && document.querySelector('input[name="schedule_frequency"]:checked')?.value === 'multiple';
            const isRecurring = selectedBookingType === 'perkuliahan_tetap' || isNonFixedRecurring;

            if (!isRecurring) {
                recurringNoticeGeneric.classList.add('hidden');
                recurringNoticeSpecific.classList.add('hidden');
                return;
            }

            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const start = bookingDate ? new Date(bookingDate + 'T00:00:00') : null;
            const days = selectedBookingType === 'perkuliahan_tetap'
                ? (start ? [dayNames[start.getDay()]] : [])
                : getSelectedRecurrenceDays();

            if (bookingDate && startTime && endTime && labSelect.value && days.length) {
                const selectedLabOption = labSelect.options[labSelect.selectedIndex];
                const labName = selectedLabOption.textContent.split(' (')[0];
                recurringDayName.textContent = days.join(' dan ');
                recurringTimeRange.textContent = startTime + ' - ' + endTime;
                recurringLabName.textContent = labName;
                recurringNoticeGeneric.classList.add('hidden');
                recurringNoticeSpecific.classList.remove('hidden');
            } else {
                recurringNoticeGeneric.classList.remove('hidden');
                recurringNoticeSpecific.classList.add('hidden');
            }
        }

        function updateRecurringEndSection() {
            const section = document.getElementById('recurring-end-section');
            const fixedCountInput = document.getElementById('fixed_repeat_count');
            const repeatEndDate = document.getElementById('repeat_end_date');
            const repeatTypeInputs = document.querySelectorAll('input[name="repeat_type"]');
            const bookingDate = document.getElementById('booking_date').value;
            const hint = document.getElementById('recurring-end-hint');
            const repeatType = document.querySelector('input[name="repeat_type"]:checked');
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const isFixed = selectedBookingType === 'perkuliahan_tetap';

            section.classList.toggle('hidden', !isFixed);
            repeatTypeInputs.forEach(input => input.disabled = !isFixed);
            if (!isFixed) {
                fixedCountInput.disabled = true;
                repeatEndDate.disabled = true;
                hint.textContent = '';
                return;
            }

            const activeRepeatType = repeatType?.value || 'count';
            fixedCountInput.disabled = activeRepeatType !== 'count';
            repeatEndDate.disabled = activeRepeatType !== 'date';

            if (!bookingDate || !repeatType) {
                hint.textContent = '';
                return;
            }

            const start = new Date(bookingDate + 'T00:00:00');
            const dayName = dayNames[start.getDay()];
            if (repeatType.value === 'count') {
                const count = parseInt(fixedCountInput.value);
                hint.textContent = count > 1
                    ? `Pertemuan setiap hari ${dayName}, ${count} kali (terakhir ${formatIndoDate(addWeeks(start, count - 1))}).`
                    : 'Masukkan jumlah pertemuan (minimal 2).';
            } else {
                const endDate = repeatEndDate.value;
                if (!endDate) {
                    hint.textContent = 'Pilih tanggal akhir pengulangan.';
                } else {
                    const end = new Date(endDate + 'T00:00:00');
                    hint.textContent = end < start
                        ? 'Tanggal akhir harus setelah tanggal mulai.'
                        : `Pertemuan setiap hari ${dayName}, hingga ${formatIndoDate(end)}.`;
                }
            }
        }

        function updateNonfixedFrequencySection() {
            const section = document.getElementById('nonfixed-frequency-section');
            const daysSection = document.getElementById('recurrence-days-section');
            const countSection = document.getElementById('nonfixed-repeat-count-section');
            const countInput = document.getElementById('nonfixed_repeat_count');
            const frequencyInputs = document.querySelectorAll('input[name="schedule_frequency"]');
            const dayInputs = document.querySelectorAll('input[name="recurrence_days[]"]');
            const isNonFixed = selectedBookingType === 'perkuliahan_tidak_tetap';
            const frequency = document.querySelector('input[name="schedule_frequency"]:checked')?.value || 'once';
            const isMultiple = isNonFixed && frequency === 'multiple';

            section.classList.toggle('hidden', !isNonFixed);
            frequencyInputs.forEach(input => input.disabled = !isNonFixed);
            daysSection.classList.toggle('hidden', !isMultiple);
            countSection.classList.toggle('hidden', !isMultiple);
            dayInputs.forEach(input => input.disabled = !isMultiple);
            countInput.disabled = !isMultiple;

            if (isMultiple) {
                const bookingDate = document.getElementById('booking_date').value;
                if (bookingDate && !getSelectedRecurrenceDays().length) {
                    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const startDay = dayNames[new Date(bookingDate + 'T00:00:00').getDay()];
                    const matchingDay = Array.from(dayInputs).find(input => input.value === startDay);
                    if (matchingDay) matchingDay.checked = true;
                }

                const selectedDays = getSelectedRecurrenceDays();
                const count = parseInt(countInput.value);
                document.getElementById('recurrence-days-hint').textContent = selectedDays.length
                    ? `Dipilih: ${selectedDays.join(', ')}.`
                    : 'Pilih minimal satu hari pertemuan.';
                document.getElementById('nonfixed-frequency-hint').textContent = count >= 2
                    ? `Sistem akan membuat ${count} occurrence pada hari yang dipilih.`
                    : 'Jumlah minimal adalah 2 pertemuan.';
            } else {
                document.getElementById('recurrence-days-hint').textContent = '';
                document.getElementById('nonfixed-frequency-hint').textContent = '';
            }
        }

        function updateRecurrenceControls() {
            updateRecurringEndSection();
            updateNonfixedFrequencySection();
            updateRecurringBookingNotice();
        }

        function addWeeks(date, weeks) {
            const d = new Date(date);
            d.setDate(d.getDate() + weeks * 7);
            return d;
        }

        function formatIndoDate(date) {
            const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
        }

        function setupStep3Validation() {
            const bookingDate = document.getElementById('booking_date');
            const participantCount = document.getElementById('participant_count');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            const labSelect = document.getElementById('labSelect');
            selectedLabId = labSelect.value;

            [bookingDate, participantCount, startTime, endTime].forEach(field => {
                field.addEventListener('change', function() {
                    if (selectedBookingType !== 'pribadi') {
                        fetchAvailableLabs();
                        checkCapacityWarning();
                    }
                    updateRecurrenceControls();
                    validateStep3();
                });
            });

            labSelect.addEventListener('change', function() {
                selectedLabId = labSelect.value;
                validateStep3();
                checkCapacityWarning();
                updateRecurrenceControls();
            });

            const repeatControls = document.querySelectorAll('#recurring-end-section input, #nonfixed-frequency-section input');
            repeatControls.forEach(input => {
                input.addEventListener('change', function() {
                    updateRecurrenceControls();
                    if (selectedBookingType === 'perkuliahan_tidak_tetap') fetchAvailableLabs();
                    validateStep3();
                });
                input.addEventListener('input', function() {
                    updateRecurrenceControls();
                    if (selectedBookingType === 'perkuliahan_tidak_tetap') fetchAvailableLabs();
                    validateStep3();
                });
            });
        }
        function validateStep3() {
            const bookingDate = document.getElementById('booking_date').value;
            const participantCount = document.getElementById('participant_count').value;
            const { start: startTime, end: endTime } = syncBookingTimeFields();
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

            // Validate recurrence controls for both lecture variants.
            let repeatValid = true;
            if (selectedBookingType === 'perkuliahan_tetap') {
                const repeatType = document.querySelector('input[name="repeat_type"]:checked');
                const repeatCount = document.getElementById('fixed_repeat_count').value;
                const repeatEndDate = document.getElementById('repeat_end_date').value;
                if (repeatType && repeatType.value === 'count') {
                    repeatValid = parseInt(repeatCount) >= 2;
                } else if (repeatType && repeatType.value === 'date') {
                    repeatValid = !!repeatEndDate && repeatEndDate >= bookingDate;
                }
            } else if (selectedBookingType === 'perkuliahan_tidak_tetap') {
                const frequency = document.querySelector('input[name="schedule_frequency"]:checked')?.value;
                if (frequency === 'multiple') {
                    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const startDay = bookingDate ? dayNames[new Date(bookingDate + 'T00:00:00').getDay()] : '';
                    const count = parseInt(document.getElementById('nonfixed_repeat_count').value);
                    const selectedDays = getSelectedRecurrenceDays();
                    repeatValid = count >= 2 && selectedDays.length > 0 && selectedDays.includes(startDay);
                }
            }

            const isValid = bookingDate && participantCount && startTime && endTime && (isPribadi || lab) && !isSunday && isTimeValid && repeatValid;
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
                selectedLabId = '';
                labSelect.dispatchEvent(new Event('change', { bubbles: true }));
                labSelect.removeAttribute('required');
                document.getElementById('booking-calendar-panel')?.classList.add('hidden');
            } else {
                // Show lab selection for other types
                labContainer.classList.remove('hidden');
                // The same field is used by the calendar and the submission.
                // It must be usable before date/time details are complete.
                labSelect.disabled = false;
                labSelect.setAttribute('required', 'required');
                document.getElementById('booking-calendar-panel')?.classList.remove('hidden');
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
            const { start: startTime, end: endTime } = syncBookingTimeFields();

            if (!bookingDate || !participantCount || !startTime || !endTime) return;

            const labSelect = document.getElementById('labSelect');
            const availabilityStatus = document.getElementById('lab-availability-status');
            // Preserve the current choice while availability is recalculated.
            const preferredLabId = selectedLabId || labSelect.value;

            availabilityController?.abort();
            availabilityController = new AbortController();
            const requestId = ++availabilityRequestId;
            labSelect.innerHTML = '<option value="">Memuat...</option>';
            labSelect.disabled = true;
            if (availabilityStatus) {
                availabilityStatus.textContent = 'Memeriksa ketersediaan laboratorium...';
                availabilityStatus.className = 'booking-loading text-sm mt-2';
            }

            fetch('{{ route("booking.available-labs") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                signal: availabilityController.signal,
                body: JSON.stringify({
                    booking_date: bookingDate,
                    participant_count: participantCount,
                    start_time: startTime,
                    end_time: endTime,
                    booking_type: selectedBookingType,
                    schedule_frequency: document.querySelector('input[name="schedule_frequency"]:checked')?.value || null,
                    recurrence_days: getSelectedRecurrenceDays(),
                    repeat_count: getActiveRepeatCountInput()?.value || null
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(labs => {
                if (requestId !== availabilityRequestId) return;
                labSelect.innerHTML = '';
                if (labs.length === 0) {
                    labSelect.innerHTML = '<option value="">Tidak ada lab tersedia</option>';
                    labSelect.disabled = true;
                    selectedLabId = '';
                    if (availabilityStatus) {
                        availabilityStatus.textContent = 'Tidak ada laboratorium yang tersedia pada kombinasi tanggal, jam, dan jumlah peserta ini. Coba ubah salah satu pilihan.';
                        availabilityStatus.className = 'text-red-700 text-sm mt-2';
                    }
                } else {
                    labSelect.innerHTML = '<option value="">-- Pilih Laboratorium --</option>';
                    labs.forEach(lab => {
                        const option = document.createElement('option');
                        option.value = lab.id;
                        // Store capacity in data attribute
                        option.dataset.capacity = lab.capacity;

                        const isUnderCapacity = lab.capacity < parseInt(participantCount);
                        const warningIcon = isUnderCapacity ? 'Kapasitas kurang — ' : '';
                        const warningText = isUnderCapacity ? ' (Kapasitas Kurang)' : '';

                        option.textContent = `${warningIcon}${lab.name} (Kap: ${lab.capacity})${warningText}`;
                        labSelect.appendChild(option);
                    });
                    labSelect.disabled = false;
                    const preferredLabIsAvailable = preferredLabId
                        && Array.from(labSelect.options).some(option => option.value === String(preferredLabId));
                    if (preferredLabIsAvailable) {
                        labSelect.value = String(preferredLabId);
                        labSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (availabilityStatus) {
                        availabilityStatus.textContent = preferredLabIsAvailable
                            ? `${labs.length} laboratorium tersedia. Pilihan Anda tetap dipertahankan.`
                            : `${labs.length} laboratorium tersedia. Pilih salah satu untuk melanjutkan.`;
                        availabilityStatus.className = 'text-green-700 text-sm mt-2';
                    }
                }
            })
            .catch(error => {
                if (error.name === 'AbortError' || requestId !== availabilityRequestId) return;
                console.error('Error:', error);
                labSelect.innerHTML = '<option value="">Gagal memuat data lab</option>';
                labSelect.disabled = true;
                if (availabilityStatus) {
                    availabilityStatus.textContent = 'Ketersediaan lab belum dapat dimuat. Periksa koneksi lalu ubah tanggal atau jam untuk mencoba lagi.';
                    availabilityStatus.className = 'text-red-700 text-sm mt-2';
                }
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
                // The visible hour/minute controls are the user-facing source
                // of truth. Sync them before building the summary and before
                // the final form submission can happen.
                syncBookingTimeFields();
                generateSummary();
                goToStep(4);
            });

            document.getElementById('btn-submit').addEventListener('click', () => {
                // Click runs before the browser's native form validation.
                // This guarantees the named hidden fields contain the values
                // selected in the custom time controls.
                syncBookingTimeFields();
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

        function goToStep(step, options = {}) {
            // Check visibility if entering step 4
            if (step === 4) {
                updateUploadVisibility();
            }

            // Hide current step
            document.getElementById(`step-${currentStep}`).classList.add('hidden');

            // Show new step
            document.getElementById(`step-${step}`).classList.remove('hidden');
            document.getElementById(`step-${step}`).classList.remove('step-disabled');

            if (step === 3) {
                window.dispatchEvent(new CustomEvent('booking-step-3-visible'));
            }

            currentStep = step;
            updateStepIndicator();

            if (options.scroll !== false) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            if (options.focus !== false) {
                const firstField = document.querySelector(`#step-${step} input:not([type="hidden"]), #step-${step} select, #step-${step} textarea, #step-${step} button`);
                firstField?.focus({ preventScroll: true });
            }
        }

        function updateStepIndicator() {
            const stepLabels = { 1: 'Tipe', 2: 'Data pengaju', 3: 'Jadwal', 4: 'Tinjau' };
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step-indicator-${i}`);
                if (i < currentStep) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('active');
                    indicator.removeAttribute('aria-current');
                } else if (i === currentStep) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                    indicator.setAttribute('aria-current', 'step');
                } else {
                    indicator.classList.remove('active', 'completed');
                    indicator.removeAttribute('aria-current');
                }
            }

            const progress = document.getElementById('step-progress-status');
            if (progress) {
                progress.textContent = `Langkah ${currentStep} dari ${totalSteps}: ${stepLabels[currentStep]}`;
            }
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character]));
        }

        function summaryRow(label, value) {
            return `<div class="border-b border-slate-200 pb-2"><dt class="text-xs text-slate-500">${escapeHtml(label)}</dt><dd class="font-semibold text-slate-800 break-words">${escapeHtml(value || '-')}</dd></div>`;
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
            summary.push(summaryRow('Tipe', bookingTypeLabels[selectedBookingType]));

            // Add Unit if not pribadi
             if (selectedBookingType !== 'pribadi') {
                 const unitLabels = {
                     's1_tembalang': 'S1 Tembalang',
                     'pascasarjana_pleburan': 'Pascasarjana Pleburan'
                 };
                 const unit = document.querySelector('input[name="unit_type"]:checked')?.value;
                 summary.push(summaryRow('Unit', unitLabels[unit] || unit));
             }

            summary.push(summaryRow('Nama', document.getElementById('pic_name').value));

            // Display NIP or NIM based on what's filled
            const nimValue = document.getElementById('nim').value;
            const nipValue = document.getElementById('nip').value;
            const isOnBehalfLecturer = selectedBookingType === 'non_perkuliahan'
                && getLecturerInvolvement() === 'on_behalf';

            if (isOnBehalfLecturer) {
                summary.push(summaryRow('Atas nama dosen', 'Ya'));
                summary.push(summaryRow('NIP', nipValue));
            } else if (nipValue) {
                summary.push(summaryRow('NIP', nipValue));
            } else if (nimValue) {
                summary.push(summaryRow('NIM', nimValue));
            }

            // Specific fields based on type
             if (selectedBookingType === 'pribadi') {
                const status = document.getElementById('applicant_status').value;
                let statusDisplay = status;

                // Jika status adalah Lainnya, gunakan custom status
                if (status === 'Lainnya') {
                    statusDisplay = document.getElementById('custom_status').value;
                }

                summary.push(summaryRow('Status', statusDisplay));
                if (status === 'Mahasiswa') {
                    summary.push(summaryRow('Angkatan', document.getElementById('class_year').value));
                }
                summary.push(summaryRow('Keperluan', document.getElementById('purpose').value));
            } else if (selectedBookingType === 'non_perkuliahan') {
                summary.push(summaryRow('Kegiatan', document.getElementById('activity_name').value));
                const isBimbingan = getLecturerInvolvement() === 'bimbingan';
                if (isBimbingan) {
                    summary.push(summaryRow('Bimbingan dosen', 'Ya'));
                    summary.push(summaryRow('Nama dosen', document.getElementById('bimbingan_lecturer_name').value));
                    summary.push(summaryRow('NIP dosen', document.getElementById('bimbingan_lecturer_nip').value));
                }
            } else {
                 summary.push(summaryRow('Mata kuliah', document.getElementById('course_name').value));
            }

            summary.push(summaryRow('Tanggal', formatIndoDate(new Date(`${document.getElementById('booking_date').value}T00:00:00`))));
            const { start: summaryStartTime, end: summaryEndTime } = syncBookingTimeFields();
            summary.push(summaryRow('Waktu', `${summaryStartTime} - ${summaryEndTime} WIB`));
            summary.push(summaryRow('Peserta', `${document.getElementById('participant_count').value} orang`));

            // Recurrence display for both lecture variants.
            if (selectedBookingType === 'perkuliahan_tetap') {
                const repeatType = document.querySelector('input[name="repeat_type"]:checked');
                if (repeatType) {
                    let endText = 'Berulang tanpa batas';
                    if (repeatType.value === 'count') {
                        const count = parseInt(document.getElementById('fixed_repeat_count').value);
                        endText = `Berulang, ${count} kali pertemuan`;
                    } else if (repeatType.value === 'date') {
                        endText = `Berulang hingga ${document.getElementById('repeat_end_date').value}`;
                    }
                    summary.push(summaryRow('Pengulangan', endText));
                }
            } else if (selectedBookingType === 'perkuliahan_tidak_tetap') {
                const frequency = document.querySelector('input[name="schedule_frequency"]:checked')?.value;
                if (frequency === 'multiple') {
                    const count = parseInt(document.getElementById('nonfixed_repeat_count').value);
                    summary.push(summaryRow('Pengulangan', `Setiap minggu pada ${getSelectedRecurrenceDays().join(', ')}, ${count} kali pertemuan`));
                } else {
                    summary.push(summaryRow('Pengulangan', 'Sekali'));
                }
            }

            // Lab and capacity warning only shown for non-pribadi bookings
            const warningBox = document.getElementById('capacity-warning-text');
            if (selectedBookingType !== 'pribadi') {
                const labSelect = document.getElementById('labSelect');
                const labName = labSelect.options[labSelect.selectedIndex]?.text;
                summary.push(summaryRow('Laboratorium', labName));

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
                        errorMsg = 'Format file harus PDF. File yang dipilih: ' + file.name.split('.').pop().toUpperCase();
                    } else if (file.size > maxSize) {
                        errorMsg = 'Ukuran file maksimal 5MB. File yang dipilih: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
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
                    fileNameDisplay.textContent = `Dipilih: ${file.name} (${fileSizeMB} MB)`;
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
                    const value = syncTimeFieldValue(prefix);

                    // Trigger change event manually for availability and validation.
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
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

        // FullCalendar writes the visible time controls programmatically.
        // Reconcile the named form fields after that interaction as well.
        document.addEventListener('booking-calendar:time-selected', (event) => {
            const selectedTimes = event.detail || {};

            ['start', 'end'].forEach(prefix => {
                const value = selectedTimes[prefix];
                const hiddenInput = document.getElementById(`${prefix}_time`);
                if (!hiddenInput || !value) return;

                hiddenInput.value = value;
                hiddenInput.defaultValue = value;
                hiddenInput.setAttribute('value', value);
            });

            syncBookingTimeFields();
        });

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
            setupBimbinganDosenToggle();
            setupOnBehalfLecturerToggle();

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

            const customTrigger = input.closest('.custom-select-wrapper')?.querySelector('.custom-select-trigger');
            customTrigger?.classList.add('border-red-500');
            customTrigger?.setAttribute('aria-invalid', 'true');

            // Add error message text
            let parent = input.parentElement;
            let errorText = parent.querySelector('.validation-error-msg');

            if (!errorText) {
                errorText = document.createElement('p');
                errorText.className = 'validation-error-msg text-red-600 text-xs mt-1 font-medium';
                errorText.setAttribute('role', 'alert');
                parent.appendChild(errorText);
            }
            errorText.textContent = message;
        }

        function clearError(input) {
            // Restore normal border
            input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            input.classList.add('border-gray-300', 'focus:ring-yellow-500', 'focus:border-transparent');

            const customTrigger = input.closest('.custom-select-wrapper')?.querySelector('.custom-select-trigger');
            customTrigger?.classList.remove('border-red-500');
            customTrigger?.removeAttribute('aria-invalid');

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
                if (this.originalSelect.dataset.customSelectReady) return;
                this.originalSelect.dataset.customSelectReady = 'true';
                this.originalSelect.classList.add('custom-select-native');
                this.originalSelect.setAttribute('aria-hidden', 'true');
                this.originalSelect.tabIndex = -1;
                this.activeIndex = 0;

                // Create wrapper
                this.wrapper = document.createElement('div');
                this.wrapper.className = 'custom-select-wrapper w-full';
                this.originalSelect.parentNode.insertBefore(this.wrapper, this.originalSelect);
                this.wrapper.appendChild(this.originalSelect); // Move original inside

                // Create Trigger Element
                this.trigger = document.createElement('button');
                this.trigger.type = 'button';
                this.trigger.className = 'custom-select-trigger';
                this.trigger.setAttribute('aria-haspopup', 'listbox');
                this.trigger.setAttribute('aria-expanded', 'false');
                const label = document.querySelector(`label[for="${CSS.escape(this.originalSelect.id)}"]`);
                if (label) {
                    label.id ||= `${this.originalSelect.id}-label`;
                    this.trigger.setAttribute('aria-labelledby', label.id);
                } else {
                    this.trigger.setAttribute('aria-label', this.originalSelect.getAttribute('aria-label') || this.originalSelect.name || 'Pilih opsi');
                }

                // Content span
                this.triggerLabel = document.createElement('span');
                this.triggerLabel.className = 'block truncate';

                // Chevron icon
                const chevron = document.createElement('div');
                chevron.innerHTML = `<svg class="w-5 h-5 text-gray-400 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
                this.chevronIcon = chevron.firstElementChild;
                this.chevronIcon.classList.add('custom-select-chevron');

                this.trigger.appendChild(this.triggerLabel);
                this.trigger.appendChild(chevron);
                this.wrapper.appendChild(this.trigger);

                // Create Options Container
                this.optionsContainer = document.createElement('div');
                this.optionsContainer.className = 'custom-select-options hidden';
                this.optionsContainer.id = `${this.originalSelect.id || `select-${Date.now()}`}-options`;
                this.optionsContainer.setAttribute('role', 'listbox');
                this.trigger.setAttribute('aria-controls', this.optionsContainer.id);
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

                this.trigger.addEventListener('keydown', (event) => this.handleTriggerKeydown(event));
                this.optionsContainer.addEventListener('keydown', (event) => this.handleOptionKeydown(event));

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

                // Calendar selections update the underlying value directly;
                // refresh only the custom trigger without running form logic.
                this.originalSelect.addEventListener('custom-select:sync', () => {
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
                    optionDiv.className = 'custom-select-option';
                    optionDiv.setAttribute('role', 'option');
                    optionDiv.setAttribute('aria-selected', option.selected ? 'true' : 'false');
                    optionDiv.dataset.index = String(option.index);
                    optionDiv.tabIndex = -1;

                    // Allow html content for warnings (lab capacity icons)
                    // But usually option.text is plain.
                    // In fetchAvailableLabs, we put emoji in textContent. So textContent is safe.
                    optionDiv.textContent = option.text;

                    // Handle disabled options (like headers "Pilih ...")
                    if (option.disabled && option.value === "") {
                        optionDiv.className = 'custom-select-option is-disabled';
                        optionDiv.setAttribute('aria-disabled', 'true');
                    }

                    if (option.selected) {
                        optionDiv.classList.add('is-active');
                        this.activeIndex = option.index;
                    }

                    if (!option.disabled) {
                        optionDiv.addEventListener('click', (e) => {
                            e.stopPropagation();
                            this.originalSelect.value = option.value;
                            this.originalSelect.dispatchEvent(new Event('change'));
                            this.closeDropdown();
                        });

                        optionDiv.addEventListener('mousemove', () => this.setActiveOption(option.index));
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

            selectableOptions() {
                return Array.from(this.originalSelect.options).filter(option => !option.disabled && option.value !== '');
            }

            setActiveOption(index) {
                const target = this.optionsContainer.querySelector(`[data-index="${index}"]`);
                if (!target || target.classList.contains('is-disabled')) return;
                this.optionsContainer.querySelectorAll('.custom-select-option').forEach(option => option.classList.remove('is-active'));
                target.classList.add('is-active');
                this.activeIndex = index;
                target.scrollIntoView({ block: 'nearest' });
            }

            chooseActiveOption() {
                const option = this.originalSelect.options[this.activeIndex];
                if (!option || option.disabled || option.value === '') return;
                this.originalSelect.value = option.value;
                this.originalSelect.dispatchEvent(new Event('change', { bubbles: true }));
                this.closeDropdown();
                this.trigger.focus();
            }

            handleTriggerKeydown(event) {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    if (this.optionsContainer.classList.contains('hidden')) this.toggleDropdown();
                    const options = this.selectableOptions();
                    if (!options.length) return;
                    const current = options.findIndex(option => option.index === this.activeIndex);
                    const next = event.key === 'ArrowDown'
                        ? options[Math.min(current + 1, options.length - 1)]
                        : options[Math.max(current - 1, 0)];
                    this.setActiveOption(next.index);
                } else if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    if (this.optionsContainer.classList.contains('hidden')) this.toggleDropdown();
                    else this.chooseActiveOption();
                } else if (event.key === 'Escape') {
                    this.closeDropdown();
                }
            }

            handleOptionKeydown(event) {
                if (event.key === 'Escape') {
                    this.closeDropdown();
                    this.trigger.focus();
                }
            }

            toggleDropdown() {
                const isHidden = this.optionsContainer.classList.contains('hidden');
                // Close others
                document.querySelectorAll('.custom-select-wrapper .custom-select-options').forEach(el => {
                    if (!el.classList.contains('hidden') && el !== this.optionsContainer) {
                        el.classList.add('hidden');
                        const otherChevron = el.parentElement.querySelector('svg');
                        if(otherChevron) otherChevron.classList.remove('rotate-180');
                    }
                });

                if (isHidden) {
                    this.optionsContainer.classList.remove('hidden');
                    this.trigger.setAttribute('aria-expanded', 'true');
                    this.setActiveOption(this.originalSelect.selectedIndex || this.selectableOptions()[0]?.index);
                } else {
                    this.closeDropdown();
                }
            }

            closeDropdown() {
                this.optionsContainer.classList.add('hidden');
                this.trigger.setAttribute('aria-expanded', 'false');
            }
        }

        // Initialize for Booking Page
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.booking-page select').forEach(select => new CustomSelectEncoded(select));
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
