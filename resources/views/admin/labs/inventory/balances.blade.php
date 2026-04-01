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
                    <div class="flex items-center gap-2 mt-1.5 group">
                        @if($batch->brand)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-orange-800 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded batch-brand-cell-{{ $batch->id }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ $batch->brand }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400 italic batch-brand-cell-{{ $batch->id }}">Belum ada merk</span>
                        @endif
                        <button onclick="openBatchBrandModal({{ $batch->id }}, '{{ addslashes($batch->brand ?? '') }}')"
                            class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-200 rounded"
                            title="Edit Merk">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                    </div>
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
                            <th class="px-6 py-3 text-center">Aksi</th>
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

                                {{-- Aksi --}}
                                <td class="px-6 py-4 text-center">
                                    @if($qty > 0)
                                    <button
                                        onclick="openConditionModal('{{ $batchId }}', '{{ $condition->value }}', '{{ $condition->label() }}', {{ $qty }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        Ubah Kondisi
                                    </button>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Kode Universitas per Kondisi --}}
            @php
                $hasAnyBalance = false;
                foreach ($conditions as $cond) {
                    $bal = $batchBalances->firstWhere('condition', $cond);
                    if ($bal && $bal->quantity > 0) { $hasAnyBalance = true; break; }
                }
            @endphp
            @if($hasAnyBalance)
            <div class="border-t border-gray-200 px-6 py-5 bg-gray-50">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kode Aset Universitas
                </h4>
                <div class="space-y-4">
                    @foreach($conditions as $cond)
                        @php
                            $bal = $batchBalances->firstWhere('condition', $cond);
                            $qty = $bal ? $bal->quantity : 0;
                            if ($qty <= 0) continue;
                            $pfx = $bal->university_asset_code_prefix ?? '';
                            $generated = [];
                            if ($pfx) {
                                if (preg_match('/^(.+\.)([A-Za-z]*)(\d+)$/', $pfx, $pm)) {
                                    for ($pi = 0; $pi < $qty; $pi++) {
                                        $generated[] = $pm[1] . $pm[2] . ((int)$pm[3] + $pi);
                                    }
                                } else {
                                    for ($pi = 1; $pi <= $qty; $pi++) {
                                        $generated[] = $pfx . '-' . $pi;
                                    }
                                }
                            }
                        @endphp
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            {{-- Kondisi Header --}}
                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $cond->colorClass() }} border">
                                    {{ $cond->label() }}
                                </span>
                                <span class="text-sm font-bold text-gray-700">{{ $qty }} unit</span>
                            </div>

                            {{-- Prefix form: only show when no prefix is set --}}
                            @if(!$pfx)
                            <div class="px-4 py-3 border-b border-gray-100">
                                <form action="{{ route('admin.inventory.balance.update-university-code', $bal->id) }}" method="POST"
                                      class="flex items-center gap-2"
                                      onsubmit="submitPrefixForm(event, this)"
                                      data-qty="{{ $qty }}"
                                      data-codelist="codelist-{{ $bal->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Kode Awal (Prefix)</label>
                                        <input type="text" name="university_asset_code_prefix"
                                               value="{{ $pfx }}"
                                               placeholder="Contoh: 132100102001.X71"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <p class="text-xs text-gray-400 mt-1">Kode berikutnya akan digenerate otomatis secara berurutan</p>
                                    </div>
                                    <div class="pt-4">
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors whitespace-nowrap">
                                            Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                            @else
                            {{-- Show current prefix info --}}
                            <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-500">Prefix:</span>
                                    <span class="font-mono text-sm font-semibold text-blue-800 bg-blue-50 px-2 py-0.5 rounded">{{ $pfx }}</span>
                                </div>
                            </div>
                            @endif

                            {{-- Generated code list --}}
                            <div id="codelist-{{ $bal->id }}" class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                                @if(count($generated) > 0)
                                    @foreach($generated as $gIdx => $gCode)
                                    <div class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                                        <span class="text-xs text-gray-400 w-6 text-right shrink-0">{{ $gIdx + 1 }}.</span>
                                        <span class="font-mono text-sm font-medium text-blue-900 bg-blue-50 px-2 py-0.5 rounded flex-1">{{ $gCode }}</span>
                                        <button
                                            onclick="openSingleConditionModal('{{ $batchId }}', '{{ $cond->value }}', '{{ $cond->label() }}', {{ $qty }}, {{ json_encode($gCode) }})"
                                            class="flex items-center gap-1 px-2.5 py-1 bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-semibold rounded-lg border border-orange-200 whitespace-nowrap transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                            Ubah Kondisi
                                        </button>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="px-4 py-4 text-center text-sm text-gray-400">
                                        Belum ada kode — isi prefix di atas lalu simpan.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
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

<!-- Ubah Kondisi Modal -->
<div id="conditionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900">Ubah Kondisi Barang</h3>
            <button onclick="closeConditionModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="conditionModalForm" method="POST" action="{{ route('admin.labs.inventory.transfer', $lab) }}">
            @csrf
            <input type="hidden" name="batch_id" id="modal_batch_id">
            <input type="hidden" name="from_condition" id="modal_from_condition">
            <input type="hidden" name="item_code" id="modal_item_code_hidden" value="">

            <!-- From -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kondisi Asal</label>
                <span id="modal_from_label" class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 border border-gray-200"></span>
            </div>

            <!-- To Condition -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kondisi Tujuan <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-2" id="modal_to_conditions">
                    @foreach($conditions as $cond)
                    <label class="cursor-pointer">
                        <input type="radio" name="to_condition" value="{{ $cond->value }}" class="sr-only peer" required>
                        <span class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold border-2 transition-all
                            peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-blue-500 peer-checked:scale-105
                            {{ $cond->colorClass() }}"
                            data-cond="{{ $cond->value }}">
                            {{ $cond->label() }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Item Code (only shown in single-item mode) -->
            <div id="modal_item_row" class="mb-4 hidden">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Barang</label>
                <span id="modal_item_code" class="font-mono text-sm font-medium text-blue-900 bg-blue-50 px-3 py-1.5 rounded-lg inline-block"></span>
            </div>

            <!-- Quantity -->
            <div class="mb-4" id="modal_qty_row">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah yang Dipindah <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-2">
                    <input type="number" name="quantity" id="modal_quantity"
                        min="1" required
                        class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center">
                    <span class="text-sm text-gray-500">dari <span id="modal_max_qty" class="font-bold text-gray-700"></span> unit tersedia</span>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan (Opsional)</label>
                <input type="text" name="notes" placeholder="Alasan perubahan kondisi..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="button" onclick="closeConditionModal()"
                    class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Proses Transfer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Batch Brand Edit Modal --}}
<div id="batchBrandModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5);">
    <div class="relative mx-auto w-full max-w-md px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-orange-50 mb-4">
                <svg class="h-7 w-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Edit Merk Barang</h3>
            <p class="text-sm text-gray-500 text-center mb-4">Masukkan nama merk untuk batch ini</p>
            <form id="batchBrandForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Merk / Brand</label>
                    <input type="text" id="batchBrandInput"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        placeholder="Contoh: JBL, Sony, Logitech">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ada merk</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeBatchBrandModal()"
                        class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-orange-500 text-white text-sm font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ---- Batch Brand Edit ----
let _batchBrandId = null;

function openBatchBrandModal(batchId, currentBrand) {
    _batchBrandId = batchId;
    document.getElementById('batchBrandInput').value = currentBrand;
    document.getElementById('batchBrandModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('batchBrandInput').focus(), 100);
}

function closeBatchBrandModal() {
    document.getElementById('batchBrandModal').classList.add('hidden');
    _batchBrandId = null;
}

document.getElementById('batchBrandForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!_batchBrandId) return;
    const brand = document.getElementById('batchBrandInput').value.trim();
    const submitBtn = this.querySelector('[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    try {
        const res = await fetch('/admin/batches/' + _batchBrandId + '/brand', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ brand }),
        });
        const data = await res.json();
        if (data.success) {
            document.querySelectorAll('.batch-brand-cell-' + _batchBrandId).forEach(function(el) {
                if (data.brand) {
                    el.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>${data.brand}`;
                    el.className = 'inline-flex items-center gap-1 text-xs font-semibold text-orange-800 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded batch-brand-cell-' + _batchBrandId;
                    // update button onclick
                    el.nextElementSibling && (el.nextElementSibling.onclick = () => openBatchBrandModal(_batchBrandId, data.brand));
                } else {
                    el.textContent = 'Belum ada merk';
                    el.className = 'text-xs text-gray-400 italic batch-brand-cell-' + _batchBrandId;
                    el.nextElementSibling && (el.nextElementSibling.onclick = () => openBatchBrandModal(_batchBrandId, ''));
                }
            });
        }
    } catch(err) { console.error(err); }
    submitBtn.disabled = false;
    submitBtn.textContent = 'Simpan';
    closeBatchBrandModal();
});

document.getElementById('batchBrandModal').addEventListener('click', function(e) {
    if (e.target === this) closeBatchBrandModal();
});
// ---- End Batch Brand Edit ----

function _setupConditionModal(batchId, fromCondition, fromLabel, maxQty) {
    document.getElementById('modal_batch_id').value = batchId;
    document.getElementById('modal_from_condition').value = fromCondition;
    document.getElementById('modal_from_label').textContent = fromLabel;
    document.getElementById('modal_max_qty').textContent = maxQty;
    const qtyInput = document.getElementById('modal_quantity');
    qtyInput.max = maxQty;
    // Disable the from_condition radio so user can't pick same
    document.querySelectorAll('#modal_to_conditions input[type="radio"]').forEach(r => {
        r.disabled = r.value === fromCondition;
        if (r.value === fromCondition) r.checked = false;
        r.closest('label').querySelector('span').style.opacity = r.value === fromCondition ? '0.3' : '1';
        r.closest('label').style.cursor = r.value === fromCondition ? 'not-allowed' : 'pointer';
    });
    document.querySelectorAll('#modal_to_conditions input[type="radio"]').forEach(r => { if (!r.disabled) r.checked = false; });
    document.querySelector('#conditionModalForm input[name="notes"]').value = '';
}

function openConditionModal(batchId, fromCondition, fromLabel, maxQty) {
    _setupConditionModal(batchId, fromCondition, fromLabel, maxQty);
    // Bulk mode: show qty input freely, clear item_code
    document.getElementById('modal_item_code_hidden').value = '';
    const qtyInput = document.getElementById('modal_quantity');
    qtyInput.value = '';
    qtyInput.readOnly = false;
    qtyInput.classList.remove('bg-gray-100');
    document.getElementById('modal_qty_row').classList.remove('hidden');
    document.getElementById('modal_item_row').classList.add('hidden');
    document.getElementById('conditionModal').classList.remove('hidden');
}

function openSingleConditionModal(batchId, fromCondition, fromLabel, maxQty, itemCode) {
    _setupConditionModal(batchId, fromCondition, fromLabel, maxQty);
    // Single-item mode: lock qty=1, show item code, pass item_code to form
    document.getElementById('modal_item_code_hidden').value = itemCode;
    const qtyInput = document.getElementById('modal_quantity');
    qtyInput.value = 1;
    qtyInput.readOnly = true;
    qtyInput.classList.add('bg-gray-100');
    document.getElementById('modal_item_code').textContent = itemCode;
    document.getElementById('modal_item_row').classList.remove('hidden');
    document.getElementById('modal_qty_row').classList.remove('hidden');
    document.getElementById('conditionModal').classList.remove('hidden');
}

function closeConditionModal() {
    document.getElementById('conditionModal').classList.add('hidden');
}

// Close on backdrop click
document.getElementById('conditionModal').addEventListener('click', function(e) {
    if (e.target === this) closeConditionModal();
});

async function submitPrefixForm(event, form) {
    event.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const qty = parseInt(form.dataset.qty) || 0;
    const codelistId = form.dataset.codelist;
    const prefix = form.querySelector('input[name="university_asset_code_prefix"]').value.trim();

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const formData = new FormData(form);
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        if (data.success) {
            btn.textContent = '✓ Tersimpan';
            btn.classList.replace('bg-blue-600', 'bg-green-600');
            btn.classList.replace('hover:bg-blue-700', 'hover:bg-green-700');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert('Gagal menyimpan.');
            btn.textContent = 'Simpan';
            btn.disabled = false;
        }
    } catch (e) {
        alert('Terjadi kesalahan.');
        btn.textContent = 'Simpan';
        btn.disabled = false;
    }
}
</script>
@endpush

@endsection
