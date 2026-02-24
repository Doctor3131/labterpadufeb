@extends('layouts.admin')

@section('title', 'Saldo ' . $item->name . ' - ' . $lab->name)

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
        <h1 class="text-2xl font-bold text-gray-800">{{ $item->name }}</h1>
        <p class="text-sm text-gray-600">{{ $lab->name }} • Tipe Agregat</p>
    </div>

    <!-- Item Specifications -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Spesifikasi Barang
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Nama Barang -->
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Aset</span>
                <span class="text-base font-medium text-gray-900">{{ $item->name }}</span>
            </div>

            <!-- Kode Tipe Aset -->
            @if($item->assetTypeCode)
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Kode Tipe Aset</span>
                <div class="flex items-center gap-2">
                    <span class="text-base font-mono font-bold text-gray-900">{{ $item->assetTypeCode->code }}</span>
                    <span class="text-sm text-gray-600">({{ $item->assetTypeCode->name }})</span>
                </div>
            </div>
            @endif

            <!-- Mode Tracking -->
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tipe Aset</span>
                <span class="inline-flex items-center w-fit px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Aggregate
                </span>
            </div>

            <!-- Can Be Borrowed -->
            @if($item->assetTypeCode)
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Status Peminjaman</span>
                <span class="inline-flex items-center w-fit px-3 py-1 rounded-full text-sm font-semibold
                    {{ $item->assetTypeCode->is_borrowable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    @if($item->assetTypeCode->is_borrowable)
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Dapat Dipinjam
                    @else
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tidak Dapat Dipinjam
                    @endif
                </span>
            </div>
            @endif

            <!-- Total Batches -->
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Batch</span>
                <span class="text-base font-bold text-blue-600">{{ $balances->count() }} batch</span>
            </div>

            <!-- Description (Full Width) -->
            @if($item->description)
            <div class="flex flex-col md:col-span-2 lg:col-span-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Deskripsi</span>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $item->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-r-lg shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Balances by Batch -->
    @foreach($balances as $batchId => $batchBalances)
        @php
            $batch = $batchBalances->first()->batch;
            $totalQty = $batchBalances->sum('quantity');
        @endphp
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-6">
            {{-- Batch Header --}}
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">Batch: {{ $batch->proc_source_code }}.{{ $batch->arrival_mmyy }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ $batch->arrival_formatted }}
                        @if($batch->source_description) &bull; {{ $batch->source_description }} @endif
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-gray-800">{{ $totalQty }}</span>
                    <span class="text-sm text-gray-500 ml-1">total</span>
                </div>
            </div>

            {{-- Per-Condition Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3 text-left">Kondisi</th>
                            <th class="px-6 py-3 text-center">Jumlah Unit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($conditions as $condition)
                            @php
                                $balance = $batchBalances->firstWhere('condition', $condition);
                                $qty = $balance ? $balance->quantity : 0;
                                $savedCode = $balance?->university_asset_code_prefix ?? '';
                                $balanceId = $balance?->id ?? null;
                            @endphp
                            @if($qty > 0 || $balance)
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- Kondisi --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $condition->colorClass() }} border">
                                        {{ $condition->label() }}
                                    </span>
                                </td>

                                {{-- Jumlah --}}
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2.5rem] px-3 py-1 rounded-full text-base font-bold
                                        {{ $qty > 0 ? 'bg-gray-100 text-gray-800' : 'bg-gray-50 text-gray-400' }}">
                                        {{ $qty }}
                                    </span>
                                </td>

                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Transfer Section - Per Item -->
            @php
                // Pre-compute all generated codes per condition for this batch
                $batchConditionCodes = [];
                foreach ($conditions as $cond) {
                    $bal = $batchBalances->firstWhere('condition', $cond);
                    $q = $bal ? $bal->quantity : 0;
                    $pfx = $bal?->university_asset_code_prefix ?? '';
                    $generated = [];
                    if ($q > 0) {
                        if ($pfx) {
                            if (preg_match('/^(.+\.)([A-Za-z]*)(\d+)$/', $pfx, $pm)) {
                                for ($pi = 0; $pi < $q; $pi++) {
                                    $generated[] = ['id' => $pi, 'code' => $pm[1] . $pm[2] . ((int)$pm[3] + $pi)];
                                }
                            } else {
                                for ($pi = 1; $pi <= $q; $pi++) {
                                    $generated[] = ['id' => $pi - 1, 'code' => $pfx . '-' . $pi];
                                }
                            }
                        } else {
                            // Belum ada prefix: biarkan code kosong, agar bisa diketik manual
                            for ($pi = 0; $pi < $q; $pi++) {
                                $generated[] = ['id' => $pi, 'code' => ''];
                            }
                        }
                    }
                    $batchConditionCodes[$cond->value] = [
                        'qty'   => $q,
                        'codes' => $generated,
                        'label' => $cond->label(),
                    ];
                }
            @endphp

            <div class="border-t border-gray-200 px-6 py-5 bg-gradient-to-br from-gray-50 to-blue-50"
                 x-data="{
                     tab: 'condition',
                     fromCond: '{{ collect($conditions)->first(fn($c) => ($batchBalances->firstWhere('condition', $c)?->quantity ?? 0) > 0)?->value ?? '' }}',
                     toCond: '',
                     targetLab: '',
                     selected: [],
                     condCodes: {{ Js::from($batchConditionCodes) }},
                     get currentCodes() { return this.condCodes[this.fromCond]?.codes ?? []; },
                     get currentQty()  { return this.condCodes[this.fromCond]?.qty ?? 0; },
                     get selectedCodesText() {
                         if (!this.currentCodes || this.currentCodes.length === 0 || this.selected.length === 0) return '';
                         return 'Kode: ' + this.currentCodes.filter(c => this.selected.includes(c.id)).map(c => c.code).join(', ');
                     },
                     selectAll()   { this.selected = this.currentCodes.map(c => c.id); },
                     clearAll()    { this.selected = []; },
                     toggle(id)  {
                         const i = this.selected.indexOf(id);
                         i === -1 ? this.selected.push(id) : this.selected.splice(i, 1);
                     },
                     isChecked(id) { return this.selected.includes(id); },
                     resetFrom() { this.selected = []; }
                 }">

                {{-- Tabs --}}
                <div class="flex gap-2 mb-5">
                    <button @click="tab = 'condition'"
                        :class="tab === 'condition' ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'"
                        class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Ubah Status Kondisi
                    </button>
                    <button @click="tab = 'room'"
                        :class="tab === 'room' ? 'bg-indigo-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'"
                        class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Pindah Ruangan
                    </button>
                </div>

                {{-- Shared: Select Source Condition + Item Checkboxes --}}
                <div class="bg-white rounded-xl border-2 p-4 mb-4"
                     :class="tab === 'condition' ? 'border-blue-100' : 'border-indigo-100'">

                    <!-- Source Condition Selector -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="inline-flex items-center justify-center bg-red-100 text-red-800 text-xs font-bold rounded-full w-5 h-5 mr-1">1</span>
                            Pilih Kondisi Asal
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($conditions as $cond)
                                @php $cQty = $batchBalances->firstWhere('condition', $cond)?->quantity ?? 0; @endphp
                                @if($cQty > 0)
                                <button type="button"
                                    @click="fromCond = '{{ $cond->value }}'; resetFrom()"
                                    :class="fromCond === '{{ $cond->value }}' ? 'ring-2 ring-offset-1 ring-blue-500 scale-105' : 'opacity-70 hover:opacity-100'"
                                    class="{{ $cond->colorClass() }} border px-4 py-2 rounded-full text-sm font-semibold transition-all">
                                    {{ $cond->label() }} ({{ $cQty }})
                                </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Item Checkbox List -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-semibold text-gray-700">
                                <span class="inline-flex items-center justify-center bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full w-5 h-5 mr-1">2</span>
                                Pilih Barang yang Dipindahkan
                                <span class="ml-2 font-normal text-gray-500">(<span x-text="selected.length"></span> dipilih)</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" @click="selectAll()"
                                    class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 font-semibold transition-colors">
                                    Pilih Semua
                                </button>
                                <button type="button" @click="clearAll()"
                                    class="text-xs px-3 py-1 bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 font-semibold transition-colors">
                                    Bersihkan
                                </button>
                            </div>
                        </div>

                        <!-- Code List -->
                        <div x-show="currentQty > 0">
                            <div class="max-h-56 overflow-y-auto rounded-lg border border-gray-200 divide-y divide-gray-100">
                                <template x-for="(item, idx) in currentCodes" :key="item.id">
                                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-yellow-50 transition-colors"
                                           :class="isChecked(item.id) ? 'bg-yellow-50' : ''">
                                        <input type="checkbox"
                                               :checked="isChecked(item.id)"
                                               @change="toggle(item.id)"
                                               class="w-4 h-4 rounded accent-yellow-500 cursor-pointer shrink-0">
                                        <span class="text-xs text-gray-400 w-6 text-right shrink-0 cursor-pointer" x-text="(idx + 1) + '.'"></span>
                                        <input type="text" x-model="item.code" 
                                               placeholder="Ketik kode di sini..."
                                               class="font-mono text-sm text-gray-800 bg-transparent border-0 border-b border-transparent focus:border-yellow-400 focus:ring-0 focus:bg-white w-full max-w-sm px-1 py-0.5 rounded transition-colors"
                                               :class="isChecked(item.id) ? 'text-yellow-800 font-semibold' : ''"
                                               title="Ubah kode secara manual jika tidak sesuai">
                                        <span x-show="isChecked(item.id)" class="ml-auto text-xs text-yellow-600 font-semibold whitespace-nowrap">✓ Terpilih</span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div x-show="currentQty === 0" class="rounded-lg border-2 border-dashed border-gray-200 p-4 text-center text-gray-500 text-sm">
                            Tidak ada barang dengan kondisi ini.
                        </div>

                        <!-- Warning if none selected and codes exist -->
                        <p x-show="currentQty > 0 && selected.length === 0"
                           class="text-xs text-orange-500 mt-1.5 font-medium">
                           ⚠ Pilih minimal 1 barang untuk melanjutkan.
                        </p>
                    </div>
                </div>

                {{-- ====== FORM: Ubah Kondisi ====== --}}
                <div x-show="tab === 'condition'" x-transition>
                    <form action="{{ route('admin.labs.inventory.transfer', $lab) }}" method="POST"
                          @submit.prevent="
                            if (currentQty > 0 && selected.length === 0) { alert('Pilih minimal 1 barang.'); return; }
                            $el.submit();
                          ">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $batchId }}">
                        <input type="hidden" name="from_condition" :value="fromCond">
                        <input type="hidden" name="quantity" :value="selected.length || 0">

                        <div class="bg-white rounded-xl border-2 border-blue-100 p-4 space-y-4">
                            <!-- Destination Condition -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="inline-flex items-center justify-center bg-green-100 text-green-800 text-xs font-bold rounded-full w-5 h-5 mr-1">3</span>
                                    Kondisi Tujuan
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($conditions as $cond)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="to_condition" value="{{ $cond->value }}"
                                               x-model="toCond" class="sr-only peer" required>
                                        <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold border-2 transition-all
                                            peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-blue-500 peer-checked:scale-105
                                            {{ $cond->colorClass() }}">
                                            {{ $cond->label() }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan (Opsional)</label>
                                <input type="text" name="notes"
                                       :value="selectedCodesText"
                                       placeholder="Catatan alasan perubahan kondisi..."
                                       class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Summary + Submit -->
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <p class="text-sm text-gray-600">
                                    <span x-text="selected.length || 0"></span>
                                    unit akan dipindahkan
                                    <span x-show="selected.length > 0" class="text-blue-600 font-medium">
                                        (<span x-text="fromCond"></span> → <span x-text="toCond"></span>)
                                    </span>
                                </p>
                                <button type="submit"
                                    :disabled="currentQty > 0 && selected.length === 0"
                                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-lg shadow transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Proses Transfer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ====== FORM: Pindah Ruangan ====== --}}
                <div x-show="tab === 'room'" x-transition>
                    <form action="{{ route('admin.labs.inventory.transfer-aggregate', $lab) }}" method="POST"
                          @submit.prevent="
                            if (currentQty > 0 && selected.length === 0) { alert('Pilih minimal 1 barang.'); return; }
                            $el.submit();
                          ">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $batchId }}">
                        <input type="hidden" name="condition" :value="fromCond">
                        <input type="hidden" name="quantity" :value="selected.length || 0">

                        <div class="bg-white rounded-xl border-2 border-indigo-100 p-4 space-y-4">
                            <!-- Destination Lab -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="inline-flex items-center justify-center bg-indigo-100 text-indigo-800 text-xs font-bold rounded-full w-5 h-5 mr-1">3</span>
                                    Ruangan Tujuan
                                </label>
                                <select name="target_lab_id" x-model="targetLab" required
                                    class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm font-medium focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <option value="">-- Pilih Ruangan Tujuan --</option>
                                    @foreach(\App\Models\Lab::where('id', '!=', $lab->id)->orderBy('name')->get() as $targetLab)
                                        <option value="{{ $targetLab->id }}">{{ $targetLab->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan (Opsional)</label>
                                <input type="text" name="notes"
                                       :value="selectedCodesText"
                                       placeholder="Catatan Pemindahan Barang"
                                       class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Summary + Submit -->
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <p class="text-sm text-gray-600">
                                    <span x-text="selected.length || 0"></span>
                                    unit akan dipindahkan
                                </p>
                                <button type="submit"
                                    :disabled="currentQty > 0 && selected.length === 0"
                                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-lg shadow transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    Proses Pindah Ruangan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach


    @if($balances->isEmpty())
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 text-center text-gray-500">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-lg font-medium">Tidak ada saldo untuk item ini</p>
            <a href="{{ route('admin.labs.inventory.create', $lab) }}" class="inline-flex items-center mt-4 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Barang
            </a>
        </div>
    @endif
@endsection
