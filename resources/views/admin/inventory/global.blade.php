@extends('layouts.admin')

@section('title', 'Inventaris - Lab Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Inventaris</h1>
        <p class="text-gray-600">Data Inventaris Seluruh Laboratorium</p>
    </div>

    <!-- Global Summary Stats -->
    <div class="bg-white rounded-xl p-6 mb-8 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            Data Aset Keseluruhan
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:border-purple-200 transition-colors">
                <div class="text-sm text-gray-500 mb-1 font-medium">Total Jenis Aset</div>
                <div class="text-3xl font-bold text-gray-800">{{ $globalTotals['total_items'] }}</div>
            </div>
            <div class="bg-green-50 rounded-xl p-4 border border-green-100 hover:border-green-200 transition-colors">
                <div class="text-sm text-green-600 mb-1 font-medium">Baik</div>
                <div class="text-3xl font-bold text-green-700">{{ $globalTotals['BAIK'] }}</div>
            </div>
            <div class="bg-red-50 rounded-xl p-4 border border-red-100 hover:border-red-200 transition-colors">
                <div class="text-sm text-red-600 mb-1 font-medium">Rusak</div>
                <div class="text-3xl font-bold text-red-700">{{ $globalTotals['RUSAK'] }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 hover:border-gray-300 transition-colors">
                <div class="text-sm text-gray-500 mb-1 font-medium">Hilang</div>
                <div class="text-3xl font-bold text-gray-700">{{ $globalTotals['HILANG'] }}</div>
            </div>
            <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100 hover:border-yellow-200 transition-colors">
                <div class="text-sm text-yellow-600 mb-1 font-medium">Maintenance</div>
                <div class="text-3xl font-bold text-yellow-700">{{ $globalTotals['MAINTENANCE'] }}</div>
            </div>
        </div>
    </div>

    <!-- Tracking Mode-wise Inventory -->
    <div class="space-y-8">
        @foreach($trackingModes as $mode)
            @if(isset($groupedItems[$mode->value]) && $groupedItems[$mode->value]->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between bg-white">
                        <div class="flex items-center mb-2 md:mb-0">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-800">{{ $mode->label() }}</h2>
                                <p class="text-gray-500 text-sm mt-0.5">{{ $mode->description() }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                             {{ $groupedItems[$mode->value]->unique('name')->count() }} Jenis Aset
                        </span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left table-fixed">
                            <thead class="bg-gray-50/50 text-gray-600 font-semibold text-sm border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 w-16 text-center">No</th>
                                    <th class="px-6 py-4">Barang</th>
                                    @if($mode->value === 'STRUCTURED_TAG')
                                        <th class="px-6 py-4 text-center w-48">Tipe Aset</th>
                                    @endif
                                    <th class="px-6 py-4 text-center w-32">Jumlah Total</th>
                                    <th class="px-6 py-4 text-right w-48">Tahun Pengadaan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($groupedItems[$mode->value] as $index => $item)
                                    @php
                                        $totalUnits = $item->total_units;
                                        // Extract years from batches
                                        $years = $item->batches->map(function($batch) {
                                            $mmyy = $batch->arrival_mmyy;
                                            if (strlen($mmyy) == 4) {
                                                return '20' . substr($mmyy, 2, 2);
                                            }
                                            return $mmyy;
                                        })->filter()->unique()->sort()->implode(', ');
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors group">
                                        <td class="px-6 py-4 text-center text-gray-400 group-hover:text-gray-600">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 truncate" title="{{ $item->name }}">{{ $item->name }}</div>
                                            @if($item->description)
                                                <div class="text-sm text-gray-500 mt-0.5 truncate" title="{{ $item->description }}">{{ $item->description }}</div>
                                            @endif
                                        </td>
                                        @if($mode->value === 'STRUCTURED_TAG')
                                            <td class="px-6 py-4 text-center">
                                                @if($item->assetTypeCode)
                                                    <div class="inline-flex flex-col items-center gap-0.5">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200 font-mono">
                                                            {{ $item->assetTypeCode->code }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">{{ $item->assetTypeCode->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-300">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-50 text-blue-700">
                                                {{ $totalUnits }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-600 font-mono text-sm">
                                            {{ $years ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach

        @if($groupedItems->isEmpty())
             <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Belum ada data inventaris</h3>
                <p class="text-gray-500 mt-1">Inventaris yang ditambahkan akan muncul di halaman ini</p>
            </div>
        @endif
    </div>
@endsection
