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
        <p class="text-sm text-gray-600">{{ $lab->name }} ({{ $lab->code }}) • Mode Agregat</p>
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
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Barang</span>
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
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mode Tracking</span>
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
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">
                            Batch: {{ $batch->proc_source_code }}.{{ $batch->arrival_mmyy }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $batch->arrival_formatted }}
                            @if($batch->source_description)
                                • {{ $batch->source_description }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold text-gray-800">{{ $totalQty }}</span>
                        <span class="text-sm text-gray-500 ml-1">total</span>
                    </div>
                </div>
            </div>

            <!-- Condition Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6">
                @foreach($conditions as $condition)
                    @php
                        $balance = $batchBalances->firstWhere('condition', $condition);
                        $qty = $balance ? $balance->quantity : 0;
                    @endphp
                    <div class="{{ $condition->colorClass() }} rounded-xl p-4 border">
                        <div class="text-2xl font-bold">{{ $qty }}</div>
                        <div class="text-sm font-medium">{{ $condition->label() }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Transfer Form -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Transfer Antar Kondisi</h4>
                <form action="{{ route('admin.labs.inventory.transfer', $lab) }}" method="POST" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batchId }}">
                    
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari</label>
                        <select name="from_condition" required class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                            @foreach($conditions as $condition)
                                @php $qty = $batchBalances->firstWhere('condition', $condition)?->quantity ?? 0; @endphp
                                <option value="{{ $condition->value }}" {{ $qty == 0 ? 'disabled' : '' }}>
                                    {{ $condition->label() }} ({{ $qty }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Ke</label>
                        <select name="to_condition" required class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                            @foreach($conditions as $condition)
                                <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Jumlah</label>
                        <input type="number" name="quantity" min="1" required 
                            class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                    </div>

                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Opsional" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg text-sm transition-all">
                        Transfer
                    </button>
                </form>
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
