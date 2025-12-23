<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Peminjaman - Lab Terpadu FEB UNDIP</title>
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
        }
        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
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
        .step-item.active .step-number {
            background: #eab308;
            color: white;
        }
        .step-item.completed .step-number {
            background: #22c55e;
            color: white;
        }
        .step-item.completed::after {
            background: #22c55e;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl font-bold text-yellow-600">Lab Terpadu</span>
                    <span class="text-xl text-gray-700">FEB UNDIP</span>
                </div>
                <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                    Login Asisten Lab
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-12 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Ajukan Peminjaman Laboratorium</h1>
            <p class="text-gray-600">Silakan lengkapi formulir di bawah ini untuk mengajukan peminjaman laboratorium</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Time Conflict Warning (More Prominent) -->
            @if ($errors->has('time_conflict'))
                <div class="mb-6 bg-red-100 border-2 border-red-500 rounded-lg p-5 animate-pulse">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-red-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-red-900 font-bold text-lg mb-2">⚠️ Konflik Jadwal!</h3>
                            <p class="text-red-800 font-semibold">{{ $errors->first('time_conflict') }}</p>
                            <p class="text-red-700 text-sm mt-2">💡 Tip: Gunakan fitur "Cek Ketersediaan" atau pilih waktu/ruangan yang berbeda.</p>
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
            <div class="step-indicator mb-8">
                <div class="step-item active" id="step-indicator-1">
                    <div class="step-number">1</div>
                    <div class="text-sm font-medium">Tipe Peminjaman</div>
                </div>
                <div class="step-item" id="step-indicator-2">
                    <div class="step-number">2</div>
                    <div class="text-sm font-medium">Data Pribadi</div>
                </div>
                <div class="step-item" id="step-indicator-3">
                    <div class="step-number">3</div>
                    <div class="text-sm font-medium">Detail Peminjaman</div>
                </div>
                <div class="step-item" id="step-indicator-4">
                    <div class="step-number">4</div>
                    <div class="text-sm font-medium">Dokumen & Submit</div>
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
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="perkuliahan_tetap" class="hidden peer" required>
                            <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all">
                                <div class="text-4xl mb-3">📚</div>
                                <div class="font-bold text-gray-800 text-lg">Perkuliahan Tetap</div>
                                <div class="text-sm text-gray-500 mt-2">Jadwal rutin setiap minggu</div>
                            </div>
                        </label>
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="perkuliahan_tidak_tetap" class="hidden peer" required>
                            <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all">
                                <div class="text-4xl mb-3">📖</div>
                                <div class="font-bold text-gray-800 text-lg">Perkuliahan Tidak Tetap</div>
                                <div class="text-sm text-gray-500 mt-2">Sekali waktu saja</div>
                            </div>
                        </label>
                        <label class="booking-type-card cursor-pointer">
                            <input type="radio" name="booking_type" value="non_perkuliahan" class="hidden peer" required>
                            <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-yellow-500 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition-all">
                                <div class="text-4xl mb-3">🎯</div>
                                <div class="font-bold text-gray-800 text-lg">Non-Perkuliahan</div>
                                <div class="text-sm text-gray-500 mt-2">Kegiatan lainnya</div>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="btn-next-1" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>
                            Lanjut ke Data Pribadi →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Data Pribadi -->
                <div id="step-2" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">2</span>
                        Data Pribadi
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap *</label>
                            <input type="text" name="nama_peminjam" id="nama_peminjam" value="{{ old('nama_peminjam') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Program Studi *</label>
                            <input type="text" name="program_studi" id="program_studi" value="{{ old('program_studi') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">NIM * <span class="text-xs text-gray-500">(14 digit)</span></label>
                            <input type="text" name="nim" id="nim" value="{{ old('nim') }}" required
                                maxlength="14" pattern="[0-9]{14}" 
                                placeholder="Contoh: 12010120130001"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor Telepon * <span class="text-xs text-gray-500">(10-15 digit)</span></label>
                            <input type="text" name="no_telpon" id="no_telpon" value="{{ old('no_telpon') }}" required
                                minlength="10" maxlength="15" pattern="[0-9+]{10,15}"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat</label>
                            <textarea name="alamat" id="alamat" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('alamat') }}</textarea>
                        </div>
                    </div>

                    <!-- Dynamic Fields Based on Booking Type -->
                    <div id="perkuliahan-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Data Perkuliahan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Mata Kuliah *</label>
                                <input type="text" name="mata_kuliah" id="mata_kuliah" value="{{ old('mata_kuliah') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Dosen Pengampu *</label>
                                <input type="text" name="dosen_pengampu" id="dosen_pengampu" value="{{ old('dosen_pengampu') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">NIP Dosen *</label>
                                <input type="text" name="nip_dosen" id="nip_dosen" value="{{ old('nip_dosen') }}"
                                    maxlength="18" pattern="[0-9]{1,18}"
                                    placeholder="Maksimal 18 digit"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Software yang Digunakan</label>
                                <input type="text" name="software_digunakan" id="software_digunakan" value="{{ old('software_digunakan') }}"
                                    placeholder="Contoh: SPSS, Microsoft Office, dll"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div id="non-perkuliahan-fields" class="hidden mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Data Kegiatan Non-Perkuliahan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Kegiatan *</label>
                                <input type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Jenis Kegiatan *</label>
                                <input type="text" name="jenis_kegiatan" id="jenis_kegiatan" value="{{ old('jenis_kegiatan') }}"
                                    placeholder="Contoh: Seminar, Workshop, Rapat"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Jabatan</label>
                                <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 text-sm font-semibold mb-2">Kebutuhan Peralatan</label>
                                <textarea name="kebutuhan_peralatan" id="kebutuhan_peralatan" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('kebutuhan_peralatan') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" id="btn-prev-2" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                            ← Kembali
                        </button>
                        <button type="button" id="btn-next-2" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>
                            Lanjut ke Detail Peminjaman →
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
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Peminjaman *</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" required
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Jumlah Peserta *</label>
                            <input type="number" name="jumlah_peserta" id="jumlah_peserta" value="{{ old('jumlah_peserta') }}" required
                                min="1" placeholder="Contoh: 30"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Jam Mulai *</label>
                            <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Jam Selesai *</label>
                            <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-semibold mb-2">Pilih Laboratorium *</label>
                            <select name="lab_id" id="labSelect" required disabled
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent disabled:bg-gray-100">
                                <option value="">Isi data di atas untuk melihat lab yang tersedia</option>
                            </select>
                            <p class="text-sm text-gray-500 mt-2">Lab akan muncul setelah Anda mengisi tanggal, waktu, dan jumlah peserta</p>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" id="btn-prev-3" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                            ← Kembali
                        </button>
                        <button type="button" id="btn-next-3" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>
                            Lanjut ke Upload Dokumen →
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Upload Dokumen & Submit -->
                <div id="step-4" class="step-section hidden step-disabled">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">4</span>
                        Upload Dokumen & Konfirmasi
                    </h3>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">
                            Upload Dokumen Pendukung (Opsional)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-yellow-500 transition-colors">
                            <input type="file" name="document" id="document" accept=".pdf" class="hidden">
                            <label for="document" class="cursor-pointer">
                                <div class="text-4xl mb-2">📄</div>
                                <div class="text-gray-700 font-semibold mb-1">Klik untuk upload dokumen</div>
                                <div class="text-sm text-gray-500 mb-1">PDF maksimal 2MB</div>
                                <div class="text-xs text-gray-400">Jika file terlalu besar, silakan compress terlebih dahulu</div>
                                <div id="file-name" class="text-sm text-yellow-600 font-medium mt-2"></div>
                            </label>
                        </div>
                        @error('document')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h4 class="font-bold text-gray-800 mb-4">Ringkasan Peminjaman</h4>
                        <div id="booking-summary" class="space-y-2 text-sm"></div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" id="btn-prev-4" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                            ← Kembali
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                            ✓ Ajukan Peminjaman
                        </button>
                    </div>
                </div>
            </form>
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateStepIndicator();
            setupBookingTypeListener();
            setupStep2Validation();
            setupStep3Validation();
            setupNavigationButtons();
            setupFileUpload();
            preventEnterSubmit();
        });

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

        // Booking Type Selection
        function setupBookingTypeListener() {
            const bookingTypeInputs = document.querySelectorAll('input[name="booking_type"]');
            bookingTypeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    selectedBookingType = this.value;
                    document.getElementById('btn-next-1').disabled = false;
                    
                    // Show/hide appropriate fields for step 2
                    if (selectedBookingType === 'non_perkuliahan') {
                        document.getElementById('perkuliahan-fields').classList.add('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.remove('hidden');
                        setRequiredFields('perkuliahan-fields', false);
                        setRequiredFields('non-perkuliahan-fields', true);
                    } else {
                        document.getElementById('perkuliahan-fields').classList.remove('hidden');
                        document.getElementById('non-perkuliahan-fields').classList.add('hidden');
                        setRequiredFields('perkuliahan-fields', true);
                        setRequiredFields('non-perkuliahan-fields', false);
                    }
                });
            });
        }

        function setRequiredFields(containerId, required) {
            const container = document.getElementById(containerId);
            const inputs = container.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.hasAttribute('data-conditional-required')) {
                    if (required) {
                        input.setAttribute('required', 'required');
                        input.removeAttribute('disabled');
                    } else {
                        input.removeAttribute('required');
                        input.setAttribute('disabled', 'disabled');
                    }
                }
            });
        }

        // Step 2 Validation
        function setupStep2Validation() {
            const requiredFields = ['nama_peminjam', 'program_studi', 'nim', 'no_telpon'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                field.addEventListener('input', validateStep2);
            });
            
            // Also listen to conditional fields
            ['mata_kuliah', 'dosen_pengampu', 'nip_dosen', 'nama_kegiatan', 'jenis_kegiatan'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) field.addEventListener('input', validateStep2);
            });
        }

        function validateStep2() {
            const nama = document.getElementById('nama_peminjam').value.trim();
            const prodi = document.getElementById('program_studi').value.trim();
            const nim = document.getElementById('nim').value.trim();
            const telpon = document.getElementById('no_telpon').value.trim();

            let isValid = nama && prodi && nim.length === 14 && telpon.length >= 10;

            // Check conditional fields
            if (selectedBookingType !== 'non_perkuliahan') {
                const mataKuliah = document.getElementById('mata_kuliah').value.trim();
                const dosen = document.getElementById('dosen_pengampu').value.trim();
                const nip = document.getElementById('nip_dosen').value.trim();
                isValid = isValid && mataKuliah && dosen && nip;
            } else {
                const namaKegiatan = document.getElementById('nama_kegiatan').value.trim();
                const jenisKegiatan = document.getElementById('jenis_kegiatan').value.trim();
                isValid = isValid && namaKegiatan && jenisKegiatan;
            }

            document.getElementById('btn-next-2').disabled = !isValid;
        }

        // Step 3 Validation
        function setupStep3Validation() {
            const tanggal = document.getElementById('tanggal');
            const jumlahPeserta = document.getElementById('jumlah_peserta');
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            const labSelect = document.getElementById('labSelect');

            [tanggal, jumlahPeserta, startTime, endTime].forEach(field => {
                field.addEventListener('change', function() {
                    fetchAvailableLabs();
                    validateStep3();
                });
            });

            labSelect.addEventListener('change', validateStep3);
        }

        function validateStep3() {
            const tanggal = document.getElementById('tanggal').value;
            const jumlahPeserta = document.getElementById('jumlah_peserta').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            const lab = document.getElementById('labSelect').value;

            const isValid = tanggal && jumlahPeserta && startTime && endTime && lab;
            document.getElementById('btn-next-3').disabled = !isValid;
        }

        // Fetch Available Labs
        function fetchAvailableLabs() {
            const tanggal = document.getElementById('tanggal').value;
            const jumlahPeserta = document.getElementById('jumlah_peserta').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            if (!tanggal || !jumlahPeserta || !startTime || !endTime) return;

            const labSelect = document.getElementById('labSelect');
            labSelect.innerHTML = '<option value="">Memuat...</option>';

            fetch('{{ route("booking.available-labs") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    tanggal: tanggal,
                    jumlah_peserta: jumlahPeserta,
                    start_time: startTime,
                    end_time: endTime
                })
            })
            .then(response => response.json())
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
                        option.textContent = `${lab.name} (Kapasitas: ${lab.capacity})`;
                        labSelect.appendChild(option);
                    });
                    labSelect.disabled = false;
                }
            });
        }

        // Navigation
        function setupNavigationButtons() {
            document.getElementById('btn-next-1').addEventListener('click', () => goToStep(2));
            document.getElementById('btn-next-2').addEventListener('click', () => goToStep(3));
            document.getElementById('btn-next-3').addEventListener('click', () => {
                generateSummary();
                goToStep(4);
            });
            
            document.getElementById('btn-prev-2').addEventListener('click', () => goToStep(1));
            document.getElementById('btn-prev-3').addEventListener('click', () => goToStep(2));
            document.getElementById('btn-prev-4').addEventListener('click', () => goToStep(3));
        }

        function goToStep(step) {
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
                'non_perkuliahan': 'Non-Perkuliahan'
            };

            const summary = [];
            summary.push(`<div><strong>Tipe:</strong> ${bookingTypeLabels[selectedBookingType]}</div>`);
            summary.push(`<div><strong>Nama:</strong> ${document.getElementById('nama_peminjam').value}</div>`);
            summary.push(`<div><strong>NIM:</strong> ${document.getElementById('nim').value}</div>`);
            summary.push(`<div><strong>Tanggal:</strong> ${document.getElementById('tanggal').value}</div>`);
            summary.push(`<div><strong>Waktu:</strong> ${document.getElementById('start_time').value} - ${document.getElementById('end_time').value}</div>`);
            summary.push(`<div><strong>Peserta:</strong> ${document.getElementById('jumlah_peserta').value} orang</div>`);
            
            const labSelect = document.getElementById('labSelect');
            const labName = labSelect.options[labSelect.selectedIndex].text;
            summary.push(`<div><strong>Lab:</strong> ${labName}</div>`);

            document.getElementById('booking-summary').innerHTML = summary.join('');
        }

        // File Upload
        function setupFileUpload() {
            document.getElementById('document').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const fileNameDisplay = document.getElementById('file-name');
                
                if (file) {
                    // Check file size (2MB = 2097152 bytes)
                    const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                    
                    if (file.size > maxSize) {
                        alert('⚠️ File terlalu besar!\n\nUkuran file: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB\nMaksimal: 2 MB\n\nSilakan compress file PDF Anda terlebih dahulu.');
                        this.value = ''; // Clear the file input
                        fileNameDisplay.textContent = '';
                        return;
                    }
                    
                    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
                    fileNameDisplay.textContent = `✓ Terpilih: ${file.name} (${fileSizeMB} MB)`;
                    fileNameDisplay.classList.add('text-green-600');
                } else {
                    fileNameDisplay.textContent = '';
                }
            });
        }

        // Add conditional required attributes
        document.addEventListener('DOMContentLoaded', function() {
            // Mark conditional fields
            const conditionalFields = [
                'mata_kuliah', 'dosen_pengampu', 'nip_dosen',
                'nama_kegiatan', 'jenis_kegiatan'
            ];
            conditionalFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.setAttribute('data-conditional-required', 'true');
                }
            });
        });
    </script>
</body>
</html>
