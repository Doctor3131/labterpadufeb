@extends('layouts.admin')

@section('title', 'Pindah Barang - Lab Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.inventory.index') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pindah Barang Antar Ruangan</h1>
        <p class="text-gray-600">Pindahkan barang satuan maupun agregat ke Gudang atau ruangan lain.</p>
    </div>

    <!-- Messages -->
    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg shadow-sm">
            <div class="font-medium mb-1">Terjadi kesalahan:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <form id="transfer-form" action="{{ route('admin.inventory.transfer.store') }}" method="POST" class="p-6 md:p-8 space-y-8">
            @csrf

            <!-- 1. Source & Target Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Source Lab -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Dari (Ruangan Asal) <span class="text-red-500">*</span>
                    </label>
                    @php
                        $isFromEksternal = isset($sourceLabId) && $labs->firstWhere('id', $sourceLabId)?->name === 'Eksternal';
                        $gudangLab = $labs->firstWhere('name', 'Gudang');
                    @endphp
                    @if($isFromEksternal)
                        <input type="hidden" name="source_lab_id" value="{{ $sourceLabId }}">
                        <div class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-medium">
                            Eksternal
                        </div>
                    @else
                        <select id="source_lab_select" name="source_lab_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Ruangan Asal --</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}" {{ old('source_lab_id', $sourceLabId ?? '') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Target Lab -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ke (Ruangan Tujuan) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        @if($isFromEksternal && $gudangLab)
                            <input type="hidden" name="target_lab_id" value="{{ $gudangLab->id }}">
                            <div class="w-full px-4 py-2.5 bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-lg font-medium">
                                Gudang
                            </div>
                        @else
                            <select id="target_lab_select" name="target_lab_id" required class="w-full px-4 py-2.5 bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Ruangan Tujuan --</option>
                                @foreach($labs as $lab)
                                    <option value="{{ $lab->id }}" {{ old('target_lab_id', $isFromEksternal && $gudangLab ? $gudangLab->id : '') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- 2. Item Selection -->
            <div id="item-section" style="display:none">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold text-gray-700">
                        Pilih Barang yang akan Dipindah <span class="text-red-500">*</span>
                    </label>
                    <span id="items-loading" style="display:none" class="text-xs text-blue-600 flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat barang...
                    </span>
                </div>

                <select id="item_select" name="item_id" required disabled class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-2">
                    <option value="">-- Pilih Barang --</option>
                </select>
                <p id="no-items-msg" style="display:none" class="text-xs text-red-500 mt-1">
                    Tidak ada barang di ruangan asal ini.
                </p>

                <!-- Hidden input for tracking_mode -->
                <input type="hidden" id="tracking_mode_input" name="tracking_mode" value="{{ old('tracking_mode', '') }}">
            </div>

            <!-- 3. Dynamic Unit/Balance Selection Area -->
            <div id="item-details-section" style="display:none" class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                <h3 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Pilih Kuantitas / Unit
                    <span id="details-loading" style="display:none" class="ml-4 text-xs text-blue-600 flex items-center">
                        <svg class="animate-spin h-3 w-3 mr-1 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </h3>

                <!-- Unit Checkboxes (Tag/Seat) -->
                <div id="units-area" style="display:none">
                    <p class="text-sm text-gray-600 mb-3">Centang unit yang ingin dipindahkan:</p>
                    <div id="units-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"></div>
                </div>

                <!-- Balance Inputs (Aggregate) -->
                <div id="balances-area" style="display:none">
                    <p class="text-sm text-gray-600 mb-3">Masukkan jumlah yang ingin dipindahkan untuk tiap batch/kondisi:</p>
                    <div id="balances-container" class="space-y-3"></div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Catatan <span class="text-gray-500 font-normal">(Opsional)</span>
                </label>
                <textarea name="notes" rows="2" placeholder="Contoh: Dipindahkan karena kebutuhan perkuliahan..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.inventory.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </a>
                <button type="submit" id="submit-btn" disabled class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                    Pindahkan Barang
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        const sourceSelect = document.getElementById('source_lab_select');
        const targetSelect = document.getElementById('target_lab_select');
        const itemSection = document.getElementById('item-section');
        const itemsLoading = document.getElementById('items-loading');
        const itemSelect = document.getElementById('item_select');
        const noItemsMsg = document.getElementById('no-items-msg');
        const trackingModeInput = document.getElementById('tracking_mode_input');
        const detailsSection = document.getElementById('item-details-section');
        const detailsLoading = document.getElementById('details-loading');
        const unitsArea = document.getElementById('units-area');
        const unitsContainer = document.getElementById('units-container');
        const balancesArea = document.getElementById('balances-area');
        const balancesContainer = document.getElementById('balances-container');
        const submitBtn = document.getElementById('submit-btn');

        // Fixed source lab id when Eksternal (no select rendered)
        const fixedSourceLabId = '{{ $sourceLabId ?? '' }}';

        // Pre-selected old values from server
        const oldItemId = '{{ old('item_id', '') }}';

        function getSourceId() {
            return sourceSelect ? sourceSelect.value : fixedSourceLabId;
        }

        function show(el) { el.style.display = ''; }
        function hide(el) { el.style.display = 'none'; }

        // Filter target options to exclude source
        function filterTargetOptions() {
            if (!targetSelect) return;
            const sourceId = getSourceId();
            Array.from(targetSelect.options).forEach(opt => {
                if (opt.value === '' || opt.value !== sourceId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                    if (targetSelect.value === sourceId) {
                        targetSelect.value = '';
                    }
                }
            });
        }

        async function loadItems() {
            const sourceId = getSourceId();
            if (!sourceId) {
                hide(itemSection);
                resetItemSelect();
                resetDetails();
                return;
            }

            show(itemSection);
            show(itemsLoading);
            hide(noItemsMsg);
            itemSelect.disabled = true;
            itemSelect.innerHTML = '<option value="">-- Pilih Barang --</option>';
            resetDetails();

            try {
                const resp = await fetch('/admin/api/inventory/' + sourceId + '/items');
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const items = await resp.json();

                if (items.length === 0) {
                    show(noItemsMsg);
                } else {
                    items.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name + ' (' + item.total + ' tersedia)';
                        opt.dataset.trackingMode = item.tracking_mode;
                        itemSelect.appendChild(opt);
                    });
                    itemSelect.disabled = false;

                    // Restore old selection if available
                    if (oldItemId && itemSelect.querySelector('option[value="' + oldItemId + '"]')) {
                        itemSelect.value = oldItemId;
                        loadItemDetails();
                    }
                }
            } catch (e) {
                console.error('Gagal memuat barang:', e);
                hide(noItemsMsg);
            }
            hide(itemsLoading);
        }

        async function loadItemDetails() {
            const sourceId = getSourceId();
            const itemId = itemSelect.value;

            if (!sourceId || !itemId) {
                resetDetails();
                return;
            }

            // Set tracking_mode from selected option's data attribute
            const selectedOpt = itemSelect.querySelector('option[value="' + itemId + '"]');
            trackingModeInput.value = selectedOpt ? selectedOpt.dataset.trackingMode : '';

            show(detailsSection);
            show(detailsLoading);
            hide(unitsArea);
            hide(balancesArea);
            unitsContainer.innerHTML = '';
            balancesContainer.innerHTML = '';
            submitBtn.disabled = true;

            try {
                const resp = await fetch('/admin/api/inventory/' + sourceId + '/items/' + itemId);
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const data = await resp.json();

                if (data.type === 'units') {
                    renderUnits(data.data);
                    show(unitsArea);
                } else {
                    renderBalances(data.data);
                    show(balancesArea);
                }
                submitBtn.disabled = false;
            } catch (e) {
                console.error('Gagal memuat detail barang:', e);
                hide(detailsSection);
            }
            hide(detailsLoading);
        }

        function renderUnits(units) {
            unitsContainer.innerHTML = '';
            units.forEach(unit => {
                const univCode = unit.university_asset_code
                    ? '<div class="text-xs text-gray-500 mt-0.5">Univ: ' + escHtml(unit.university_asset_code) + '</div>'
                    : '';
                unitsContainer.insertAdjacentHTML('beforeend',
                    '<label class="flex items-start p-3 bg-white border rounded-lg cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-colors">' +
                        '<input type="checkbox" name="unit_ids[]" value="' + unit.id + '" class="mt-1 mr-3 w-4 h-4 text-indigo-600 rounded">' +
                        '<div class="flex-1">' +
                            '<div class="font-mono text-sm font-bold text-gray-800">' + escHtml(unit.asset_tag || '-') + '</div>' +
                            univCode +
                            '<div class="mt-1.5 flex items-center justify-between">' +
                                '<span class="text-[10px] text-gray-500">Batch: ' + escHtml(unit.batch_formatted) + '</span>' +
                                '<span class="' + escHtml(unit.condition_color) + ' text-[10px] font-bold px-2.5 py-0.5 rounded-full">' + escHtml(unit.condition_label) + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</label>'
                );
            });
        }

        function renderBalances(balances) {
            balancesContainer.innerHTML = '';
            balances.forEach((balance, index) => {
                balancesContainer.insertAdjacentHTML('beforeend',
                    '<div class="flex items-center justify-between p-4 bg-white border rounded-lg">' +
                        '<div>' +
                            '<div class="text-sm font-semibold text-gray-800">Batch: ' + escHtml(balance.batch_formatted) + '</div>' +
                            '<span class="' + escHtml(balance.condition_color) + ' text-xs font-bold px-2 py-0.5 rounded-full mt-1 inline-block">' + escHtml(balance.condition_label) + '</span>' +
                            '<div class="text-xs text-gray-500 mt-1">Tersedia: ' + balance.max_quantity + ' unit</div>' +
                        '</div>' +
                        '<div class="flex items-center gap-3">' +
                            '<input type="hidden" name="transfers[' + index + '][batch_id]" value="' + balance.batch_id + '">' +
                            '<input type="hidden" name="transfers[' + index + '][condition]" value="' + escHtml(balance.condition_value) + '">' +
                            '<div class="flex items-center">' +
                                '<button type="button" onclick="this.nextElementSibling.stepDown()" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-l-md hover:bg-gray-200">' +
                                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>' +
                                '</button>' +
                                '<input type="number" id="qty_' + index + '" name="transfers[' + index + '][quantity]" min="0" max="' + balance.max_quantity + '" value="0" class="w-20 text-center py-2 border-y border-gray-300 focus:ring-0 focus:outline-none">' +
                                '<button type="button" onclick="this.previousElementSibling.stepUp()" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-r-md hover:bg-gray-200">' +
                                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>' +
                                '</button>' +
                            '</div>' +
                            '<button type="button" onclick="document.getElementById(\'qty_' + index + '\').value=' + balance.max_quantity + '" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium bg-indigo-50 px-2 py-1.5 rounded border border-indigo-100">Max</button>' +
                        '</div>' +
                    '</div>'
                );
            });
        }

        function resetItemSelect() {
            itemSelect.innerHTML = '<option value="">-- Pilih Barang --</option>';
            itemSelect.disabled = true;
            trackingModeInput.value = '';
        }

        function resetDetails() {
            hide(detailsSection);
            unitsContainer.innerHTML = '';
            balancesContainer.innerHTML = '';
            submitBtn.disabled = true;
        }

        function escHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // Event listeners
        if (sourceSelect) {
            sourceSelect.addEventListener('change', function () {
                filterTargetOptions();
                loadItems();
            });
        }
        itemSelect.addEventListener('change', loadItemDetails);

        // Init
        filterTargetOptions();
        if (sourceSelect && sourceSelect.value) {
            loadItems();
        } else if (!sourceSelect) {
            // isFromEksternal case — source is fixed, load immediately
            loadItems();
        }
    })();
    </script>
    @endpush
@endsection
