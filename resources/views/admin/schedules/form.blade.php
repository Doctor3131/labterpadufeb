<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isEdit ? 'Edit' : 'Tambah' }} Jadwal - Laboratorium dan Fasilitas Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 border-b-4 border-yellow-500">
        <div class="container mx-auto px-3 sm:px-4 md:px-6 py-3 md:py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('landing') }}">
                        <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-12 sm:h-14 md:h-16 w-auto object-contain">
                    </a>
                </div>
                <a href="{{ route('admin.schedules.index') }}" class="px-3 sm:px-4 py-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg font-medium text-sm md:text-base">
                    ← <span class="hidden sm:inline">Kembali ke </span>Daftar
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-3 sm:px-4 md:px-6 py-4 md:py-8 max-w-3xl">
        <!-- Header -->
        <div class="mb-4 md:mb-6">
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">{{ $isEdit ? 'Edit' : 'Tambah' }} Jadwal</h1>
            <p class="text-sm md:text-base text-gray-600">Silakan isi form berikut untuk {{ $isEdit ? 'memperbarui' : 'menambahkan' }} jadwal</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg">
                <p class="font-bold">Terjadi Kesalahan:</p>
                <ul class="list-disc list-inside mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Booking Info (if editing booking schedule) -->
        @if($isEdit && $schedule->booking)
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-r-lg">
                <p class="font-bold">Jadwal dari Booking</p>
                <p class="text-sm">Perubahan akan disinkronkan ke data booking terkait (ID: #{{ $schedule->booking_id }})</p>
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 md:p-6">
            <form id="scheduleForm" action="{{ $isEdit ? route('admin.schedules.update', $schedule->id) : route('admin.schedules.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <!-- Day (FIRST - Time selection starts here) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hari *</label>
                        <select name="day" id="daySelect" required class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-base">
                            <option value="">Pilih Hari</option>
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ old('day', $schedule->day ?? '') == $day ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type (Moved up for context) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Jadwal *</label>
                        <select name="type" id="typeSelect" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $schedule->type ?? 'perkuliahan_tetap') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Time -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Mulai *</label>
                        <input type="hidden" name="start_time" id="startTime" required
                               value="{{ old('start_time', $schedule ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '') }}">
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

                    <!-- End Time -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Selesai *</label>
                        <input type="hidden" name="end_time" id="endTime" required
                               value="{{ old('end_time', $schedule ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '') }}">
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
                        <p id="time-error" class="text-xs text-red-500 mt-1 hidden"><strong>* Jam Selesai harus setelah Jam Mulai</strong></p>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDate"
                               value="{{ old('start_date', $schedule && $schedule->start_date ? $schedule->start_date->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="endDate"
                               value="{{ old('end_date', $schedule && $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika berlaku selamanya</p>
                    </div>

                    <!-- Lab (LAST - After time is selected, fetched via AJAX) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Laboratorium *</label>
                        <select name="lab_id" id="labSelect" required class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-base" {{ $isEdit ? '' : 'disabled' }}>
                            @if($isEdit)
                                @foreach($labs as $lab)
                                    <option value="{{ $lab->id }}" data-capacity="{{ $lab->capacity }}" {{ old('lab_id', $schedule->lab_id ?? '') == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->name }} (Kap. {{ $lab->capacity }})
                                    </option>
                                @endforeach
                            @else
                                <option value="">Pilih waktu terlebih dahulu</option>
                            @endif
                        </select>
                    </div>

                    <!-- Student Count -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Mahasiswa *</label>
                        <input type="number" name="student_count" id="student_count" min="1" required
                               value="{{ old('student_count', $schedule->student_count ?? '') }}"
                               placeholder="Wajib diisi"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Conditional Fields Based on Type -->
                
                <!-- Perkuliahan Fields -->
                <div id="perkuliahan-fields" class="hidden mt-6">
                    <h4 class="font-bold text-gray-800 mb-4 text-lg">Data Perkuliahan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Kuliah *</label>
                            <input type="text" name="course_name" id="course_name"
                                   value="{{ old('course_name', $schedule->course ?? '') }}"
                                   placeholder="Contoh: Sistem Informasi Manajemen"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Dosen Pengampu *</label>
                            <input type="text" name="lecturer_name" id="lecturer_name"
                                   value="{{ old('lecturer_name', $schedule->lecturer ?? '') }}"
                                   placeholder="Contoh: Dr. Budi Santoso"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Koordinator / Komting</label>
                            <input type="text" name="komting" id="komting"
                                   value="{{ old('komting', $schedule->komting ?? '') }}"
                                   placeholder="Contoh: Ahmad Faizal"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon Komting</label>
                            <input type="text" name="komting_phone" id="komting_phone"
                                   value="{{ old('komting_phone', $schedule->komting_phone ?? '') }}"
                                   placeholder="Contoh: 08123456789"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Non-Perkuliahan Fields -->
                <div id="non-perkuliahan-fields" class="hidden mt-6">
                    <h4 class="font-bold text-gray-800 mb-4 text-lg">Data Kegiatan Non-Perkuliahan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kegiatan *</label>
                            <input type="text" name="activity_name" id="activity_name"
                                   value="{{ old('activity_name', $isEdit ? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_name : ($schedule->type === 'non_perkuliahan' ? $schedule->course : '')) : '') }}"
                                   placeholder="Contoh: Workshop Data Analytics"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kegiatan *</label>
                            <select name="activity_type" id="activity_type"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                @php
                                    $currentActivityType = old('activity_type', $isEdit ? ($schedule->activity_type ?? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '')) : '');
                                @endphp
                                <option value="">-- Pilih Jenis Kegiatan --</option>
                                <option value="Seminar" {{ $currentActivityType == 'Seminar' ? 'selected' : '' }}>Seminar</option>
                                <option value="Workshop" {{ $currentActivityType == 'Workshop' ? 'selected' : '' }}>Workshop</option>
                                <option value="Pelatihan" {{ $currentActivityType == 'Pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                <option value="Rapat" {{ $currentActivityType == 'Rapat' ? 'selected' : '' }}>Rapat</option>
                                <option value="Ujian" {{ $currentActivityType == 'Ujian' ? 'selected' : '' }}>Ujian</option>
                                <option value="Lainnya" {{ $currentActivityType == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Posisi Peminjam</label>
                            <input type="text" name="position" id="position"
                                   value="{{ old('position', $isEdit ? ($schedule->position ?? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->position : '')) : '') }}"
                                   placeholder="Contoh: Ketua Panitia"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Peminjam / PIC *</label>
                            <input type="text" name="pic_name_non_perkuliahan" id="pic_name_non_perkuliahan"
                                   value="{{ old('pic_name_non_perkuliahan', $schedule->lecturer ?? '') }}"
                                   placeholder="Contoh: Ahmad Rafi"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kebutuhan Peralatan</label>
                            <textarea name="equipment_needs" id="equipment_needs" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('equipment_needs', $isEdit ? ($schedule->equipment_needs ?? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->equipment_needs : '')) : '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Document Fields (Collapsible) -->
                <div class="mt-6 border border-gray-200 rounded-lg overflow-hidden">
                    <button type="button" id="doc-toggle" onclick="toggleDocSection()" class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 flex items-center justify-between text-left transition-colors">
                        <div>
                            <span class="font-semibold text-gray-700">Data Dokumen (Opsional)</span>
                            <span class="text-sm text-gray-500 ml-2">— untuk generate dokumen peminjaman</span>
                        </div>
                        <svg id="doc-chevron" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="doc-section" class="hidden px-4 py-4 space-y-4">
                        @php
                            $doc = ($isEdit && $schedule->document) ? $schedule->document : null;
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Study Program (all types) -->
                            <div id="doc-study-program">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Strata/Jurusan</label>
                                <select name="study_program" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="">Pilih Program Studi</option>
                                    @foreach(['S1- Ekonomi', 'S1- Manajemen', 'S1- Akuntansi', 'S1- Ekonomi Islam', 'S1- Bisnis Digital', 'S2- Ekonomi', 'S2- Manajemen', 'S2- Akuntansi', 'Sekolah Vokasi', 'S3- PDIE Ilmu Ekonomi', 'S3- PDIE Akuntansi', 'S3- PDIE Manajemen', 'Lainnya'] as $program)
                                        <option value="{{ $program }}" {{ old('study_program', $doc->study_program ?? '') == $program ? 'selected' : '' }}>{{ $program }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Phone Number: only for non_perkuliahan (perkuliahan uses komting_phone) --}}
                            <div id="doc-phone-number" class="hidden">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telp. Koordinator</label>
                                <input type="text" name="doc_phone_number" 
                                       value="{{ old('doc_phone_number', $doc->phone_number ?? '') }}"
                                       placeholder="08xxxxxxxxxx"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Lecturer NIP (perkuliahan only) -->
                            <div id="doc-lecturer-nip">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">NIP Dosen Pengampu</label>
                                <input type="text" name="lecturer_nip"
                                       value="{{ old('lecturer_nip', $doc->lecturer_nip ?? '') }}"
                                       placeholder="NIP Dosen"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- NIM (non-perkuliahan) -->
                            <div id="doc-nim">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">NIM</label>
                                <input type="text" name="nim"
                                       value="{{ old('nim', $doc->nim ?? '') }}"
                                       placeholder="NIM Koordinator"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- NIP (non-perkuliahan, alternative) -->
                            <div id="doc-nip">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">NIP (jika dosen/pegawai)</label>
                                <input type="text" name="nip"
                                       value="{{ old('nip', $doc->nip ?? '') }}"
                                       placeholder="NIP jika bukan mahasiswa"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Software Needs -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Software yang Digunakan</label>
                            <textarea name="software_needs" rows="2"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="Contoh: Microsoft Excel, SPSS, EViews">{{ old('software_needs', $doc->software_needs ?? '') }}</textarea>
                        </div>

                        <!-- KTM Upload -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Upload KTM (Opsional)</label>
                            <div class="flex items-center gap-3">
                                <input type="file" name="ktm_file" id="ktm_file" accept=".jpg,.jpeg,.png,.pdf"
                                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                                @if($doc && $doc->ktm_path)
                                    <a href="{{ route('admin.secure-file', ['path' => $doc->ktm_path]) }}" target="_blank" 
                                       class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium whitespace-nowrap">
                                        Lihat KTM
                                    </a>
                                    <button type="button" onclick="confirmDeleteKtm()" class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, PDF. Maks 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 md:mt-8 flex flex-col sm:flex-row gap-3 md:gap-4">
                    <button type="submit" class="flex-1 py-3.5 md:py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all text-base">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="text-center px-6 py-3.5 md:py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all text-base">
                        Batal
                    </a>    
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Hidden Form for Deleting KTM -->
    @if($isEdit && $schedule->document && $schedule->document->ktm_path)
        <form id="delete-ktm-form" action="{{ route('admin.schedules.delete-ktm', $schedule->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <script>
        // Time Dropdown Logic
        function setupTimeDropdowns() {
            const timeInputs = [
                { id: 'start', inputId: 'startTime' },
                { id: 'end', inputId: 'endTime' }
            ];
            
            timeInputs.forEach(config => {
                const hourSelect = document.getElementById(config.id + '_hour');
                const minuteSelect = document.getElementById(config.id + '_minute');
                const hiddenInput = document.getElementById(config.inputId);
                
                if (!hourSelect || !minuteSelect || !hiddenInput) return;

                function updateHiddenInput() {
                    if (hourSelect.value && minuteSelect.value) {
                        hiddenInput.value = `${hourSelect.value}:${minuteSelect.value}`;
                        hiddenInput.dispatchEvent(new Event('change'));
                    } else {
                        hiddenInput.value = '';
                        hiddenInput.dispatchEvent(new Event('change'));
                    }
                }
                
                // Initialize
                if (hiddenInput.value) {
                    const [h, m] = hiddenInput.value.split(':');
                    if (h) hourSelect.value = h;
                    if (m) minuteSelect.value = m;
                }
                
                hourSelect.addEventListener('change', updateHiddenInput);
                minuteSelect.addEventListener('change', updateHiddenInput);
            });
        }
        
        document.addEventListener('DOMContentLoaded', setupTimeDropdowns);

        // Time Validation - Ensure end time > start time
        function validateTimeSelection() {
            const startTimeEl = document.getElementById('startTime');
            const endTimeEl = document.getElementById('endTime');
            const timeError = document.getElementById('time-error');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (!startTimeEl || !endTimeEl || !timeError) return true;
            
            const startTime = startTimeEl.value;
            const endTime = endTimeEl.value;
            
            // Skip validation if either time is not set
            if (!startTime || !endTime) {
                timeError.classList.add('hidden');
                return true;
            }
            
            // Compare times (format HH:MM)
            const [startH, startM] = startTime.split(':').map(Number);
            const [endH, endM] = endTime.split(':').map(Number);
            const startMinutes = startH * 60 + startM;
            const endMinutes = endH * 60 + endM;
            
            if (endMinutes <= startMinutes) {
                // Invalid - end time is not after start time
                timeError.classList.remove('hidden');
                if (submitBtn) submitBtn.disabled = true;
                return false;
            } else {
                // Valid
                timeError.classList.add('hidden');
                if (submitBtn) submitBtn.disabled = false;
                return true;
            }
        }
        
        // Add event listener for time changes
        document.addEventListener('DOMContentLoaded', function() {
            const startTimeEl = document.getElementById('startTime');
            const endTimeEl = document.getElementById('endTime');
            
            if (startTimeEl) {
                startTimeEl.addEventListener('change', validateTimeSelection);
            }
            if (endTimeEl) {
                endTimeEl.addEventListener('change', validateTimeSelection);
            }
            
            // Also validate on form submit
            const form = document.getElementById('scheduleForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateTimeSelection()) {
                        e.preventDefault();
                        document.getElementById('time-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        });

        // Time-First Flow Variables
        const daySelectEl = document.getElementById('daySelect');
        const startTimeEl = document.getElementById('startTime');
        const endTimeEl = document.getElementById('endTime');
        const startDateEl = document.getElementById('startDate');
        const endDateEl = document.getElementById('endDate');
        const labSelectEl = document.getElementById('labSelect');
        const isEditMode = {{ $isEdit ? 'true' : 'false' }};
        const excludeScheduleId = {{ $isEdit ? $schedule->id : 'null' }};
        const currentLabId = {{ $isEdit ? ($schedule->lab_id ?? 'null') : 'null' }};

        // Fetch available labs via AJAX
        function fetchAvailableLabs() {
            const day = daySelectEl.value;
            const startTime = startTimeEl.value;
            const endTime = endTimeEl.value;
            const startDate = startDateEl.value;
            const endDate = endDateEl.value;

            // Require day, start_time, and end_time
            if (!day || !startTime || !endTime) {
                if (!isEditMode) {
                    labSelectEl.disabled = true;
                    labSelectEl.innerHTML = '<option value="">Pilih waktu terlebih dahulu</option>';
                }
                return;
            }

            // Show loading state
            labSelectEl.innerHTML = '<option value="">Memuat lab tersedia...</option>';
            labSelectEl.disabled = true;

            // Make AJAX request
            fetch('{{ route("admin.schedules.available-labs") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    day: day,
                    start_time: startTime,
                    end_time: endTime,
                    start_date: startDate || null,
                    end_date: endDate || null,
                    exclude_schedule_id: excludeScheduleId
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(labs => {
                labSelectEl.innerHTML = '';
                
                if (labs.length === 0) {
                    labSelectEl.innerHTML = '<option value="">Tidak ada lab tersedia pada waktu ini</option>';
                    labSelectEl.disabled = true;
                } else {
                    labSelectEl.innerHTML = '<option value="">-- Pilih Laboratorium --</option>';
                    labs.forEach(lab => {
                        const option = document.createElement('option');
                        option.value = lab.id;
                        option.dataset.capacity = lab.capacity;
                        option.textContent = `${lab.name} (Kap. ${lab.capacity})`;
                        
                        // Pre-select current lab in edit mode
                        if (isEditMode && lab.id === currentLabId) {
                            option.selected = true;
                        }
                        
                        labSelectEl.appendChild(option);
                    });
                    labSelectEl.disabled = false;
                    
                    // Trigger validation after loading labs
                    validateStudentCount();
                }
            })
            .catch(error => {
                console.error('Error fetching labs:', error);
                labSelectEl.innerHTML = '<option value="">Gagal memuat data lab</option>';
                labSelectEl.disabled = true;
            });
        }

        // Event listeners for time fields - fetch labs when any changes
        [daySelectEl, startTimeEl, endTimeEl, startDateEl, endDateEl].forEach(el => {
            el.addEventListener('change', fetchAvailableLabs);
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (!isEditMode) {
                // In create mode, fetch if time fields already have values
                if (daySelectEl.value && startTimeEl.value && endTimeEl.value) {
                    fetchAvailableLabs();
                }
            }
        });

        // Conditional Fields Logic
        const typeSelect = document.getElementById('typeSelect');
        const perkuliahanFields = document.getElementById('perkuliahan-fields');
        const nonPerkuliahanFields = document.getElementById('non-perkuliahan-fields');

        // Track previous type for smart field transfer
        let previousType = typeSelect.value;

        // Function to show/hide fields based on type
        function updateFieldsVisibility() {
            const selectedType = typeSelect.value;
            
            // Smart field transfer when switching types
            transferFieldsBetweenTypes(previousType, selectedType);
            previousType = selectedType;
            
            // Hide all conditional sections first
            perkuliahanFields.classList.add('hidden');
            nonPerkuliahanFields.classList.add('hidden');
            
            // Disable all conditional fields
            setFieldsRequired('perkuliahan-fields', false);
            setFieldsRequired('non-perkuliahan-fields', false);
            
            // Show and enable appropriate section
            if (selectedType === 'perkuliahan_tetap' || selectedType === 'perkuliahan_tidak_tetap') {
                perkuliahanFields.classList.remove('hidden');
                setFieldsRequired('perkuliahan-fields', true);
            } else if (selectedType === 'non_perkuliahan') {
                nonPerkuliahanFields.classList.remove('hidden');
                setFieldsRequired('non-perkuliahan-fields', true);
            }

            // Doc phone number: only for non_perkuliahan (perkuliahan uses komting_phone)
            const docPhoneEl = document.getElementById('doc-phone-number');
            if (docPhoneEl) {
                if (selectedType === 'non_perkuliahan') {
                    docPhoneEl.classList.remove('hidden');
                } else {
                    docPhoneEl.classList.add('hidden');
                }
            }
        }

        /**
         * Transfer coordinator/phone data when switching between types
         * Koordinator = Komting (same role, different label)
         */
        function transferFieldsBetweenTypes(fromType, toType) {
            if (fromType === toType) return;

            const isFromPerkuliahan = (fromType === 'perkuliahan_tetap' || fromType === 'perkuliahan_tidak_tetap');
            const isToPerkuliahan = (toType === 'perkuliahan_tetap' || toType === 'perkuliahan_tidak_tetap');
            const isFromNonPerkuliahan = (fromType === 'non_perkuliahan');
            const isToNonPerkuliahan = (toType === 'non_perkuliahan');

            // Get field references
            const komtingInput = document.querySelector('input[name="komting"]');
            const komtingPhoneInput = document.querySelector('input[name="komting_phone"]');
            const picNameInput = document.querySelector('input[name="pic_name_non_perkuliahan"]');
            const docPhoneInput = document.querySelector('input[name="doc_phone_number"]');

            // Non-perkuliahan → Perkuliahan: PIC → Komting, Telp Koordinator → Telp Komting
            if (isFromNonPerkuliahan && isToPerkuliahan) {
                if (komtingInput && picNameInput && picNameInput.value) {
                    komtingInput.value = picNameInput.value;
                }
                if (komtingPhoneInput && docPhoneInput && docPhoneInput.value) {
                    komtingPhoneInput.value = docPhoneInput.value;
                }
            }

            // Perkuliahan → Non-perkuliahan: Komting → PIC, Telp Komting → Telp Koordinator
            if (isFromPerkuliahan && isToNonPerkuliahan) {
                if (picNameInput && komtingInput && komtingInput.value) {
                    picNameInput.value = komtingInput.value;
                }
                if (docPhoneInput && komtingPhoneInput && komtingPhoneInput.value) {
                    docPhoneInput.value = komtingPhoneInput.value;
                }
            }
        }

        // Function to set required attribute on fields
        function setFieldsRequired(containerId, required) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const inputs = container.querySelectorAll('input, select, textarea');
            const optionalFields = ['equipment_needs', 'lecturer', 'komting', 'komting_phone', 'position'];
            
            inputs.forEach(input => {
                const fieldName = input.name || input.id;
                
                // Skip optional fields
                if (optionalFields.includes(fieldName)) {
                    input.removeAttribute('required');
                    if (!required) {
                        input.setAttribute('disabled', 'disabled');
                    } else {
                        input.removeAttribute('disabled');
                    }
                    return;
                }
                
                // Handle required/disabled based on visibility
                if (required) {
                    input.setAttribute('required', 'required');
                    input.removeAttribute('disabled');
                } else {
                    input.removeAttribute('required');
                    input.setAttribute('disabled', 'disabled');
                }
            });
        }

        // Event listeners
        typeSelect.addEventListener('change', function() {
            updateFieldsVisibility();
            updateDocFieldsVisibility();
        });

        // Document section toggle
        function toggleDocSection() {
            const section = document.getElementById('doc-section');
            const chevron = document.getElementById('doc-chevron');
            section.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }

        // Confirm Delete KTM
        function confirmDeleteKtm() {
            if (confirm('Hapus file KTM?')) {
                document.getElementById('delete-ktm-form').submit();
            }
        }

        // Document fields visibility based on type
        function updateDocFieldsVisibility() {
            const selectedType = typeSelect.value;
            const lecturerNip = document.getElementById('doc-lecturer-nip');
            const nim = document.getElementById('doc-nim');
            const nip = document.getElementById('doc-nip');

            if (!lecturerNip || !nim || !nip) return;

            // Reset: hide all type-specific doc fields
            lecturerNip.classList.add('hidden');
            nim.classList.add('hidden');
            nip.classList.add('hidden');

            if (selectedType === 'perkuliahan_tetap' || selectedType === 'perkuliahan_tidak_tetap') {
                lecturerNip.classList.remove('hidden');
            } else if (selectedType === 'non_perkuliahan') {
                nim.classList.remove('hidden');
                nip.classList.remove('hidden');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateFieldsVisibility();
            updateDocFieldsVisibility();

            // Auto-expand doc section if editing and has document data
            @if($isEdit && isset($schedule) && $schedule->document)
                toggleDocSection();
            @endif
        });

        // Real-time validation for day in date range
        // Re-use existing variables: daySelectEl, startDateEl, endDateEl from above
        const submitButton = document.querySelector('button[type="submit"]');
        
        // Create error message container
        const errorContainer = document.createElement('div');
        errorContainer.className = 'hidden mt-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg';
        errorContainer.id = 'day-validation-error';
        endDateEl.parentElement.appendChild(errorContainer);

        // Indonesian day names mapping
        const dayNames = {
            0: 'Minggu',
            1: 'Senin',
            2: 'Selasa',
            3: 'Rabu',
            4: 'Kamis',
            5: 'Jumat',
            6: 'Sabtu'
        };

        function validateDayInDateRange() {
            const selectedDay = daySelectEl.value;
            const startDate = startDateEl.value;
            const endDate = endDateEl.value;

            // If no day selected or no dates, clear error
            if (!selectedDay || (!startDate && !endDate)) {
                errorContainer.classList.add('hidden');
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Parse dates
            const start = new Date(startDate || endDate);
            const end = new Date(endDate || startDate);

            // IMPORTANT: Validate that start date matches the selected day
            const startDayOfWeek = start.getDay();
            const startDayName = dayNames[startDayOfWeek];
            
            if (startDayName !== selectedDay) {
                const formattedStart = start.toLocaleDateString('id-ID');
                errorContainer.innerHTML = `
                    <p class="font-bold">⚠️ Validasi Hari dan Tanggal Mulai</p>
                    <p class="text-sm mt-1">Tanggal mulai (<strong>${formattedStart}</strong>) adalah hari <strong>${startDayName}</strong>, tetapi hari yang dipilih adalah <strong>${selectedDay}</strong>.</p>
                    <p class="text-sm mt-1">Silakan pilih tanggal mulai yang jatuh pada hari ${selectedDay}.</p>
                `;
                errorContainer.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Check if selected day exists in range
            let dayFound = false;
            const currentDate = new Date(start);

            while (currentDate <= end) {
                const dayOfWeek = currentDate.getDay();
                if (dayNames[dayOfWeek] === selectedDay) {
                    dayFound = true;
                    break;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }

            if (!dayFound) {
                // Format dates for display
                const formattedStart = start.toLocaleDateString('id-ID');
                const formattedEnd = end.toLocaleDateString('id-ID');
                
                errorContainer.innerHTML = `
                    <p class="font-bold">⚠️ Validasi Hari dan Tanggal</p>
                    <p class="text-sm mt-1">Hari <strong>${selectedDay}</strong> tidak ditemukan dalam rentang tanggal <strong>${formattedStart} - ${formattedEnd}</strong>.</p>
                    <p class="text-sm mt-1">Silakan pilih rentang tanggal yang mengandung hari ${selectedDay} atau ubah pilihan hari.</p>
                `;
                errorContainer.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                errorContainer.classList.add('hidden');
                // Check if student count validation also passes before enabling
                const studentCountValid = validateStudentCount();
                if (studentCountValid) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Add event listeners
        daySelectEl.addEventListener('change', validateDayInDateRange);
        startDateEl.addEventListener('change', validateDayInDateRange);
        endDateEl.addEventListener('change', validateDayInDateRange);

        // Run validation on page load (for edit form with existing data)
        document.addEventListener('DOMContentLoaded', validateDayInDateRange);

        // Real-time validation for student count vs lab capacity
        // Use labSelectEl (already defined above for AJAX)
        const studentCountInput = document.querySelector('input[name="student_count"]');
        
        // Create warning message container for student count (changed to warning style)
        const studentCountErrorContainer = document.createElement('div');
        studentCountErrorContainer.className = 'hidden mt-2 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-r-lg text-sm';
        studentCountErrorContainer.id = 'student-count-error';
        studentCountInput.parentElement.appendChild(studentCountErrorContainer);

        function validateStudentCount() {
            const selectedLabId = labSelectEl.value;
            const studentCount = parseInt(studentCountInput.value);

            // Clear error if no lab selected or no student count
            if (!selectedLabId || !studentCount || studentCount <= 0) {
                studentCountErrorContainer.classList.add('hidden');
                return true;
            }

            // Get capacity from selected option's data attribute (supports dynamic AJAX options)
            const selectedOption = labSelectEl.options[labSelectEl.selectedIndex];
            const labCapacity = selectedOption ? parseInt(selectedOption.dataset.capacity) : 0;
            
            if (!labCapacity || studentCount > labCapacity) {
                const labName = labSelectEl.options[labSelectEl.selectedIndex].text;
                studentCountErrorContainer.innerHTML = `
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-bold">⚠️ Kapasitas Tidak Memadai</p>
                            <p class="mt-1">Jumlah mahasiswa (${studentCount}) melebihi kapasitas ${labName} (${labCapacity} orang).</p>
                            <p class="mt-1 italic font-semibold">Konsekuensi: Fasilitas mungkin tidak mencukupi untuk setiap peserta dan ketidaknyamanan ditanggung sendiri.</p>
                        </div>
                    </div>
                `;
                studentCountErrorContainer.classList.remove('hidden');
                
                // Allow submit even with warning
                const dayErrorVisible = !errorContainer.classList.contains('hidden');
                if (!dayErrorVisible) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                return true; 
            } else {
                studentCountErrorContainer.classList.add('hidden');
                // Check if day validation also passes before enabling
                const dayErrorVisible = !errorContainer.classList.contains('hidden');
                if (!dayErrorVisible) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                return true;
            }
        }

        // Add event listeners for student count validation
        labSelectEl.addEventListener('change', validateStudentCount);
        studentCountInput.addEventListener('input', validateStudentCount);

        // Run student count validation on page load
        document.addEventListener('DOMContentLoaded', validateStudentCount);

        // Custom Dropdown Implementation for Mobile Friendliness
        class CustomSelect {
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
                this.trigger.className = 'w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-base text-left bg-white flex justify-between items-center transition-shadow duration-200';
                
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
                // Add some animation styles inline or verify classes
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

                // Observe disabled attribute changes AND dynamic options changes on original select
                this.observer = new MutationObserver((mutations) => {
                    let shouldUpdateTrigger = false;
                    let shouldReinitOptions = false;
                    
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                            shouldUpdateTrigger = true;
                        }
                        if (mutation.type === 'childList') {
                            // Options were added/removed (e.g., from AJAX)
                            shouldReinitOptions = true;
                            shouldUpdateTrigger = true;
                        }
                    });
                    
                    if (shouldReinitOptions) this.initOptions();
                    if (shouldUpdateTrigger) this.updateTrigger();
                });
                this.observer.observe(this.originalSelect, { 
                    attributes: true, 
                    childList: true, 
                    subtree: true 
                });

                // Listen for changes on original select (to update UI if changed programmatically)
                this.originalSelect.addEventListener('change', () => {
                   this.updateTrigger();
                   this.initOptions(); // Re-render to update checkmarks
                });
            }

            initOptions() {
                this.optionsContainer.innerHTML = '';
                Array.from(this.originalSelect.options).forEach(option => {
                    if (option.value === "" && option.disabled) return; // Skip placeholder if disabled? usually simply keep it

                    const optionDiv = document.createElement('div');
                    optionDiv.className = `text-gray-900 cursor-pointer select-none relative py-2.5 pl-4 pr-9 hover:bg-yellow-50 transition-colors duration-150 border-b border-gray-50 last:border-0`;
                    optionDiv.textContent = option.text;
                    
                    if (option.selected) {
                        optionDiv.classList.add('bg-blue-50', 'text-blue-900', 'font-medium'); // Highlight selected
                        const check = document.createElement('span');
                        check.className = 'absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600';
                        check.innerHTML = `<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`;
                        optionDiv.appendChild(check);
                    }

                    optionDiv.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.originalSelect.value = option.value;
                        this.originalSelect.dispatchEvent(new Event('change'));
                        this.closeDropdown();
                    });

                    this.optionsContainer.appendChild(optionDiv);
                });
            }

            updateTrigger() {
                const selectedOption = this.originalSelect.options[this.originalSelect.selectedIndex];
                this.triggerLabel.textContent = selectedOption ? selectedOption.text : 'Pilih...';
                
                // Handle disabled state
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
                        // Reset chevron of others
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

        // Initialize Custom Selects
        document.addEventListener('DOMContentLoaded', function() {
            // Target specific selects
            const selects = [
                'lab_id', 
                'day', 
                'type', 
                'applicant_status', 
                'activity_type'
            ];

            selects.forEach(name => {
                const selectElement = document.querySelector(`select[name="${name}"]`);
                if (selectElement) {
                    new CustomSelect(selectElement);
                }
            });
        });
    </script>
</body>
</html>
