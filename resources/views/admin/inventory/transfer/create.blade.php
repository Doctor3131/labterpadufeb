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

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden" x-data="transferForm()">
        <form action="{{ route('admin.inventory.transfer.store') }}" method="POST" class="p-6 md:p-8 space-y-8">
            @csrf

            <!-- 1. Source & Target Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Source Lab -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Dari (Ruangan Asal) <span class="text-red-500">*</span>
                    </label>
                    <select name="source_lab_id" x-model="sourceLabId" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Ruangan Asal --</option>
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Lab -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Ke (Ruangan Tujuan) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="target_lab_id" x-model="targetLabId" required class="w-full px-4 py-2.5 bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih Ruangan Tujuan --</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}" x-show="sourceLabId != {{ $lab->id }}">{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <!-- 2. Item Selection -->
            <div x-show="sourceLabId" x-cloak>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold text-gray-700">
                        Pilih Barang yang akan Dipindah <span class="text-red-500">*</span>
                    </label>
                    <span x-show="isLoadingItems" class="text-xs text-blue-600 flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat barang...
                    </span>
                </div>
                
                <select name="item_id" x-model="selectedItemId" required :disabled="items.length === 0" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 mb-2">
                    <option value="">-- Pilih Barang --</option>
                    <template x-for="item in items" :key="item.id">
                        <option :value="item.id" x-text="`${item.name} (${item.total} tersedia)`"></option>
                    </template>
                </select>
                <p x-show="items.length === 0 && !isLoadingItems && sourceLabId" class="text-xs text-red-500 mt-1">
                    Tidak ada barang di ruangan asal ini.
                </p>

                <!-- Hidden inputs for backend logic -->
                <input type="hidden" name="tracking_mode" :value="selectedItem ? selectedItem.tracking_mode : ''">
            </div>

            <!-- 3. Dynamic Unit/Balance Selection Area -->
            <div x-show="itemDetails" x-cloak class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                <h3 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Pilih Kuantitas / Unit
                    <span x-show="isLoadingDetails" class="ml-4 text-xs text-blue-600 flex items-center">
                        <svg class="animate-spin h-3 w-3 mr-1 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </h3>

                <!-- Unit Checkboxes (Tag/Seat) -->
                <div x-show="itemDetails?.type === 'units'">
                    <p class="text-sm text-gray-600 mb-3">Centang unit yang ingin dipindahkan:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <template x-for="unit in itemDetails?.data" :key="unit.id">
                            <label class="flex items-start p-3 bg-white border rounded-lg cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-colors">
                                <input type="checkbox" name="unit_ids[]" :value="unit.id" class="mt-1 mr-3 w-4 h-4 text-indigo-600 rounded">
                                <div class="flex-1">
                                    <div class="font-mono text-sm font-bold text-gray-800" x-text="unit.asset_tag || '-'"></div>
                                    <div x-show="unit.university_asset_code" class="text-xs text-gray-500 mt-0.5" x-text="`Univ: ${unit.university_asset_code}`"></div>
                                    <div class="mt-1.5 flex items-center justify-between">
                                        <span class="text-[10px] text-gray-500" x-text="`Batch: ${unit.batch_formatted}`"></span>
                                        <span :class="`${unit.condition_color} text-[10px] font-bold px-2.5 py-0.5 rounded-full`" x-text="unit.condition_label"></span>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Balance Inputs (Aggregate) -->
                <div x-show="itemDetails?.type === 'balances'">
                    <p class="text-sm text-gray-600 mb-3">Masukkan jumlah yang ingin dipindahkan untuk tiap batch/kondisi:</p>
                    <div class="space-y-3">
                        <template x-for="(balance, index) in itemDetails?.data" :key="balance.id">
                            <div class="flex items-center justify-between p-4 bg-white border rounded-lg">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800" x-text="`Batch: ${balance.batch_formatted}`"></div>
                                    <span :class="`${balance.condition_color} text-xs font-bold px-2 py-0.5 rounded-full mt-1 inline-block`" x-text="balance.condition_label"></span>
                                    <div class="text-xs text-gray-500 mt-1" x-text="`Tersedia: ${balance.max_quantity} unit`"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="hidden" :name="`transfers[${index}][batch_id]`" :value="balance.batch_id">
                                    <input type="hidden" :name="`transfers[${index}][condition]`" :value="balance.condition_value">
                                    
                                    <div class="flex items-center">
                                        <button type="button" @click="$refs[`qty_${index}`].stepDown()" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-l-md hover:bg-gray-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                        </button>
                                        <input type="number" :name="`transfers[${index}][quantity]`" :x-ref="`qty_${index}`" min="0" :max="balance.max_quantity" value="0" class="w-20 text-center py-2 border-y border-gray-300 focus:ring-0 focus:outline-none">
                                        <button type="button" @click="$refs[`qty_${index}`].stepUp()" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-r-md hover:bg-gray-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>
                                    <button type="button" @click="$refs[`qty_${index}`].value = balance.max_quantity" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium bg-indigo-50 px-2 py-1.5 rounded border border-indigo-100">
                                        Max
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Catatan <span class="text-gray-500 font-normal">(Opsional)</span>
                </label>
                <textarea name="notes" rows="2" placeholder="Contoh: Dipindahkan karena kebutuhan perkuliahan..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.inventory.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </a>
                <button type="submit" :disabled="!targetLabId || !itemDetails" class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                    Pindahkan Barang
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('transferForm', () => ({
                sourceLabId: '{{ $sourceLabId ?? old('source_lab_id', '') }}',
                targetLabId: '{{ old('target_lab_id', '') }}',
                items: [],
                selectedItem: null,
                selectedItemId: '{{ old('item_id', '') }}',
                itemDetails: null,
                isLoadingItems: false,
                isLoadingDetails: false,
                
                async loadItems() {
                    if (!this.sourceLabId) {
                        this.items = [];
                        this.selectedItemId = '';
                        this.itemDetails = null;
                        return;
                    }
                    this.isLoadingItems = true;
                    try {
                        const response = await fetch(`/admin/api/inventory/${this.sourceLabId}/items`);
                        this.items = await response.json();
                        
                        // If we have an old selected item that still exists, load its details
                        if (this.selectedItemId && this.items.find(i => i.id == this.selectedItemId)) {
                            this.loadItemDetails();
                        } else {
                            this.selectedItemId = '';
                            this.itemDetails = null;
                        }
                    } catch (e) {
                        console.error('Failed to load items:', e);
                    }
                    this.isLoadingItems = false;
                },
                
                async loadItemDetails() {
                    if (!this.selectedItemId || !this.sourceLabId) {
                        this.itemDetails = null;
                        this.selectedItem = null;
                        return;
                    }
                    
                    this.selectedItem = this.items.find(i => i.id == this.selectedItemId);
                    this.isLoadingDetails = true;
                    
                    try {
                        const response = await fetch(`/admin/api/inventory/${this.sourceLabId}/items/${this.selectedItemId}`);
                        this.itemDetails = await response.json();
                    } catch (e) {
                        console.error('Failed to load item details:', e);
                    }
                    this.isLoadingDetails = false;
                },
                
                init() {
                    if (this.sourceLabId) {
                        this.loadItems();
                    }
                    this.$watch('sourceLabId', (newValue, oldValue) => {
                        if (newValue !== oldValue) {
                            this.selectedItemId = '';
                            this.itemDetails = null;
                            this.loadItems();
                        }
                    });
                    this.$watch('selectedItemId', value => {
                        this.loadItemDetails();
                    });
                }
            }));
        });
    </script>
    @endpush
@endsection
