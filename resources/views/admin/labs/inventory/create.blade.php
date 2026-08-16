@extends('layouts.admin')

@section('title', 'Tambah Inventaris ' . $lab->name . ' - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.labs.inventory', $lab) }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Inventaris
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tambah Inventaris</h1>
        <p class="text-sm text-gray-600">{{ $lab->name }}</p>
    </div>

    <!-- Errors -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.labs.inventory.store', $lab) }}" method="POST" enctype="multipart/form-data" class="p-6" x-data="inventoryForm()">
            @csrf

            <!-- Step 1: Select Tracking Mode -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Unit *</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($trackingModes as $mode)
                        <label class="relative cursor-pointer h-full">
                            <input type="radio" name="tracking_mode" value="{{ $mode->value }}" 
                                x-model="trackingMode"
                                onchange="switchTrackingMode(this.value)"
                                class="peer sr-only" 
                                {{ old('tracking_mode', 'STRUCTURED_TAG') === $mode->value ? 'checked' : '' }}>
                            <div class="h-full p-4 border rounded-lg peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:border-gray-400 transition-all flex flex-col">
                                <div class="font-medium text-gray-800">{{ $mode->label() }}</div>
                                <div class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $mode->description() }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <hr class="my-6">

            <!-- Item Fields -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Aset Baru *</label>
                    <input type="text" name="new_item_name" value="{{ old('new_item_name') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                        placeholder="Contoh: Mouse Ajazz, Keyboard Asus, Router Cisco">
                </div>
                
                <!-- Category hanya untuk non-STRUCTURED_TAG -->
                <div id="item-category-section" x-show="trackingMode !== 'STRUCTURED_TAG'" style="display:none">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Barang *</label>
                    
                    <!-- Dropdown Kategori -->
                    <select 
                        id="category-select"
                        onchange="switchCategorySelect(this.value)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent mb-2">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="PC" {{ old('category') == 'PC' ? 'selected' : '' }}>PC</option>
                        <option value="Monitor" {{ old('category') == 'Monitor' ? 'selected' : '' }}>Monitor</option>
                        <option value="Keyboard" {{ old('category') == 'Keyboard' ? 'selected' : '' }}>Keyboard</option>
                        <option value="Mouse" {{ old('category') == 'Mouse' ? 'selected' : '' }}>Mouse</option>
                        <option value="TV" {{ old('category') == 'TV' ? 'selected' : '' }}>TV</option>
                        <option value="Laptop" {{ old('category') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
                        <option value="Printer" {{ old('category') == 'Printer' ? 'selected' : '' }}>Printer</option>
                        <option value="Scanner" {{ old('category') == 'Scanner' ? 'selected' : '' }}>Scanner</option>
                        <option value="Router" {{ old('category') == 'Router' ? 'selected' : '' }}>Router</option>
                        <option value="Switch" {{ old('category') == 'Switch' ? 'selected' : '' }}>Switch</option>
                        <option value="AC" {{ old('category') == 'AC' ? 'selected' : '' }}>AC</option>
                        <option value="Proyektor" {{ old('category') == 'Proyektor' ? 'selected' : '' }}>Proyektor</option>
                        <option value="Bracket TV" {{ old('category') == 'Bracket TV' ? 'selected' : '' }}>Bracket TV</option>
                        <option value="Meja" {{ old('category') == 'Meja' ? 'selected' : '' }}>Meja</option>
                        <option value="Kursi" {{ old('category') == 'Kursi' ? 'selected' : '' }}>Kursi</option>
                        @foreach($customCategories ?? [] as $cat)
                            <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                        <option value="custom">Lainnya</option>
                    </select>
                    
                    <!-- Input untuk kategori custom -->
                    <div id="custom-category-section" class="mt-2" style="display:none">
                        <input 
                            type="text" 
                            id="custom-category-input"
                            oninput="document.getElementById('category-hidden').value = this.value"
                            placeholder="Masukkan kategori baru..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Kategori baru akan disimpan dan bisa digunakan lagi</p>
                    </div>
                    
                    <!-- Hidden input untuk submit -->
                    <input type="hidden" id="category-hidden" name="category" value="{{ old('category') }}">
                    
                    <p class="text-xs text-gray-500 mt-1">Pilih dari daftar atau buat kategori baru</p>
                </div>

                <!-- Item Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Barang (Opsional)</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" onchange="previewItemImage(this)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-yellow-500 file:text-white file:font-semibold hover:file:bg-yellow-600 cursor-pointer">
                    <div class="mt-3 hidden" id="item-image-preview-wrap">
                        <img id="item-image-preview" class="w-32 h-32 object-cover rounded-lg border border-gray-200" alt="Pratinjau foto barang">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WebP (maks 5MB). Resolusi besar akan otomatis diperkecil menjadi maks 1280px.</p>
                </div>
            </div>

            <!-- Asset Type Code (for Structured Tag) -->
            <div id="asset-type-code-section" x-show="trackingMode === 'STRUCTURED_TAG'" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Tipe Aset UPK <span class="text-gray-400 text-xs font-normal">(opsional)</span></label>
                
                <!-- Mode Selection -->
                <div class="flex flex-wrap gap-4 mb-3">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="asset_type_code_mode" value="kosong" onchange="toggleAssetTypeCodeMode(this.value)" {{ old('asset_type_code_mode', 'kosong') === 'kosong' ? 'checked' : '' }} class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                        <span class="ml-2 text-sm text-gray-700">Kosongkan</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="asset_type_code_mode" value="pilih" onchange="toggleAssetTypeCodeMode(this.value)" {{ old('asset_type_code_mode', 'kosong') === 'pilih' ? 'checked' : '' }} class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                        <span class="ml-2 text-sm text-gray-700">Pilih dari Daftar</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="asset_type_code_mode" value="manual" onchange="toggleAssetTypeCodeMode(this.value)" {{ old('asset_type_code_mode', 'kosong') === 'manual' ? 'checked' : '' }} class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                        <span class="ml-2 text-sm text-gray-700">Isi Manual</span>
                    </label>
                </div>

                <!-- Dropdown (Pilih dari Daftar) -->
                <div id="atc-pilih" style="display:none">
                    <select name="asset_type_code" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        <option value="">-- Pilih Kode Tipe --</option>
                        <option value="H3" {{ old('asset_type_code') == 'H3' ? 'selected' : '' }}>H3 - PC AIO</option>
                        <option value="I2" {{ old('asset_type_code') == 'I2' ? 'selected' : '' }}>I2 - TV</option>
                        <option value="BRK" {{ old('asset_type_code') == 'BRK' ? 'selected' : '' }}>BRK - Bracket</option>
                        <option value="J1" {{ old('asset_type_code') == 'J1' ? 'selected' : '' }}>J1 - Speaker</option>
                        <option value="O1" {{ old('asset_type_code') == 'O1' ? 'selected' : '' }}>O1 - Laptop</option>
                        <option value="L1" {{ old('asset_type_code') == 'L1' ? 'selected' : '' }}>L1 - Printer</option>
                        <option value="P" {{ old('asset_type_code') == 'P' ? 'selected' : '' }}>P - Samsung Tab</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kode tipe untuk format tag (H3=PC AIO, O1=Laptop, dll)</p>
                </div>

                <!-- Manual Input -->
                <div id="atc-manual" style="display:none">
                    <input type="text" name="manual_asset_tag_prefix" 
                        value="{{ old('manual_asset_tag_prefix') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                        placeholder="Contoh: H3.01.1023">
                    <p class="text-xs text-gray-500 mt-1">Masukkan kode aset lengkap tanpa nomor urut. Nomor urut akan ditambahkan otomatis (misal: 01.0226.H3.EL 301)</p>
                </div>

                <!-- Empty info -->
                <div id="atc-kosong">
                    <p class="text-xs text-gray-500 italic">Kode tipe aset akan dikosongkan</p>
                </div>
            </div>

            <hr class="my-6">

            <!-- Batch Fields -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Source Code Field -->
                    <div id="batch-source-code" x-show="trackingMode === 'STRUCTURED_TAG'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Sumber Pengadaan *</label>
                        <select name="proc_source_code" :disabled="trackingMode !== 'STRUCTURED_TAG'" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <option value="01" {{ old('proc_source_code') == '01' ? 'selected' : '' }}>01 - Universitas</option>
                            <option value="02" {{ old('proc_source_code') == '02' ? 'selected' : '' }}>02 - Fakultas</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Sumber dana pengadaan</p>
                    </div>

                    <!-- Hidden input moved here to not mess up grid -->
                    <input type="hidden" id="hidden-proc-source" name="proc_source_code" value="01" disabled>

                    <!-- Arrival Date (MMYY) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Datang *</label>
                        
                        <!-- Format 1023 (Structured Tag) -->
                        <div id="batch-arrival-structured" x-show="trackingMode === 'STRUCTURED_TAG'" style="display:none">
                            <input type="text" name="arrival_mmyy_text" value="{{ old('arrival_mmyy_text', '1023') }}" 
                                :disabled="trackingMode !== 'STRUCTURED_TAG'"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="1023" maxlength="4">
                            <p class="text-xs text-gray-500 mt-1">Format: MMYY (Contoh: 1023)</p>
                        </div>

                        <!-- Format Dropdown (Other Modes) -->
                        <div id="batch-arrival-other" x-show="trackingMode !== 'STRUCTURED_TAG'" class="grid grid-cols-2 gap-2" style="display:none">
                            <div>
                                <select name="arrival_month" :disabled="trackingMode === 'STRUCTURED_TAG'" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="" disabled selected>Bulan</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ old('arrival_month') == sprintf('%02d', $m) ? 'selected' : '' }}>
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="number" name="arrival_year" value="{{ old('arrival_year', date('Y')) }}" 
                                    :disabled="trackingMode === 'STRUCTURED_TAG'"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                    placeholder="Tahun (YYYY)" min="2000" max="2100">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specification Field -->
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Merk (Opsional)</label>
                     <input type="text" name="brand" value="{{ old('brand') }}" 
                         class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                         placeholder="Contoh: Dell, HP, Asus, Logitech">
                     <p class="text-xs text-gray-500 mt-1">Merk/brand dari barang</p>
                </div>
                
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Spesifikasi (Opsional)</label>
                     <input type="text" name="item_description" value="{{ old('item_description') }}" 
                         class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                         placeholder="Contoh: RAM 8GB, SSD 512GB">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Sumber (Opsional)</label>
                        <input type="text" name="source_description" value="{{ old('source_description') }}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                            placeholder="APBN / Hibah / dll">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Unit (Rp) (Opsional)</label>
                        <input type="number" name="unit_price" value="{{ old('unit_price') }}" step="0.01" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                            placeholder="15000000">
                    </div>
                </div>
            </div>

            <hr class="my-6">

            <!-- Mode-specific Fields -->
            <!-- Structured Tag Fields -->
            <div id="fields-structured-tag" x-show="trackingMode === 'STRUCTURED_TAG'" class="mb-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Unit *</label>
                        <input type="number" id="qty-structured" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="999"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mulai dari Seq</label>
                        <input type="number" name="start_seq" value="{{ old('start_seq') }}" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                            placeholder="Auto">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk auto</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtype</label>
                        <select name="subtype" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            <option value="">-- Tidak Ada --</option>
                            <option value="ADMIN" {{ old('subtype') === 'ADMIN' ? 'selected' : '' }}>ADMIN (PC Admin)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Untuk PC Admin/khusus</p>
                    </div>
                </div>
            </div>

            <!-- Seat Number Fields -->
            <div id="fields-seat-number" x-show="trackingMode === 'SEAT_NUMBER'" class="mb-6 space-y-4" style="display:none">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Unit *</label>
                        <input type="number" id="qty-seat" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="999" disabled
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mulai dari Nomor</label>
                        <input type="number" name="start_seat" value="{{ old('start_seat') }}" min="1"
                            :disabled="trackingMode !== 'SEAT_NUMBER'"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                            placeholder="Auto">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk lanjut dari nomor terakhir</p>
                    </div>
                </div>
            </div>

            <!-- Aggregate Fields -->
            <div id="fields-aggregate" x-show="trackingMode === 'AGGREGATE'" class="mb-6" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah *</label>
                <input type="number" id="qty-aggregate" name="quantity" value="{{ old('quantity', 1) }}" min="1" disabled
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <!-- University Asset Code Field (All Modes) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Aset Universitas <span class="text-gray-400 text-xs font-normal">(opsional)</span>
                </label>
                <input type="text" name="university_asset_code_prefix" value="{{ old('university_asset_code_prefix') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <hr class="my-6">

            <!-- Condition -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi Awal *</label>
                <select name="condition" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    @foreach($conditions as $condition)
                        <option value="{{ $condition->value }}" {{ old('condition', 'BAIK') === $condition->value ? 'selected' : '' }}>
                            {{ $condition->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.labs.inventory', $lab) }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function inventoryForm() {
            return {
                trackingMode: '{{ old('tracking_mode', 'STRUCTURED_TAG') }}',
            }
        }

        function switchCategorySelect(val) {
            document.getElementById('custom-category-section').style.display = (val === 'custom') ? '' : 'none';
            if (val !== 'custom') {
                document.getElementById('category-hidden').value = val;
            } else {
                document.getElementById('category-hidden').value = document.getElementById('custom-category-input').value;
            }
        }

        function switchTrackingMode(mode) {
            var map = {
                'item-category-section':    mode !== 'STRUCTURED_TAG',
                'asset-type-code-section':  mode === 'STRUCTURED_TAG',
                'batch-source-code':        mode === 'STRUCTURED_TAG',
                'batch-arrival-structured': mode === 'STRUCTURED_TAG',
                'batch-arrival-other':      mode !== 'STRUCTURED_TAG',
                'fields-structured-tag':    mode === 'STRUCTURED_TAG',
                'fields-seat-number':       mode === 'SEAT_NUMBER',
                'fields-aggregate':         mode === 'AGGREGATE',
            };
            Object.entries(map).forEach(function([id, show]) {
                var el = document.getElementById(id);
                if (el) el.style.display = show ? '' : 'none';
            });
            // Disable inactive quantity inputs so they don't override the active one on submit
            document.getElementById('qty-structured').disabled = (mode !== 'STRUCTURED_TAG');
            document.getElementById('qty-seat').disabled       = (mode !== 'SEAT_NUMBER');
            document.getElementById('qty-aggregate').disabled  = (mode !== 'AGGREGATE');
            // Disable proc_source_code hidden input when STRUCTURED_TAG (the select handles it instead)
            document.getElementById('hidden-proc-source').disabled = (mode === 'STRUCTURED_TAG');
        }

        function toggleAssetTypeCodeMode(mode) {
            document.getElementById('atc-kosong').style.display = mode === 'kosong' ? '' : 'none';
            document.getElementById('atc-pilih').style.display  = mode === 'pilih'  ? '' : 'none';
            document.getElementById('atc-manual').style.display = mode === 'manual' ? '' : 'none';
        }

        function previewItemImage(input) {
            var wrap = document.getElementById('item-image-preview-wrap');
            var img = document.getElementById('item-image-preview');
            if (input.files && input.files[0]) {
                img.src = URL.createObjectURL(input.files[0]);
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
                img.src = '';
            }
        }

        // Init on page load (handles old() re-render after validation error)
        (function() {
            var initMode = '{{ old('tracking_mode', 'STRUCTURED_TAG') }}';
            switchTrackingMode(initMode);

            var initAtcMode = '{{ old('asset_type_code_mode', 'kosong') }}';
            if (initAtcMode !== 'kosong') toggleAssetTypeCodeMode(initAtcMode);

            // Restore category select state after validation error
            var oldCat = '{{ old('category') }}';
            if (oldCat) {
                var catSel = document.getElementById('category-select');
                if (catSel) {
                    var matched = Array.from(catSel.options).some(function(o) { return o.value === oldCat; });
                    if (!matched) {
                        // Custom value not in list → show custom input, set select to 'custom'
                        catSel.value = 'custom';
                        document.getElementById('custom-category-section').style.display = '';
                        document.getElementById('custom-category-input').value = oldCat;
                    }
                }
            }
        })();
    </script>
    @endpush
@endsection
