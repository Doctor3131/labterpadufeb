<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peminjaman Aset Lab - FEB UNDIP</title>
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e5e7eb;
            color: #6b7280;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .item-row {
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: white;
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
                <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 md:px-6 py-2 rounded-lg font-bold transition-all shadow-sm hover:shadow-md text-sm md:text-base">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 md:px-6 py-4 md:py-12 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-6 md:mb-10">
            <h1 class="text-2xl md:text-4xl font-bold text-gray-800 mb-2 md:mb-3">Peminjaman Aset Laboratorium</h1>
            <p class="text-sm md:text-base text-gray-600 px-4">Ajukan peminjaman peralatan dan aset laboratorium</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-lg p-4 md:p-8 mb-8">
            <!-- Progress Indicator -->
            <div class="step-indicator mb-8">
                <div class="step-item active" id="step-indicator-1">
                    <div class="step-number">1</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Data Peminjam</div>
                </div>
                <div class="step-item" id="step-indicator-2">
                    <div class="step-number">2</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Pilih Aset</div>
                </div>
                <div class="step-item" id="step-indicator-3">
                    <div class="step-number">3</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Jadwal</div>
                </div>
                <div class="step-item" id="step-indicator-4">
                    <div class="step-number">4</div>
                    <div class="text-xs md:text-sm font-medium hidden md:block">Submit</div>
                </div>
            </div>

            <form action="{{ route('asset-borrowing.store') }}" method="POST" enctype="multipart/form-data" id="borrowingForm">
                @csrf

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <p class="font-semibold mb-2">Terjadi kesalahan:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- STEP 1: Data Peminjam -->
                <div id="step-1" class="step-section">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">1</span>
                        Data Peminjam
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" name="borrower_name" value="{{ old('borrower_name') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Nama lengkap peminjam">
                            @error('borrower_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Peminjam *</label>
                            <select name="borrower_type" id="borrower_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Pilih Status</option>
                                <option value="Mahasiswa" {{ old('borrower_type') == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="Dosen" {{ old('borrower_type') == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="Staff" {{ old('borrower_type') == 'Staff' ? 'selected' : '' }}>Staff</option>
                                <option value="Lainnya" {{ old('borrower_type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <!-- Conditional Fields -->
                    <div id="mahasiswa-fields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NIM</label>
                            <input type="text" name="borrower_id_number" value="{{ old('borrower_id_number') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Nomor Induk Mahasiswa">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Program Studi</label>
                            <input type="text" name="study_program" value="{{ old('study_program') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Contoh: Akuntansi">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Angkatan</label>
                            <input type="text" name="class_year" value="{{ old('class_year') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Contoh: 2023" maxlength="4">
                        </div>
                    </div>

                    <div id="staff-fields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                            <input type="text" name="borrower_id_number" value="{{ old('borrower_id_number') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Nomor Induk Pegawai">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                            <input type="text" name="position" value="{{ old('position') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="Contoh: Dosen/Staf Administrasi">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP *</label>
                            <input type="tel" name="phone_number" value="{{ old('phone_number') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                placeholder="email@example.com">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="nextStep(1)" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md hover:shadow-lg">
                            Selanjutnya →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Pilih Aset -->
                <div id="step-2" class="step-section hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">2</span>
                        Pilih Aset yang Dipinjam
                    </h3>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Lab Asal *</label>
                        <select name="lab_id" id="lab_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">Pilih Lab</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}" {{ old('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                            @endforeach
                        </select>
                        @error('lab_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="items-container">
                        <!-- Items will be added here dynamically -->
                    </div>

                    <button type="button" id="add-item-btn" class="mb-6 inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Barang
                    </button>

                    <div class="flex justify-between">
                        <button type="button" onclick="prevStep(2)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-bold transition-all">
                            ← Sebelumnya
                        </button>
                        <button type="button" onclick="nextStep(2)" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md hover:shadow-lg">
                            Selanjutnya →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Jadwal & Tujuan -->
                <div id="step-3" class="step-section hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">3</span>
                        Jadwal & Tujuan Peminjaman
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Pinjam *</label>
                            <input type="date" name="borrow_date" value="{{ old('borrow_date') }}" required
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Pinjam</label>
                            <input type="time" name="borrow_time" value="{{ old('borrow_time') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Kembali *</label>
                            <input type="date" name="return_date" value="{{ old('return_date') }}" required
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Kembali</label>
                            <input type="time" name="return_time" value="{{ old('return_time') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tujuan Peminjaman *</label>
                        <textarea name="purpose" rows="4" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                            placeholder="Jelaskan tujuan peminjaman aset">{{ old('purpose') }}</textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Dokumen (KTM/KTP/Surat)</label>
                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG (Maks 5MB)</p>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" onclick="prevStep(3)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-bold transition-all">
                            ← Sebelumnya
                        </button>
                        <button type="button" onclick="nextStep(3)" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md hover:shadow-lg">
                            Selanjutnya →
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Konfirmasi -->
                <div id="step-4" class="step-section hidden">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">4</span>
                        Konfirmasi Data
                    </h3>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                        <p class="text-sm text-gray-700 mb-4">
                            <strong>Perhatian:</strong> Pastikan semua data yang Anda masukkan sudah benar. 
                            Pengajuan yang sudah disubmit tidak dapat diubah.
                        </p>
                        <div id="confirmation-summary" class="text-sm text-gray-700 space-y-2">
                            <!-- Will be filled by JS -->
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" onclick="prevStep(4)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-bold transition-all">
                            ← Sebelumnya
                        </button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md hover:shadow-lg">
                            Submit Pengajuan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let itemCounter = 0;
        const borrowableItems = @json($borrowableItems);

        // Handle borrower type change
        document.getElementById('borrower_type').addEventListener('change', function() {
            const mahasiswaFields = document.getElementById('mahasiswa-fields');
            const staffFields = document.getElementById('staff-fields');
            
            // Store original name attributes and clear all hidden fields
            mahasiswaFields.querySelectorAll('input').forEach(input => {
                if (!input.dataset.originalName) {
                    input.dataset.originalName = input.getAttribute('name');
                }
                input.removeAttribute('name');
                input.value = '';
            });
            staffFields.querySelectorAll('input').forEach(input => {
                if (!input.dataset.originalName) {
                    input.dataset.originalName = input.getAttribute('name');
                }
                input.removeAttribute('name');
                input.value = '';
            });
            
            mahasiswaFields.classList.add('hidden');
            staffFields.classList.add('hidden');
            
            if (this.value === 'Mahasiswa') {
                mahasiswaFields.classList.remove('hidden');
                // Restore name attributes for visible inputs
                mahasiswaFields.querySelectorAll('input').forEach(input => {
                    if (input.dataset.originalName) {
                        input.setAttribute('name', input.dataset.originalName);
                    }
                });
            } else if (this.value === 'Dosen' || this.value === 'Staff') {
                staffFields.classList.remove('hidden');
                // Restore name attributes for visible inputs
                staffFields.querySelectorAll('input').forEach(input => {
                    if (input.dataset.originalName) {
                        input.setAttribute('name', input.dataset.originalName);
                    }
                });
            }
        });

        // Add item row
        document.getElementById('add-item-btn').addEventListener('click', function() {
            const labId = document.getElementById('lab_id').value;
            if (!labId) {
                alert('Pilih lab terlebih dahulu!');
                return;
            }
            
            addItemRow();
        });

        function addItemRow() {
            const container = document.getElementById('items-container');
            const labId = document.getElementById('lab_id').value;
            const currentIndex = container.children.length; // Use 0-based index for Laravel
            itemCounter++; // For display purposes only
            
            const itemRow = document.createElement('div');
            itemRow.className = 'item-row';
            itemRow.id = `item-row-${itemCounter}`;
            itemRow.dataset.index = currentIndex; // Store the actual array index
            itemRow.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <h4 class="font-semibold text-gray-800">Barang #${itemCounter}</h4>
                    <button type="button" onclick="removeItemRow(${itemCounter})" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Barang</label>
                        <select name="items[${currentIndex}][item_id]" class="item-select w-full px-4 py-3 border border-gray-300 rounded-lg" required onchange="handleItemSelect(${itemCounter}, ${currentIndex}, this.value)">
                            <option value="">Pilih Barang</option>
                            ${borrowableItems.map(item => `<option value="${item.id}" data-tracking="${item.tracking_mode}">${item.name}</option>`).join('')}
                        </select>
                    </div>
                    <div id="unit-selector-${itemCounter}" class="md:col-span-2 hidden">
                        <!-- Will be filled dynamically -->
                    </div>
                </div>
            `;
            
            container.appendChild(itemRow);
        }

        function removeItemRow(id) {
            const row = document.getElementById(`item-row-${id}`);
            if (row) {
                row.remove();
                // Re-index remaining items
                reindexItems();
            }
        }

        function reindexItems() {
            const container = document.getElementById('items-container');
            const rows = container.querySelectorAll('.item-row');
            
            rows.forEach((row, index) => {
                // Update the data-index
                row.dataset.index = index;
                
                // Update item_id field name
                const itemIdSelect = row.querySelector('select[name^="items"]');
                if (itemIdSelect) {
                    const currentName = itemIdSelect.name;
                    itemIdSelect.name = currentName.replace(/items\[\d+\]/, `items[${index}]`);
                }
                
                // Update quantity or asset_unit_id field name if exists
                const dynamicFields = row.querySelectorAll('input[name^="items"], select[name^="items"]:not(.item-select)');
                dynamicFields.forEach(field => {
                    const currentName = field.name;
                    field.name = currentName.replace(/items\[\d+\]/, `items[${index}]`);
                });
            });
        }

        async function handleItemSelect(displayId, arrayIndex, itemId) {
            const labId = document.getElementById('lab_id').value;
            const unitSelector = document.getElementById(`unit-selector-${displayId}`);
            
            if (!itemId) {
                unitSelector.classList.add('hidden');
                return;
            }

            try {
                const response = await fetch(`/asset-borrowing/available-assets?lab_id=${labId}&item_id=${itemId}`);
                const data = await response.json();
                
                unitSelector.classList.remove('hidden');
                
                if (data.type === 'aggregate') {
                    unitSelector.innerHTML = `
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="items[${arrayIndex}][quantity]" min="1" max="${data.available_quantity}" value="1" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">Tersedia: ${data.available_quantity} unit</p>
                    `;
                } else {
                    unitSelector.innerHTML = `
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Unit</label>
                        <select name="items[${arrayIndex}][asset_unit_id]" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="">Pilih Unit</option>
                            ${data.units.map(unit => `<option value="${unit.id}">${unit.asset_tag}${unit.subtype ? ' (' + unit.subtype + ')' : ''}</option>`).join('')}
                        </select>
                    `;
                }
            } catch (error) {
                console.error('Error fetching available assets:', error);
                alert('Gagal memuat data aset');
            }
        }

        function nextStep(step) {
            console.log('Moving to next step from:', step);
            
            // Validation
            const currentStepEl = document.getElementById(`step-${step}`);
            const inputs = currentStepEl.querySelectorAll('input[required], select[required], textarea[required]');
            let valid = true;
            let invalidFields = [];
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    invalidFields.push(input.name || 'unnamed field');
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (!valid) {
                console.log('Validation failed. Invalid fields:', invalidFields);
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return;
            }

            // Special validation for step 2
            if (step === 2) {
                const itemsContainer = document.getElementById('items-container');
                if (itemsContainer.children.length === 0) {
                    alert('Tambahkan minimal 1 barang!');
                    return;
                }
            }

            console.log('Validation passed, moving to step:', step + 1);
            
            document.getElementById(`step-${step}`).classList.add('hidden');
            document.getElementById(`step-${step + 1}`).classList.remove('hidden');
            
            document.getElementById(`step-indicator-${step}`).classList.add('completed');
            document.getElementById(`step-indicator-${step + 1}`).classList.add('active');
            
            if (step + 1 === 4) {
                showConfirmation();
            }
            
            currentStep = step + 1;
            window.scrollTo(0, 0);
        }

        function prevStep(step) {
            document.getElementById(`step-${step}`).classList.add('hidden');
            document.getElementById(`step-${step - 1}`).classList.remove('hidden');
            
            document.getElementById(`step-indicator-${step}`).classList.remove('active');
            document.getElementById(`step-indicator-${step - 1}`).classList.remove('completed');
            
            currentStep = step - 1;
            window.scrollTo(0, 0);
        }

        function showConfirmation() {
            const summary = document.getElementById('confirmation-summary');
            const borrowerName = document.querySelector('input[name="borrower_name"]').value;
            const borrowerType = document.querySelector('select[name="borrower_type"]').value;
            const phone = document.querySelector('input[name="phone_number"]').value;
            const borrowDate = document.querySelector('input[name="borrow_date"]').value;
            const returnDate = document.querySelector('input[name="return_date"]').value;
            const purpose = document.querySelector('textarea[name="purpose"]').value;
            
            const itemsContainer = document.getElementById('items-container');
            const itemCount = itemsContainer.children.length;
            
            summary.innerHTML = `
                <p><strong>Nama:</strong> ${borrowerName}</p>
                <p><strong>Status:</strong> ${borrowerType}</p>
                <p><strong>No. HP:</strong> ${phone}</p>
                <p><strong>Tanggal Pinjam:</strong> ${borrowDate}</p>
                <p><strong>Tanggal Kembali:</strong> ${returnDate}</p>
                <p><strong>Jumlah Barang:</strong> ${itemCount} item</p>
                <p><strong>Tujuan:</strong> ${purpose}</p>
            `;
        }

        // Initialize with one item row if form is fresh
        window.addEventListener('DOMContentLoaded', function() {
            const labSelect = document.getElementById('lab_id');
            labSelect.addEventListener('change', function() {
                document.getElementById('items-container').innerHTML = '';
                itemCounter = 0;
            });

            // Form submission logging
            const form = document.getElementById('borrowingForm');
            form.addEventListener('submit', function(e) {
                console.log('Form is being submitted...');
                console.log('Form action:', this.action);
                console.log('Form method:', this.method);
                
                // Check if items exist
                const itemsContainer = document.getElementById('items-container');
                console.log('Number of items:', itemsContainer.children.length);
                
                // Log form data
                const formData = new FormData(this);
                console.log('Form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
            });
        });
    </script>
</body>
</html>
