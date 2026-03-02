@extends('layouts.admin')

@section('title', 'Detail Peminjaman Barang')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-sm mb-3" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-sm mb-3" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Status & Actions -->
        <div class="bg-white shadow-sm rounded-lg mb-3">
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-start gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-lg font-bold text-gray-800">Peminjaman #{{ $borrowing->id }}</h2>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'borrowed' => 'bg-blue-100 text-blue-800',
                                    'returned' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $statusColors[$borrowing->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($borrowing->status) }}
                            </span>
                            @if($borrowing->document_number)
                                <span class="text-xs text-gray-600">No: {{ $borrowing->document_number }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex gap-2 flex-wrap flex-shrink-0">
                        @if($borrowing->status === 'pending' && $borrowing->generated_document_path)
                            <form action="{{ route('admin.asset-borrowings.approve', $borrowing->id) }}" method="POST">
                                @csrf
                                <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all duration-200" onclick="confirmForm(this.closest('form'), 'Setujui peminjaman ini?')">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Setujui
                                    </span>
                                </button>
                            </form>
                            <button onclick="openRejectModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all duration-200">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </span>
                            </button>
                        @endif

                        @if($borrowing->generated_document_path)
                            <a href="{{ route('admin.asset-borrowings.preview', $borrowing->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all duration-200 inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Data PIHAK PERTAMA (Admin) -->
        <div class="bg-white shadow-sm rounded-lg mb-3">
            <div class="px-4 py-3">
                <h3 class="text-base font-bold text-gray-800 mb-2.5 pb-2 border-b">Data Penanggung Jawab (PIHAK PERTAMA)</h3>
                
                <form id="first-party-form" action="{{ route('admin.asset-borrowings.update-first-party', $borrowing->id) }}" method="POST" class="space-y-2.5">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama *</label>
                        <input type="text" name="first_party_name" value="{{ old('first_party_name', $borrowing->first_party_name) }}" required
                            class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jabatan *</label>
                        <input type="text" name="first_party_position" value="{{ old('first_party_position', $borrowing->first_party_position) }}" required
                            class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: Asisten UPK">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat *</label>
                        <input type="text" name="first_party_address" value="{{ old('first_party_address', $borrowing->first_party_address) }}" required
                            class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: Semarang">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Telepon Kantor *</label>
                        <input type="text" name="first_party_phone" value="{{ old('first_party_phone', $borrowing->first_party_phone) }}" required
                            class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: +62 877-4119-1305">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Surat</label>
                        <input type="date" name="document_date" value="{{ old('document_date', $borrowing->document_date ? $borrowing->document_date->format('Y-m-d') : '') }}"
                            class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-0.5">Kosongkan untuk menggunakan tanggal hari ini</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Surat</label>
                        <input type="text" name="document_number" value="{{ old('document_number', $borrowing->document_number) }}"
                            class="w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: 001/SPB/UPKFEB/II/2026">
                        <p class="text-xs text-gray-500 mt-0.5">Kosongkan untuk generate nomor otomatis</p>
                    </div>

                    <!-- Grid 2 kolom untuk Daftar Barang dan Data Peminjam -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:start;" class="border-t pt-3 mt-3">
                        <!-- ===== DAFTAR BARANG PADA SURAT ===== -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-700">Daftar Barang pada Surat</label>
                                <button type="button" onclick="addItemRow()"
                                    class="text-xs bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded font-semibold">
                                    + Tambah Baris
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mb-1">
                                Isi daftar barang yang akan muncul di surat. Satu nama barang bisa dipecah menjadi beberapa baris untuk merek berbeda.
                            </p>
                            
                            @php
                                $borrowedSummary = $borrowing->borrowedItems
                                    ->groupBy(function($bi) {
                                        return $bi->item->category ?? $bi->item->name;
                                    })->map(function($group) {
                                        return $group->sum('quantity');
                                    });
                            @endphp
                            <div class="text-xs bg-blue-50 border border-blue-200 rounded px-2 py-1.5 mb-2">
                                <span class="font-semibold text-blue-800">Jumlah dipinjam:</span>
                                @foreach($borrowedSummary as $name => $qty)
                                    <span class="inline-block ml-2 text-blue-700">{{ $name }}: <strong>{{ $qty }}</strong></span>
                                @endforeach
                            </div>

                            <div id="items-container" class="space-y-2">
                                {{-- Rows will be rendered by JS below --}}
                            </div>
                        </div>
                        <!-- ===== END DAFTAR BARANG ===== -->

                        <!-- Data Peminjam (PIHAK KEDUA) -->
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                            <h4 class="text-xs font-bold text-gray-800 mb-2 pb-2 border-b border-gray-300">Data Peminjam (PIHAK KEDUA)</h4>
                            
                            <div class="space-y-1.5 text-sm">
                                <div class="grid grid-cols-3 gap-2">
                                    <span class="font-semibold text-gray-600 text-xs">Nama:</span>
                                    <span class="col-span-2 text-gray-900 text-xs">{{ $borrowing->borrower_name }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <span class="font-semibold text-gray-600 text-xs">Jabatan/Status:</span>
                                    <span class="col-span-2 text-gray-900 text-xs">{{ $borrowing->borrower_type }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <span class="font-semibold text-gray-600 text-xs">Alamat:</span>
                                    <span class="col-span-2 text-gray-900 text-xs">{{ $borrowing->borrower_address ?? '-' }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <span class="font-semibold text-gray-600 text-xs">Telepon:</span>
                                    <span class="col-span-2 text-gray-900 text-xs">{{ $borrowing->phone_number }}</span>
                                </div>
                                @if($borrowing->email)
                                <div class="grid grid-cols-3 gap-2">
                                    <span class="font-semibold text-gray-600 text-xs">Email:</span>
                                    <span class="col-span-2 text-gray-900 text-xs">{{ $borrowing->email }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Hidden template row (cloned by JS) --}}
                    <template id="item-row-template">
                        <div class="item-row border border-gray-200 rounded p-2 bg-gray-50 relative">
                            <button type="button" onclick="removeItemRow(this)"
                                class="absolute top-1.5 right-1.5 text-red-400 hover:text-red-600 text-xs font-bold leading-none"
                                title="Hapus baris">✕</button>

                            <div class="grid grid-cols-2 gap-1.5 mb-1.5">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">Nama Barang *</label>
                                    <input type="text" data-field="name" required
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-indigo-400"
                                        placeholder="Contoh: Mouse">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">Merk/Tipe</label>
                                    <input type="text" data-field="brand_type"
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-indigo-400"
                                        placeholder="Contoh: Ajazz">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-1.5 mb-1.5">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">Jumlah *</label>
                                    <input type="number" data-field="quantity" min="1" required
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-indigo-400"
                                        placeholder="1" value="1">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-600 mb-0.5">Keterangan</label>
                                    <input type="text" data-field="remarks"
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-indigo-400"
                                        placeholder="Opsional">
                                </div>
                            </div>

                            {{-- Kondisi: hidden inputs as actual submitted values, checkboxes as UI only --}}
                            <div class="flex flex-wrap gap-3">
                                <label class="flex items-center gap-1 text-xs cursor-pointer">
                                    <input type="hidden" data-field="condition_good" value="1">
                                    <input type="checkbox" data-mirror="condition_good" checked
                                        class="w-3.5 h-3.5 text-indigo-600 rounded"
                                        onchange="this.previousElementSibling.value = this.checked ? '1' : '0'">
                                    <span class="text-gray-700">Baik</span>
                                </label>
                                <label class="flex items-center gap-1 text-xs cursor-pointer">
                                    <input type="hidden" data-field="condition_adequate" value="0">
                                    <input type="checkbox" data-mirror="condition_adequate"
                                        class="w-3.5 h-3.5 text-indigo-600 rounded"
                                        onchange="this.previousElementSibling.value = this.checked ? '1' : '0'">
                                    <span class="text-gray-700">Cukup</span>
                                </label>
                                <label class="flex items-center gap-1 text-xs cursor-pointer">
                                    <input type="hidden" data-field="condition_complete" value="1">
                                    <input type="checkbox" data-mirror="condition_complete" checked
                                        class="w-3.5 h-3.5 text-indigo-600 rounded"
                                        onchange="this.previousElementSibling.value = this.checked ? '1' : '0'">
                                    <span class="text-gray-700">Lengkap</span>
                                </label>
                            </div>
                        </div>
                    </template>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded font-semibold text-sm mt-3">
                        {{ $borrowing->generated_document_path ? 'Update & Generate Ulang Surat' : 'Simpan & Generate Surat' }}
                    </button>
                </form>

                    @php
                        // Pre-compute item seed data for JS
                        if (!empty($borrowing->items_override)) {
                            $jsSeedItems = collect($borrowing->items_override)->values();
                        } else {
                            $jsSeedItems = $borrowing->borrowedItems
                                ->groupBy(function($bi) {
                                    return ($bi->item->category ?? $bi->item->name)
                                         . '||'
                                         . ($bi->brand_type ?? $bi->item->brand ?? $bi->item->name ?? '-')
                                         . '||'
                                         . $bi->item_id;
                                })->map(function($group) {
                                    $first = $group->first();
                                    return [
                                        'name'               => $first->item->category ?? $first->item->name,
                                        'brand_type'         => $first->brand_type ?? $first->item->brand ?? '',
                                        'quantity'           => $group->sum('quantity'),
                                        'condition_good'     => (bool) $first->condition_good,
                                        'condition_adequate' => (bool) $first->condition_adequate,
                                        'condition_complete' => (bool) $first->condition_complete,
                                        'remarks'            => $first->remarks ?? '',
                                    ];
                                })->values();
                        }
                        
                        // Calculate total borrowed quantity per item category (for validation)
                        $borrowedTotals = $borrowing->borrowedItems
                            ->groupBy(function($bi) {
                                return strtolower(trim($bi->item->category ?? $bi->item->name));
                            })->map(function($group) {
                                return $group->sum('quantity');
                            });
                    @endphp
                    <script>
                        const existingItems = @json($jsSeedItems);
                        const borrowedTotals = @json($borrowedTotals); // e.g. {"mouse": 20, "keyboard": 5}
                        
                        console.log('Borrowed totals:', borrowedTotals);

                        function addItemRow(data = {}) {
                            const template = document.getElementById('item-row-template');
                            const clone = template.content.cloneNode(true);
                            const row = clone.querySelector('.item-row');

                            // Fill text/number inputs
                            const fields = { name: '', brand_type: '', quantity: 1, remarks: '' };
                            Object.keys(fields).forEach(function(field) {
                                const input = row.querySelector('[data-field="' + field + '"]:not([type="hidden"])');
                                if (input && data[field] !== undefined) input.value = data[field];
                            });

                            // Fill condition hidden inputs + sync checkboxes
                            ['condition_good', 'condition_adequate', 'condition_complete'].forEach(function(field) {
                                const hidden = row.querySelector('[data-field="' + field + '"]');
                                const checkbox = row.querySelector('[data-mirror="' + field + '"]');
                                if (data[field] !== undefined) {
                                    const val = !!data[field];
                                    if (hidden) hidden.value = val ? '1' : '0';
                                    if (checkbox) checkbox.checked = val;
                                }
                            });

                            document.getElementById('items-container').appendChild(clone);
                        }

                        function removeItemRow(btn) {
                            const rows = document.querySelectorAll('#items-container .item-row');
                            if (rows.length <= 1) {
                                showCustomAlert('Peringatan', 'Validasi gagal', '<p>Minimal harus ada <strong>1 baris barang</strong>.</p>');
                                return;
                            }
                            btn.closest('.item-row').remove();
                        }

                        // Initialize on page load
                        document.addEventListener('DOMContentLoaded', function () {
                            // 1. Populate items first
                            if (existingItems && existingItems.length > 0) {
                                existingItems.forEach(function(item) { addItemRow(item); });
                            } else {
                                addItemRow();
                            }

                            // 2. Setup form submit handler
                            const form = document.getElementById('first-party-form');
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    console.log('Form submitting, validating quantities...');
                                    const rows = document.querySelectorAll('#items-container .item-row');
                                    console.log('Found ' + rows.length + ' item rows');
                                    
                                    // Validate: sum quantities per item name must not exceed borrowed total
                                    const itemSums = {};
                                    let hasError = false;
                                    let errorMsg = '';
                                    
                                    rows.forEach(function(row) {
                                        const nameInput = row.querySelector('[data-field="name"]:not([type="hidden"])');
                                        const qtyInput = row.querySelector('[data-field="quantity"]');
                                        
                                        if (nameInput && qtyInput) {
                                            const name = nameInput.value.trim().toLowerCase();
                                            const qty = parseInt(qtyInput.value) || 0;
                                            
                                            if (!itemSums[name]) itemSums[name] = 0;
                                            itemSums[name] += qty;
                                        }
                                    });
                                    
                                    console.log('Item sums:', itemSums);
                                    
                                    // Check against borrowed totals
                                    for (const itemName in itemSums) {
                                        const inputTotal = itemSums[itemName];
                                        const borrowedTotal = borrowedTotals[itemName];
                                        
                                        if (borrowedTotal !== undefined && inputTotal > borrowedTotal) {
                                            hasError = true;
                                            errorMsg += '• ' + itemName.toUpperCase() + ': Anda input ' + inputTotal + ' unit, tapi yang dipinjam hanya ' + borrowedTotal + ' unit.\n';
                                        }
                                    }
                                    
                                    if (hasError) {
                                        e.preventDefault();
                                        let htmlMsg = '<ul class="list-disc list-inside space-y-1 mb-3">';
                                        errorMsg.split('\n').filter(line => line.trim()).forEach(function(line) {
                                            htmlMsg += '<li>' + line.replace(/^• /, '') + '</li>';
                                        });
                                        htmlMsg += '</ul><p class="font-medium text-gray-600">Silakan perbaiki jumlah barang.</p>';
                                        showCustomAlert('Jumlah Melebihi yang Dipinjam!', 'Periksa kembali input Anda', htmlMsg);
                                        return false;
                                    }
                                    
                                    // Re-index inputs
                                    rows.forEach(function(row, i) {
                                        // Text / number inputs
                                        row.querySelectorAll('[data-field]:not([type="hidden"])').forEach(function(input) {
                                            input.name = 'items_override[' + i + '][' + input.dataset.field + ']';
                                            console.log('Set name:', input.name, '=', input.value);
                                        });
                                        // Hidden inputs (condition values)
                                        row.querySelectorAll('input[type="hidden"][data-field]').forEach(function(input) {
                                            input.name = 'items_override[' + i + '][' + input.dataset.field + ']';
                                            console.log('Set name:', input.name, '=', input.value);
                                        });
                                    });
                                    
                                    console.log('Validation passed, form submitting');
                                });
                            } else {
                                console.error('Form #first-party-form not found!');
                            }
                        });
                    </script>
                </div>
            </div>

        <!-- Detail Peminjaman -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-4 py-3">
                <h3 class="text-base font-bold text-gray-800 mb-2.5 pb-2 border-b">Detail Peminjaman</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 mb-3 text-sm">
                    <div class="flex items-baseline gap-2">
                        <span class="font-semibold text-gray-600 text-xs">No. Surat:</span>
                        <span class="text-gray-900">{{ $borrowing->document_number ?? 'Belum dibuat' }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="font-semibold text-gray-600 text-xs">Tgl Pinjam:</span>
                        <span class="text-gray-900">{{ $borrowing->borrow_date->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="font-semibold text-gray-600 text-xs">Tgl Kembali:</span>
                        <span class="text-gray-900">{{ $borrowing->return_date->format('d M Y') }}</span>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4 flex items-baseline gap-2">
                        <span class="font-semibold text-gray-600 text-xs flex-shrink-0">Tujuan:</span>
                        <span class="text-gray-900 text-sm">{{ $borrowing->purpose }}</span>
                    </div>
                </div>

                <h4 class="text-sm font-semibold text-gray-800 mb-2 mt-3">Barang yang Dipinjam</h4>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700">No</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700">Nama Barang</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700">Merk/Tipe</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700">Jumlah</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @php
                            if (!empty($borrowing->items_override)) {
                                $groupedItems = collect($borrowing->items_override)->map(function($item) {
                                    return [
                                        'name' => $item['name'] ?? '-',
                                        'brand_type' => (!empty($item['brand_type'])) ? $item['brand_type'] : '-',
                                        'quantity' => $item['quantity'] ?? 0,
                                        'condition_good' => $item['condition_good'] ?? false,
                                        'condition_adequate' => $item['condition_adequate'] ?? false,
                                        'condition_complete' => $item['condition_complete'] ?? false,
                                    ];
                                });
                            } else {
                                $groupedItems = $borrowing->borrowedItems->groupBy(function($item) {
                                    $category = $item->item->category ?? $item->item->name;
                                    $brand = $item->brand_type ?? $item->item->brand ?? '-';
                                    return $category . '|' . $brand;
                                })->map(function($items) {
                                    $first = $items->first();
                                    return [
                                        'name' => $first->item->category ?? $first->item->name,
                                        'brand_type' => $first->brand_type ?? $first->item->brand ?? '-',
                                        'quantity' => $items->sum('quantity'),
                                        'condition_good' => $first->condition_good,
                                        'condition_adequate' => $first->condition_adequate,
                                        'condition_complete' => $first->condition_complete,
                                    ];
                                })->values();
                            }
                            @endphp
                            @foreach($groupedItems as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2 text-gray-900">
                                        {{ $item['name'] }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-900">{{ $item['brand_type'] }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                            {{ $item['quantity'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex gap-1 flex-wrap">
                                            @if($item['condition_good'])
                                                <span class="px-1.5 py-0.5 bg-green-100 text-green-800 text-xs rounded">Baik</span>
                                            @endif
                                            @if($item['condition_complete'])
                                                <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 text-xs rounded">Lengkap</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Handover & Return Details -->
                @if($borrowing->handed_out_at || $borrowing->received_back_at)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Riwayat Transaksi Barang</h4>
                        <div class="space-y-3 text-sm">
                            @if($borrowing->handed_out_at)
                                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                    <div class="flex flex-col sm:flex-row sm:justify-between gap-1 mb-1">
                                        <span class="font-semibold text-blue-800 flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Penyerahan Barang
                                        </span>
                                        <span class="text-xs text-blue-600">
                                            {{ $borrowing->handed_out_at->format('d M Y H:i') }} oleh {{ $borrowing->handedOutBy->name ?? 'Admin' }}
                                        </span>
                                    </div>
                                    @if($borrowing->borrow_condition_notes)
                                        <div class="text-gray-700 mt-1 pl-5.5 text-xs">
                                            <span class="font-medium">Catatan:</span> {{ $borrowing->borrow_condition_notes }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($borrowing->received_back_at)
                                <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                    <div class="flex flex-col sm:flex-row sm:justify-between gap-1 mb-1">
                                        <span class="font-semibold text-green-800 flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                            Pengembalian Barang
                                        </span>
                                        <span class="text-xs text-green-600">
                                            {{ $borrowing->received_back_at->format('d M Y H:i') }} oleh {{ $borrowing->receivedBackBy->name ?? 'Admin' }}
                                        </span>
                                    </div>
                                    @if($borrowing->return_condition_notes)
                                        <div class="text-gray-700 mt-1 pl-5.5 text-xs">
                                            <span class="font-medium">Catatan:</span> {{ $borrowing->return_condition_notes }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Action Buttons Footer -->
            @if(in_array($borrowing->status, ['approved', 'borrowed']))
                <div class="bg-gray-50 px-4 py-3 sm:px-6 rounded-b-lg border-t border-gray-200 flex flex-row-reverse gap-2">
                    @if($borrowing->status === 'approved')
                        <button onclick="openHandoutModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Serahkan Barang
                            </span>
                        </button>
                    @elseif($borrowing->status === 'borrowed')
                        <button onclick="openReceiveModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Terima Kembali
                            </span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Penyerahan Barang -->
<div id="handoutModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all max-w-2xl w-full z-20">
            <form action="{{ route('admin.asset-borrowings.handout', $borrowing->id) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Penyerahan Barang</h3>
                    <p class="text-sm text-gray-500 mb-4">Pilih unit spesifik untuk setiap barang yang akan diserahkan.</p>
                    
                    <!-- Loading State -->
                    <div id="loadingUnits" class="text-center py-4">
                        <svg class="animate-spin h-8 w-8 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-gray-600 mt-2">Memuat data unit...</p>
                    </div>

                    <!-- Unit Selection -->
                    <div id="unitSelectionContainer" class="hidden space-y-4 mb-4">
                        <!-- Will be populated by JavaScript -->
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Penyerahan (Opsional)</label>
                        <textarea name="borrow_condition_notes" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: Kondisi barang baik, lengkap dengan kabel..."></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="submitHandoutBtn" class="w-full sm:w-auto sm:ml-3 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Serahkan Barang
                    </button>
                    <button type="button" onclick="closeHandoutModal()" class="mt-3 w-full sm:mt-0 sm:w-auto bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg border border-gray-300">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pengembalian Barang -->
<div id="receiveModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all max-w-2xl w-full z-20 max-h-[90vh] flex flex-col">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-5 flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="text-lg font-bold">Terima Kembali Barang</h3>
                    <p class="text-sm text-green-100">Catat kondisi setiap unit yang dikembalikan</p>
                </div>
                <button type="button" onclick="closeReceiveModal()" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.asset-borrowings.receive', $borrowing->id) }}" method="POST" id="receiveFormShow" class="flex-1 overflow-y-auto">
                @csrf
                <div class="p-6">
                    <!-- Loading -->
                    <div id="loadingReturnUnitsShow" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 mx-auto text-green-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-gray-600 mt-3">Memuat data unit...</p>
                    </div>

                    <!-- Unit Cards -->
                    <div id="returnUnitContainerShow" class="hidden space-y-4 mb-6"></div>
                    
                    <div id="returnNotesSectionShow" class="hidden mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Umum (Opsional)</label>
                        <textarea name="return_condition_notes" rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                            placeholder="Catatan umum tentang pengembalian..."></textarea>
                    </div>
                    
                    <div id="returnSubmitSectionShow" class="hidden flex justify-end space-x-3">
                        <button type="button" onclick="closeReceiveModal()" class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg border border-gray-300 text-sm font-medium">Batal</button>
                        <button type="submit" id="submitReceiveBtnShow" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold shadow-sm disabled:opacity-50">
                            ✓ Konfirmasi Terima
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Alert Modal -->
<div id="customAlertModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity" onclick="closeCustomAlert()"></div>
        <div class="bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all max-w-md w-full z-20">
            <div id="customAlertHeader" class="bg-gradient-to-r from-red-600 to-rose-600 text-white p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="customAlertTitle" class="text-lg font-bold">Peringatan</h3>
                        <p id="customAlertSubtitle" class="text-sm text-red-100">Terdapat kesalahan pada input</p>
                    </div>
                </div>
                <button type="button" onclick="closeCustomAlert()" class="text-white/80 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="customAlertBody" class="text-gray-700 text-sm leading-relaxed mb-5"></div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeCustomAlert()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition-all text-sm">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penolakan Peminjaman -->
<div id="rejectModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity"></div>
        <div class="bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all max-w-md w-full z-20">
            <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Tolak Peminjaman</h3>
                        <p class="text-sm text-red-100">Berikan alasan penolakan</p>
                    </div>
                </div>
                <button type="button" onclick="closeRejectModal()" class="text-white/80 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.asset-borrowings.reject', $borrowing->id) }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="mb-5">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="rejection_reason" 
                            rows="4" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all" 
                            required 
                            placeholder="Contoh: Jadwal bertabrakan dengan kegiatan lain, dokumen tidak lengkap, dll..."
                        ></textarea>
                        <p class="mt-2 text-xs text-gray-500">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Peminjam akan melihat alasan ini
                        </p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeRejectModal()" 
                            class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition-all text-sm"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-sm hover:shadow-md transition-all text-sm flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak Peminjaman
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCustomAlert(title, subtitle, bodyHtml) {
    document.getElementById('customAlertTitle').textContent = title;
    document.getElementById('customAlertSubtitle').textContent = subtitle;
    document.getElementById('customAlertBody').innerHTML = bodyHtml;
    document.getElementById('customAlertModal').classList.remove('hidden');
}

function closeCustomAlert() {
    document.getElementById('customAlertModal').classList.add('hidden');
}

function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

async function openHandoutModal() {
    const modal = document.getElementById('handoutModal');
    const loadingDiv = document.getElementById('loadingUnits');
    const containerDiv = document.getElementById('unitSelectionContainer');
    const submitBtn = document.getElementById('submitHandoutBtn');
    
    modal.classList.remove('hidden');
    loadingDiv.classList.remove('hidden');
    containerDiv.classList.add('hidden');
    submitBtn.disabled = true;

    try {
        const response = await fetch('{{ route('admin.asset-borrowings.available-units', $borrowing->id) }}');
        const data = await response.json();
        
        if (data.length === 0) {
            containerDiv.innerHTML = '<p class="text-sm text-gray-600">Tidak ada barang yang perlu diserahkan.</p>';
        } else {
            let html = '';
            data.forEach((item) => {
                if (item.tracking_mode === 'AGGREGATE') {
                    // Show aggregate item with detailed unit list
                    const totalAvailable = item.inventory_units ? item.inventory_units.length : 0;
                    
                    html += `
                        <div class="border border-gray-200 rounded-lg p-4 bg-gradient-to-br from-orange-50 to-amber-50">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-lg">${item.item_name}</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        📦 Total dipinjam: <span class="font-semibold text-indigo-600">${item.total_quantity} unit</span>
                                        · Tersedia: <span class="font-semibold ${totalAvailable >= item.total_quantity ? 'text-green-600' : 'text-red-600'}">${totalAvailable} unit</span>
                                    </p>
                                </div>
                                <span class="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded">Aggregate</span>
                            </div>
                            
                            <div class="mt-3 bg-white border border-orange-200 rounded-lg p-3 max-h-60 overflow-y-auto">
                                <p class="text-xs text-gray-600 font-bold mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Unit yang Tersedia untuk Dipinjamkan:
                                </p>
                                ${item.inventory_units && item.inventory_units.length > 0 ? `
                                    <div class="grid grid-cols-1 gap-2">
                                        ${item.inventory_units.map((unit, idx) => `
                                            <label class="flex items-center gap-2 p-2 bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-orange-200 rounded cursor-pointer hover:border-orange-400 hover:shadow transition-all">
                                                <input type="checkbox" 
                                                    name="aggregate_units[${item.item_name}][]" 
                                                    value="${unit.code}|${unit.batch_number}|${unit.lab_name}"
                                                    data-item-name="${item.item_name}"
                                                    data-required="${item.total_quantity}"
                                                    class="w-5 h-5 text-orange-600 border-2 border-orange-300 rounded focus:ring-2 focus:ring-orange-500 aggregate-checkbox-show"
                                                    onchange="validateAggregateSelectionShow('${item.item_name}', ${item.total_quantity})">
                                                <span class="flex-shrink-0 w-6 h-6 bg-orange-500 text-white rounded-full text-xs font-bold flex items-center justify-center">${idx + 1}</span>
                                                <div class="flex-1">
                                                    <p class="text-sm font-mono font-semibold text-gray-800">${unit.code}</p>
                                                    <p class="text-xs text-gray-500">📍 ${unit.lab_name}</p>
                                                </div>
                                            </label>
                                        `).join('')}
                                    </div>
                                    <p class="text-xs text-orange-600 font-semibold mt-2">
                                        <span id="selection-status-show-${item.item_name.replace(/\s+/g, '-')}">⚠️ Pilih ${item.total_quantity} unit dari ${item.inventory_units.length} unit tersedia</span>
                                    </p>
                                ` : `
                                    <p class="text-sm text-gray-500 italic text-center py-3">Tidak ada unit tersedia</p>
                                `}
                            </div>
                            
                            <p class="text-xs text-blue-600 mt-3">ℹ️ Tersedia ${totalAvailable} unit. Admin dapat memutuskan ${item.total_quantity} unit mana yang akan dipinjamkan</p>
                        </div>
                    `;
                } else {
                    // Show structured/seat items with unit selection
                    html += `
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-lg">${item.item_name}</h4>
                                    <p class="text-xs text-gray-500 mt-1">Total dipinjam: <span class="font-semibold text-indigo-600">${item.total_quantity} unit</span> · Tersedia: <span class="font-semibold ${item.units.length >= item.total_quantity ? 'text-green-600' : 'text-red-600'}">${item.units.length} unit</span></p>
                                </div>
                            </div>
                            ${generateUnitSelects(item)}
                        </div>
                    `;
                }
            });
            containerDiv.innerHTML = html;
            initUnitSelectListenersShow();
            
            // Initialize validation for aggregate items
            const aggregateItems = data.filter(item => item.tracking_mode === 'AGGREGATE');
            aggregateItems.forEach(item => {
                validateAggregateSelectionShow(item.item_name, item.total_quantity);
            });
        }
        
        loadingDiv.classList.add('hidden');
        containerDiv.classList.remove('hidden');
        // Keep submit disabled initially if there are items requiring selection
        const hasItems = data.length > 0;
        submitBtn.disabled = hasItems;
        
    } catch (error) {
        console.error('Error:', error);
        containerDiv.innerHTML = '<p class="text-sm text-red-600">Terjadi kesalahan saat memuat data unit.</p>';
        loadingDiv.classList.add('hidden');
        containerDiv.classList.remove('hidden');
    }
}

function generateUnitSelects(item) {
    let selectsHtml = '';
    const unitOptions = item.units.map(unit => `<option value="${unit.id}">${unit.display}</option>`).join('');
    
    item.borrowing_items.forEach((bi, idx) => {
        for (let q = 0; q < bi.quantity; q++) {
            const label = item.total_quantity === 1 ? 'Pilih Unit' : `Unit ${idx + q + 1}`;
            selectsHtml += `
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-gray-500 w-14 shrink-0">${label}</span>
                    <select name="unit_assignments[${bi.borrowing_item_id}][]" 
                        required
                        data-unit-group="${item.item_name}"
                        class="unit-select-show flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm font-mono">
                        <option value="">-- Pilih Unit --</option>
                        ${unitOptions}
                    </select>
                </div>
            `;
        }
    });
    
    return `
        <div class="space-y-3">
            ${selectsHtml}
        </div>
        <p class="text-xs text-gray-400 mt-3">Format: Kode UPK | Kode Universitas</p>
    `;
}

function initUnitSelectListenersShow() {
    document.querySelectorAll('.unit-select-show').forEach(select => {
        select.addEventListener('change', function() {
            const group = this.dataset.unitGroup;
            const selects = document.querySelectorAll(`.unit-select-show[data-unit-group="${group}"]`);
            const selectedValues = [];
            selects.forEach(s => { if (s.value) selectedValues.push(s.value); });
            
            selects.forEach(s => {
                Array.from(s.options).forEach(opt => {
                    if (opt.value && opt.value !== s.value) {
                        opt.disabled = selectedValues.includes(opt.value);
                    }
                });
            });
            
            // Trigger aggregate validation to update submit button
            const firstAggregateCheckbox = document.querySelector('.aggregate-checkbox-show');
            if (firstAggregateCheckbox) {
                const itemName = firstAggregateCheckbox.dataset.itemName;
                const required = parseInt(firstAggregateCheckbox.dataset.required);
                validateAggregateSelectionShow(itemName, required);
            } else {
                // No aggregate items, just check if all selects are filled
                const allSelects = document.querySelectorAll('.unit-select-show');
                const allFilled = Array.from(allSelects).every(s => s.value);
                const submitBtn = document.getElementById('submitHandoutBtn');
                if (submitBtn) {
                    submitBtn.disabled = !allFilled;
                }
            }
        });
    });
}

function validateAggregateSelectionShow(itemName, requiredCount) {
    const checkboxes = document.querySelectorAll(`input[data-item-name="${itemName}"].aggregate-checkbox-show`);
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const statusElement = document.getElementById(`selection-status-show-${itemName.replace(/\s+/g, '-')}`);
    const submitBtn = document.getElementById('submitHandoutBtn');
    
    if (statusElement) {
        if (checkedCount === requiredCount) {
            statusElement.innerHTML = `✅ ${checkedCount} unit dipilih (sesuai kebutuhan)`;
            statusElement.className = 'text-green-600 font-bold';
        } else if (checkedCount < requiredCount) {
            statusElement.innerHTML = `⚠️ Pilih ${requiredCount - checkedCount} unit lagi (${checkedCount}/${requiredCount})`;
            statusElement.className = 'text-orange-600 font-semibold';
        } else {
            statusElement.innerHTML = `❌ Terlalu banyak! Pilih hanya ${requiredCount} unit (dipilih: ${checkedCount})`;
            statusElement.className = 'text-red-600 font-bold';
        }
    }
    
    // Validate all aggregate items
    const allItemNames = new Set();
    document.querySelectorAll('.aggregate-checkbox-show').forEach(cb => {
        allItemNames.add(cb.dataset.itemName);
    });
    
    let allValid = true;
    allItemNames.forEach(name => {
        const cbs = document.querySelectorAll(`input[data-item-name="${name}"].aggregate-checkbox-show`);
        const required = parseInt(cbs[0]?.dataset.required || 0);
        const checked = Array.from(cbs).filter(c => c.checked).length;
        if (checked !== required) {
            allValid = false;
        }
    });
    
    // Also check if there are structured/seat items that need validation
    const structuredSelects = document.querySelectorAll('.unit-select-show');
    if (structuredSelects.length > 0) {
        const allFilled = Array.from(structuredSelects).every(s => s.value);
        allValid = allValid && allFilled;
    }
    
    if (submitBtn) {
        submitBtn.disabled = !allValid;
    }
}

function closeHandoutModal() {
    document.getElementById('handoutModal').classList.add('hidden');
    document.getElementById('unitSelectionContainer').innerHTML = '';
}

async function openReceiveModal() {
    const modal = document.getElementById('receiveModal');
    const loadingDiv = document.getElementById('loadingReturnUnitsShow');
    const containerDiv = document.getElementById('returnUnitContainerShow');  
    const notesSection = document.getElementById('returnNotesSectionShow');
    const submitSection = document.getElementById('returnSubmitSectionShow');
    const submitBtn = document.getElementById('submitReceiveBtnShow');
    
    modal.classList.remove('hidden');
    loadingDiv.classList.remove('hidden');
    containerDiv.classList.add('hidden');
    notesSection.classList.add('hidden');
    submitSection.classList.add('hidden');
    submitBtn.disabled = true;

    try {
        const response = await fetch('{{ route('admin.asset-borrowings.borrowed-units', $borrowing->id) }}');
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        
        if (data.length === 0) {
            containerDiv.innerHTML = '<p class="text-sm text-gray-600">Tidak ada unit spesifik yang perlu dicatat kondisinya.</p>';
        } else {
            let html = '';
            data.forEach((group) => {
                html += `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 text-lg mb-3">${group.item_name}</h4>
                        <div class="space-y-3">
                            ${group.units.map((unit, idx) => generateReturnUnitCardShow(unit, idx, group.units.length)).join('')}
                        </div>
                    </div>
                `;
            });
            containerDiv.innerHTML = html;
        }
        
        loadingDiv.classList.add('hidden');
        containerDiv.classList.remove('hidden');
        notesSection.classList.remove('hidden');
        submitSection.classList.remove('hidden');
        submitBtn.disabled = false;
    } catch (error) {
        console.error('Error:', error);
        containerDiv.innerHTML = `<p class="text-sm text-red-600">Gagal memuat data: ${error.message}</p>`;
        loadingDiv.classList.add('hidden');
        containerDiv.classList.remove('hidden');
        notesSection.classList.remove('hidden');
        submitSection.classList.remove('hidden');
    }
}

function generateReturnUnitCardShow(unit, idx, total) {
    const unitLabel = unit.display || `Unit #${idx + 1}`;
    const condName = `item_conditions[${unit.borrowing_item_id}][condition]`;
    const notesName = `item_conditions[${unit.borrowing_item_id}][notes]`;
    const notesId = `return_notes_show_${unit.borrowing_item_id}`;
    
    return `
        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
            <div class="flex items-center gap-2 mb-3">
                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">${total > 1 ? 'Unit ' + (idx + 1) : 'Unit'}</span>
                <span class="text-sm font-mono font-semibold text-gray-800">${unitLabel}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-2">
                <label class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg border-2 cursor-pointer transition-all hover:bg-green-50 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                    <input type="radio" name="${condName}" value="BAIK" checked class="text-green-600 focus:ring-green-500" onchange="toggleReturnNotesShow('${notesId}', this.value)">
                    <span class="text-xs font-semibold">✅ Baik</span>
                </label>
                <label class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg border-2 cursor-pointer transition-all hover:bg-yellow-50 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                    <input type="radio" name="${condName}" value="RUSAK_RINGAN" class="text-yellow-600 focus:ring-yellow-500" onchange="toggleReturnNotesShow('${notesId}', this.value)">
                    <span class="text-xs font-semibold">⚠️ Rusak Ringan</span>
                </label>
                <label class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg border-2 cursor-pointer transition-all hover:bg-red-50 has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                    <input type="radio" name="${condName}" value="RUSAK_BERAT" class="text-red-600 focus:ring-red-500" onchange="toggleReturnNotesShow('${notesId}', this.value)">
                    <span class="text-xs font-semibold">🔴 Rusak Berat</span>
                </label>
                <label class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg border-2 cursor-pointer transition-all hover:bg-gray-100 has-[:checked]:border-gray-600 has-[:checked]:bg-gray-100">
                    <input type="radio" name="${condName}" value="HILANG" class="text-gray-600 focus:ring-gray-500" onchange="toggleReturnNotesShow('${notesId}', this.value)">
                    <span class="text-xs font-semibold">❌ Hilang</span>
                </label>
            </div>
            <div id="${notesId}" class="hidden mt-2">
                <textarea name="${notesName}" rows="2" 
                    class="w-full px-3 py-2 border-2 border-red-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Jelaskan detail kerusakan/kehilangan..."></textarea>
            </div>
        </div>
    `;
}

function toggleReturnNotesShow(notesId, value) {
    const el = document.getElementById(notesId);
    if (value !== 'BAIK') {
        el.classList.remove('hidden');
        el.querySelector('textarea').required = true;
    } else {
        el.classList.add('hidden');
        el.querySelector('textarea').required = false;
        el.querySelector('textarea').value = '';
    }
}

function closeReceiveModal() {
    document.getElementById('receiveModal').classList.add('hidden');
    document.getElementById('returnUnitContainerShow').innerHTML = '';
}
</script>
@endsection
