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
                        <select name="type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $schedule->type ?? 'regular') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Student Count -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Mahasiswa</label>
                        <input type="number" name="student_count" min="1"
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

                    <!-- Course/Activity -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Kuliah / Nama Kegiatan *</label>
                        <input type="text" name="course" required
                               value="{{ old('course', $schedule->course ?? '') }}"
                               placeholder="Contoh: Sistem Informasi Manajemen"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>

                    <!-- Lecturer -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Dosen Pengampu / PIC</label>
                        <input type="text" name="lecturer"
                               value="{{ old('lecturer', $schedule->lecturer ?? '') }}"
                               placeholder="Contoh: Dr. Budi Santoso"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>

                    <!-- Komting -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Koordinator / Komting</label>
                        <input type="text" name="komting"
                               value="{{ old('komting', $schedule->komting ?? '') }}"
                               placeholder="Contoh: Ahmad Faizal"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
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
        
        // Create error message container for student count
        const studentCountErrorContainer = document.createElement('div');
        studentCountErrorContainer.className = 'hidden mt-2 text-red-600 text-sm font-medium';
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
                studentCountErrorContainer.innerHTML = `⚠️ Jumlah mahasiswa (${studentCount}) melebihi kapasitas ${labName} (${labCapacity} orang)`;
                studentCountErrorContainer.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                return false;
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
