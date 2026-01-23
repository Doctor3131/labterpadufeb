@extends('layouts.admin')

@section('title', 'Inventaris ' . $lab->name . ' - Lab Digital FEB UNDIP')

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
            <p class="text-sm text-gray-600">Kode: {{ $lab->code }} • {{ $lab->location }}</p>
        </div>
        <a href="{{ route('admin.labs.inventory.create', $lab) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Barang
        </a>
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
        <div class="bg-green-50 rounded-xl p-4 border border-green-200">
            <div class="text-3xl font-bold text-green-700">{{ $totalBaik }}</div>
            <div class="text-sm text-green-600 font-medium">Baik</div>
        </div>
        <div class="bg-red-50 rounded-xl p-4 border border-red-200">
            <div class="text-3xl font-bold text-red-700">{{ $totalRusak }}</div>
            <div class="text-sm text-red-600 font-medium">Rusak</div>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <div class="text-3xl font-bold text-gray-700">{{ $totalHilang }}</div>
            <div class="text-sm text-gray-600 font-medium">Hilang</div>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
            <div class="text-3xl font-bold text-yellow-700">{{ $totalMaintenance }}</div>
            <div class="text-sm text-yellow-600 font-medium">Maintenance</div>
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
                            <h3 class="font-bold text-gray-800 text-lg">{{ $item['name'] }}</h3>
                            <span class="text-xs font-medium px-2 py-1 rounded-full 
                                {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'bg-purple-100 text-purple-800' : 
                                   ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'Tag' : 
                                   ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'Seat' : 'Agregat') }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-4 gap-2 text-center mb-3">
                            <div class="bg-green-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-green-700">{{ $item['conditions']['BAIK'] }}</div>
                                <div class="text-xs text-green-600">Baik</div>
                            </div>
                            <div class="bg-red-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-red-700">{{ $item['conditions']['RUSAK'] }}</div>
                                <div class="text-xs text-red-600">Rusak</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-700">{{ $item['conditions']['HILANG'] }}</div>
                                <div class="text-xs text-gray-600">Hilang</div>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-yellow-700">{{ $item['conditions']['MAINTENANCE'] }}</div>
                                <div class="text-xs text-yellow-600">Maint.</div>
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
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                            <th class="px-6 py-4 font-semibold">Nama Barang</th>
                            <th class="px-6 py-4 font-semibold text-center">Mode</th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-green-600">Baik</span>
                            </th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-red-600">Rusak</span>
                            </th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-gray-600">Hilang</span>
                            </th>
                            <th class="px-6 py-4 font-semibold text-center">
                                <span class="text-yellow-600">Maintenance</span>
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
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full 
                                        {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'bg-purple-100 text-purple-800' : 
                                           ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800') }}">
                                        {{ $item['tracking_mode'] === 'STRUCTURED_TAG' ? 'Structured Tag' : 
                                           ($item['tracking_mode'] === 'SEAT_NUMBER' ? 'Seat Number' : 'Aggregate') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-green-100 text-green-800 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['BAIK'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-red-100 text-red-800 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['RUSAK'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-gray-100 text-gray-800 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['HILANG'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-yellow-100 text-yellow-800 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['conditions']['MAINTENANCE'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] bg-blue-100 text-blue-800 text-sm font-bold px-2.5 py-0.5 rounded-full">
                                        {{ $item['total'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
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
                    <p class="text-lg font-medium">Belum ada inventaris di lab ini</p>
                    <p class="text-sm mb-4">Silakan tambahkan data barang baru</p>
                    <a href="{{ route('admin.labs.inventory.create', $lab) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Barang
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
