@extends('layouts.admin')

@section('title', 'Inventaris ' . $lab->name . ' - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.labs.index') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Lab
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Inventaris {{ $lab->name }}</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            @if($lab->name === 'Gudang')
                <a href="{{ route('admin.external-transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Transfer ke Eksternal
                </a>
            @endif
            @if($lab->name === 'Eksternal')
                <a href="{{ route('admin.external-transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Riwayat Transfer Eksternal
                </a>
            @endif
            @if($lab->name !== 'Eksternal')
                <a href="{{ route('admin.inventory.transfer.create', ['source_lab_id' => $lab->id]) }}" class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Pindah Barang
                </a>
                <a href="{{ route('admin.inventory.logs.index') }}?lab_id={{ $lab->id }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Arus Barang
                </a>
            @endif
            @if($lab->name === 'Eksternal')
                <a href="{{ route('admin.inventory.transfer.create', ['source_lab_id' => $lab->id]) }}" class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Pindah Barang
                </a>
            @endif
            <a href="{{ route('admin.labs.inventory.ledger', $lab) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Riwayat Transaksi 
            </a>
            @if($lab->name !== 'Eksternal')
                <a href="{{ route('admin.labs.inventory.create', $lab) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Barang
                </a>
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
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @php
            $totalBaik = collect($summary)->sum(fn($s) => $s['conditions']['BAIK']);
            $totalRusak = collect($summary)->sum(fn($s) => $s['conditions']['RUSAK']);
            $totalHilang = collect($summary)->sum(fn($s) => $s['conditions']['HILANG']);
            $totalMaintenance = collect($summary)->sum(fn($s) => $s['conditions']['MAINTENANCE']);
        @endphp
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-3xl font-bold text-gray-700">{{ $totalBaik }}</div>
            <div class="text-sm text-gray-600 font-medium">Baik</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-3xl font-bold text-gray-700">{{ $totalRusak }}</div>
            <div class="text-sm text-gray-600 font-medium">Rusak</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-3xl font-bold text-gray-700">{{ $totalHilang }}</div>
            <div class="text-sm text-gray-600 font-medium">Hilang</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-3xl font-bold text-gray-700">{{ $totalMaintenance }}</div>
            <div class="text-sm text-gray-600 font-medium">Maintenance</div>
        </div>
    </div>

    <!-- Inventory List -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        @if(count($summary) > 0)
            <!-- Mobile Card View -->
            <div class="grid grid-cols-1 gap-4 md:hidden p-4">
                @foreach($summary as $item)
                    <div class="bg-white border rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">{{ $item['name'] }}</h3>
                                @if($item['category'])
                                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800">
                                        {{ $item['category'] }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs font-medium px-2 py-1 rounded-full 
                                {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'bg-purple-100 text-purple-800' : 
                                   ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'Tag' : 
                                   ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'Seat' : 'Agregat') }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-2 text-center mb-3">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-700">{{ $item['conditions']['BAIK'] }}</div>
                                <div class="text-xs text-gray-600">Baik</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-700">{{ $item['conditions']['RUSAK'] }}</div>
                                <div class="text-xs text-gray-600">Rusak</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-700">{{ $item['conditions']['HILANG'] }}</div>
                                <div class="text-xs text-gray-600">Hilang</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-700">{{ $item['conditions']['MAINTENANCE'] }}</div>
                                <div class="text-xs text-gray-600">Maint.</div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            @if($item['tracking_mode'] !== 'AGGREGATE')
                                <a href="{{ route('admin.labs.inventory.units', [$lab, $item['id']]) }}" class="inline-flex items-center px-3 py-2 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-100 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    Lihat Unit
                                </a>
                            @else
                                <a href="{{ route('admin.labs.inventory.balances', [$lab, $item['id']]) }}" class="inline-flex items-center px-3 py-2 bg-orange-50 text-orange-700 text-sm font-semibold rounded-lg hover:bg-orange-100 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Lihat Saldo
                                </a>
                            @endif
                            <button onclick="confirmDeleteItem('{{ $item['id'] }}', '{{ $item['name'] }}', {{ $item['total'] }})" class="ml-2 inline-flex items-center px-3 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                            <th class="px-6 py-4 font-semibold">Nama Aset</th>
                            <th class="px-6 py-4 font-semibold text-center">Kategori</th>
                            <th class="px-6 py-4 font-semibold text-center">Mode</th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-gray-600">Baik</span>
                            </th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-gray-600">Rusak</span>
                            </th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-gray-600">Hilang</span>
                            </th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-gray-600">Maintenance</span>
                            </th>
                            <th class="px-6 py-4 font-semibold text-center">Total</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($summary as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($item['category'])
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-800">
                                            {{ $item['category'] }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full 
                                        {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'bg-purple-100 text-purple-800' : 
                                           ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') }}">
                                        {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'Structured Tag' : 
                                           ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'Seat Number' : 'Aggregate') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-gray-100 text-gray-700 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['BAIK'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-gray-100 text-gray-700 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['RUSAK'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-gray-100 text-gray-700 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['HILANG'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-gray-100 text-gray-700 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['MAINTENANCE'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-blue-100 text-blue-800 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['total'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($item['tracking_mode'] !== 'AGGREGATE')
                                            <a href="{{ route('admin.labs.inventory.units', [$lab, $item['id']]) }}" class="text-blue-600 hover:text-blue-800 p-1 rounded-md hover:bg-blue-50 transition-colors" title="Lihat Unit">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                                </svg>
                                            </a>
                                        @else
                                            <a href="{{ route('admin.labs.inventory.balances', [$lab, $item['id']]) }}" class="text-orange-600 hover:text-orange-800 p-1 rounded-md hover:bg-orange-50 transition-colors" title="Lihat Saldo">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        
                                        <!-- Delete Item Button -->
                                        <button onclick="confirmDeleteItem('{{ $item['id'] }}', '{{ $item['name'] }}', {{ $item['total'] }})" class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-50 transition-colors" title="Hapus Barang">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    @if($lab->name === 'Eksternal')
                        <p class="text-lg font-medium">Belum ada barang di ruangan Eksternal</p>
                        <p class="text-sm mb-4">Barang akan muncul setelah ditransfer dari Gudang</p>
                        <a href="{{ route('admin.external-transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Transfer dari Gudang
                        </a>
                    @else
                        <p class="text-lg font-medium">Belum ada inventaris di lab ini</p>
                        <p class="text-sm mb-4">Silakan tambahkan data barang baru</p>
                        <a href="{{ route('admin.labs.inventory.create', $lab) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Barang
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Item Confirmation Modal -->
    <div id="deleteItemModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="relative mx-auto w-full max-w-md px-4">
            <div class="bg-white rounded-2xl shadow-2xl p-6">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4">
                    <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <!-- Title -->
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Hapus Barang</h3>
                
                <!-- Message -->
                <div class="mb-6">
                    <p class="text-sm text-gray-600 text-center">
                        Apakah Anda yakin ingin menghapus
                    </p>
                    <p class="text-sm text-gray-900 text-center font-semibold mt-1">
                        <strong id="deleteItemName"></strong>
                    </p>
                    <p class="text-sm text-gray-600 text-center mt-1">
                        <span class="text-red-600 font-semibold"><span id="deleteItemCount"></span> unit</span> akan dihapus secara permanen.
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400">
                        Batal
                    </button>
                    <form id="deleteItemForm" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDeleteItem(itemId, itemName, itemCount) {
            document.getElementById('deleteItemName').textContent = itemName;
            document.getElementById('deleteItemCount').textContent = itemCount;
            document.getElementById('deleteItemForm').action = `{{ route('admin.labs.inventory', $lab) }}/${itemId}`;
            document.getElementById('deleteItemModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteItemModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('deleteItemModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
    @endpush
@endsection
