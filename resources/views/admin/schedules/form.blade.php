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
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-gray-800">{{ $isEdit ? 'Edit' : 'Tambah' }} Jadwal</span>
                        <p class="text-xs text-gray-500">Lab Terpadu FEB UNDIP</p>
                    </div>
                </div>
                <a href="{{ route('admin.schedules.index') }}" class="px-4 py-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg font-medium">
                    ← Kembali ke Daftar
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 max-w-3xl">
        <!-- Error Messages -->
        @if($errors->any())
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg">
                <p class="font-bold">Terjadi Kesalahan:</p>
                <ul class="list-disc list-inside">
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
        <div class="bg-white rounded-xl shadow-md p-6">
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
</body>
</html>
