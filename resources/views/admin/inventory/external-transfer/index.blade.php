@extends('layouts.admin')

@section('title', 'Transfer Eksternal - Lab Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Inventaris
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Transfer Eksternal</h1>
            <p class="text-gray-600">Riwayat barang yang ditransfer dari Gudang ke pihak eksternal.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.external-transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Transfer Baru
            </a>
            @if($eksternalLab)
                <a href="{{ route('admin.labs.inventory', $eksternalLab) }}" class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Lihat Inventaris Eksternal
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
    @php
        $totalTransfers = $transfers->total();
        $totalQuantity = $transfers->sum('quantity');
    @endphp
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-3xl font-bold text-amber-600">{{ $totalTransfers }}</div>
            <div class="text-sm text-gray-600 font-medium">Total Transfer</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-3xl font-bold text-amber-600">{{ $totalQuantity }}</div>
            <div class="text-sm text-gray-600 font-medium">Total Barang</div>
        </div>
    </div>

    <!-- Transfer List -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        @if($transfers->count() > 0)
            <!-- Mobile Card View -->
            <div class="grid grid-cols-1 gap-4 md:hidden p-4">
                @foreach($transfers as $transfer)
                    <div class="bg-white border rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">{{ $transfer->item_name }}</h3>
                                <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-100 text-amber-800">
                                    {{ $transfer->quantity }} unit
                                </span>
                            </div>
                            <span class="text-xs text-gray-500">{{ $transfer->transfer_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="space-y-1.5 text-sm">
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="font-medium">{{ $transfer->recipient }}</span>
                            </div>
                            @if($transfer->notes)
                                <div class="flex items-start text-gray-500">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <span class="text-xs">{{ $transfer->notes }}</span>
                                </div>
                            @endif
                            @if($transfer->user)
                                <div class="text-xs text-gray-400 mt-1">Oleh: {{ $transfer->user->name }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                            <th class="px-6 py-4 font-semibold">Tanggal</th>
                            <th class="px-6 py-4 font-semibold">Nama Barang</th>
                            <th class="px-6 py-4 font-semibold">Penerima</th>
                            <th class="px-6 py-4 font-semibold text-center">Jumlah</th>
                            <th class="px-6 py-4 font-semibold">Catatan</th>
                            <th class="px-6 py-4 font-semibold">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($transfers as $transfer)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $transfer->transfer_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $transfer->item_name }}
                                    <div class="text-xs text-gray-500">{{ $transfer->tracking_mode }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $transfer->recipient }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-amber-100 text-amber-800 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $transfer->quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $transfer->notes ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $transfer->user->name ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transfers->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $transfers->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 text-gray-500">
                <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <p class="text-lg font-medium">Belum ada transfer ke Eksternal</p>
                    <p class="text-sm mb-4">Transfer barang dari Gudang ke pihak luar</p>
                    <a href="{{ route('admin.external-transfers.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Transfer Baru
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
