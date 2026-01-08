<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isEdit ? 'Edit' : 'Tambah' }} Jadwal - Lab Terpadu FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 border-b-4 border-yellow-500">
        <div class="container mx-auto px-4 md:px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-16 w-auto object-contain">
                </div>
                <a href="{{ route('admin.schedules.index') }}" class="px-4 py-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg font-medium">
                    ← Kembali ke Daftar
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 md:px-6 py-6 md:py-8 max-w-3xl">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $isEdit ? 'Edit' : 'Tambah' }} Jadwal</h1>
            <p class="text-gray-600">Silakan isi form berikut untuk {{ $isEdit ? 'memperbarui' : 'menambahkan' }} jadwal</p>
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
        <div class="bg-white rounded-xl shadow-md p-4 md:p-6">
            <form action="{{ $isEdit ? route('admin.schedules.update', $schedule->id) : route('admin.schedules.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Lab -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Laboratorium *</label>
                        <select name="lab_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <option value="">Pilih Lab</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}" {{ old('lab_id', $schedule->lab_id ?? '') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->name }} (Kap. {{ $lab->capacity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Day -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hari *</label>
                        <select name="day" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <option value="">Pilih Hari</option>
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ old('day', $schedule->day ?? '') == $day ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Time -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Mulai *</label>
                        <input type="time" name="start_time" required
                               value="{{ old('start_time', $schedule ? $schedule->start_time->format('H:i') : '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>

                    <!-- End Time -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Selesai *</label>
                        <input type="time" name="end_time" required
                               value="{{ old('end_time', $schedule ? $schedule->end_time->format('H:i') : '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>

                    <!-- Type -->
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

                    <!-- Student Count -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Mahasiswa</label>
                        <input type="number" name="student_count" id="student_count" min="1"
                               value="{{ old('student_count', $schedule->student_count ?? '') }}"
                               placeholder="Opsional"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date', $schedule && $schedule->start_date ? $schedule->start_date->format('Y-m-d') : '') }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika berlaku selamanya</p>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date"
                               value="{{ old('end_date', $schedule && $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika berlaku selamanya</p>
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
                    </div>
                </div>

                <!-- Non-Perkuliahan Fields -->
                <div id="non-perkuliahan-fields" class="hidden mt-6">
                    <h4 class="font-bold text-gray-800 mb-4 text-lg">Data Kegiatan Non-Perkuliahan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kegiatan *</label>
                            <input type="text" name="activity_name" id="activity_name"
                                   value="{{ old('activity_name', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_name : '') }}"
                                   placeholder="Contoh: Workshop Data Analytics"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kegiatan *</label>
                            <select name="activity_type" id="activity_type"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="">-- Pilih Jenis Kegiatan --</option>
                                <option value="Seminar" {{ old('activity_type', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '') == 'Seminar' ? 'selected' : '' }}>Seminar</option>
                                <option value="Workshop" {{ old('activity_type', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '') == 'Workshop' ? 'selected' : '' }}>Workshop</option>
                                <option value="Pelatihan" {{ old('activity_type', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '') == 'Pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                <option value="Rapat" {{ old('activity_type', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '') == 'Rapat' ? 'selected' : '' }}>Rapat</option>
                                <option value="Ujian" {{ old('activity_type', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '') == 'Ujian' ? 'selected' : '' }}>Ujian</option>
                                <option value="Lainnya" {{ old('activity_type', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan Peminjam *</label>
                            <input type="text" name="position" id="position"
                                   value="{{ old('position', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->position : '') }}"
                                   placeholder="Contoh: Ketua Panitia"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kebutuhan Peralatan</label>
                            <textarea name="equipment_needs" id="equipment_needs" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('equipment_needs', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->equipment_needs : '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Pribadi Fields -->
                <div id="pribadi-fields" class="hidden mt-6">
                    <h4 class="font-bold text-gray-800 mb-4 text-lg">Data Peminjaman Pribadi</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                            <select name="applicant_status" id="applicant_status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                <option value="">Pilih Status</option>
                                @php
                                    $currentStatus = old('applicant_status', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'pribadi') ? $schedule->booking->applicant_status : '');
                                    // If custom_status exists, set applicant_status to "Lainnya"
                                    if ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'pribadi' && $schedule->booking->custom_status) {
                                        $currentStatus = 'Lainnya';
                                    }
                                @endphp
                                <option value="Mahasiswa" {{ $currentStatus == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="Dosen" {{ $currentStatus == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="Pegawai" {{ $currentStatus == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                <option value="Lainnya" {{ $currentStatus == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        
                        <div id="custom-status-field" style="display: none;">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Lainnya *</label>
                            <input type="text" name="custom_status" id="custom_status"
                                   value="{{ old('custom_status', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'pribadi') ? $schedule->booking->custom_status : '') }}"
                                   placeholder="Masukkan status Anda"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div id="class-year-field" style="display: none;">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Angkatan *</label>
                            <input type="text" name="class_year" id="class_year"
                                   value="{{ old('class_year', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'pribadi') ? $schedule->booking->class_year : '') }}"
                                   placeholder="Contoh: 2023" maxlength="4"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan *</label>
                            <input type="text" name="purpose" id="purpose"
                                   value="{{ old('purpose', ($isEdit && $schedule->booking && $schedule->booking->booking_type === 'pribadi') ? $schedule->booking->purpose : '') }}"
                                   placeholder="Contoh: Ujian, Kuliah, Mengerjakan tugas pribadi"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex gap-4">
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all">
                        Batal
                    </a>    
                </div>
            </form>
        </div>
    </div>

    <script>
        // Conditional Fields Logic
        const typeSelect = document.getElementById('typeSelect');
        const perkuliahanFields = document.getElementById('perkuliahan-fields');
        const nonPerkuliahanFields = document.getElementById('non-perkuliahan-fields');
        const pribadiFields = document.getElementById('pribadi-fields');
        const applicantStatusSelect = document.getElementById('applicant_status');
        const classYearField = document.getElementById('class-year-field');
        const classYearInput = document.getElementById('class_year');
        const customStatusField = document.getElementById('custom-status-field');
        const customStatusInput = document.getElementById('custom_status');

        // Function to show/hide fields based on type
        function updateFieldsVisibility() {
            const selectedType = typeSelect.value;
            
            // Hide all conditional sections first
            perkuliahanFields.classList.add('hidden');
            nonPerkuliahanFields.classList.add('hidden');
            pribadiFields.classList.add('hidden');
            
            // Disable all conditional fields
            setFieldsRequired('perkuliahan-fields', false);
            setFieldsRequired('non-perkuliahan-fields', false);
            setFieldsRequired('pribadi-fields', false);
            
            // Show and enable appropriate section
            if (selectedType === 'perkuliahan_tetap' || selectedType === 'perkuliahan_tidak_tetap') {
                perkuliahanFields.classList.remove('hidden');
                setFieldsRequired('perkuliahan-fields', true);
            } else if (selectedType === 'non_perkuliahan') {
                nonPerkuliahanFields.classList.remove('hidden');
                setFieldsRequired('non-perkuliahan-fields', true);
            } else if (selectedType === 'pribadi') {
                pribadiFields.classList.remove('hidden');
                setFieldsRequired('pribadi-fields', true);
                // Check if we need to show class year field or custom status field
                updatePribadiFields();
            }
        }

        // Function to set required attribute on fields
        function setFieldsRequired(containerId, required) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const inputs = container.querySelectorAll('input, select, textarea');
            const optionalFields = ['equipment_needs', 'lecturer', 'komting', 'class_year', 'custom_status'];
            
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

        // Function to toggle fields for Pribadi status
        function updatePribadiFields() {
            if (!applicantStatusSelect) return;
            
            const status = applicantStatusSelect.value;
            
            // Toggle Class Year (Only for Mahasiswa)
            if (status === 'Mahasiswa') {
                classYearField.style.display = 'block';
                classYearInput.setAttribute('required', 'required');
                classYearInput.removeAttribute('disabled');
            } else {
                classYearField.style.display = 'none';
                classYearInput.removeAttribute('required');
                classYearInput.setAttribute('disabled', 'disabled');
                // Don't clear value immediately in case user switches back
            }
            
            // Toggle Custom Status (Only for Lainnya)
            if (status === 'Lainnya') {
                customStatusField.style.display = 'block';
                customStatusInput.setAttribute('required', 'required');
                customStatusInput.removeAttribute('disabled');
            } else {
                customStatusField.style.display = 'none';
                customStatusInput.removeAttribute('required');
                customStatusInput.setAttribute('disabled', 'disabled');
            }
        }

        // Event listeners
        typeSelect.addEventListener('change', updateFieldsVisibility);
        if (applicantStatusSelect) {
            applicantStatusSelect.addEventListener('change', updatePribadiFields);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateFieldsVisibility();
            // Also update pribadi fields if type is pribadi on page load
            if (typeSelect.value === 'pribadi') {
                updatePribadiFields();
            }
        });

        // Real-time validation for day in date range
        const daySelect = document.querySelector('select[name="day"]');
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');
        const submitButton = document.querySelector('button[type="submit"]');
        
        // Create error message container
        const errorContainer = document.createElement('div');
        errorContainer.className = 'hidden mt-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg';
        errorContainer.id = 'day-validation-error';
        endDateInput.parentElement.appendChild(errorContainer);

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
            const selectedDay = daySelect.value;
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

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
        daySelect.addEventListener('change', validateDayInDateRange);
        startDateInput.addEventListener('change', validateDayInDateRange);
        endDateInput.addEventListener('change', validateDayInDateRange);

        // Run validation on page load (for edit form with existing data)
        document.addEventListener('DOMContentLoaded', validateDayInDateRange);

        // Real-time validation for student count vs lab capacity
        const labSelect = document.querySelector('select[name="lab_id"]');
        const studentCountInput = document.querySelector('input[name="student_count"]');
        
        // Create warning message container for student count (changed to warning style)
        const studentCountErrorContainer = document.createElement('div');
        studentCountErrorContainer.className = 'hidden mt-2 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-r-lg text-sm';
        studentCountErrorContainer.id = 'student-count-error';
        studentCountInput.parentElement.appendChild(studentCountErrorContainer);

        // Store lab capacities
        const labCapacities = {
            @foreach($labs as $lab)
                {{ $lab->id }}: {{ $lab->capacity }},
            @endforeach
        };

        function validateStudentCount() {
            const selectedLabId = labSelect.value;
            const studentCount = parseInt(studentCountInput.value);

            // Clear error if no lab selected or no student count
            if (!selectedLabId || !studentCount || studentCount <= 0) {
                studentCountErrorContainer.classList.add('hidden');
                return true;
            }

            const labCapacity = labCapacities[selectedLabId];
            
            if (studentCount > labCapacity) {
                const labName = labSelect.options[labSelect.selectedIndex].text;
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
        labSelect.addEventListener('change', validateStudentCount);
        studentCountInput.addEventListener('input', validateStudentCount);

        // Run student count validation on page load
        document.addEventListener('DOMContentLoaded', validateStudentCount);
    </script>
</body>
</html>
