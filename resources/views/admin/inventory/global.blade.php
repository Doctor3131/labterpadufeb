@extends('layouts.admin')

@section('title', 'Inventaris - Laboratorium dan Fasilitas Digital FEB UNDIP')

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
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Inventaris</h1>
            <p class="text-gray-600">Data Inventaris Seluruh Laboratorium</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.labs.inventory.create', $gudangLab) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Stok/Barang
            </a>
            <a href="{{ route('admin.inventory.transfer.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Pindah Barang
            </a>
            <a href="{{ route('admin.inventory.logs.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Arus Barang
            </a>
            <a href="{{ route('admin.external-transfers.index') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Transfer Eksternal
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl p-6 mb-6 border border-gray-200 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <label for="labFilter" class="text-sm font-semibold text-gray-700 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter Ruangan:
            </label>
            <select id="labFilter" 
                    class="flex-1 md:flex-none md:w-64 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all bg-white text-gray-700 font-medium">
                <option value="">Semua Ruangan</option>
                @foreach($labs as $lab)
                    <option value="{{ $lab->id }}" {{ $selectedLabId == $lab->id ? 'selected' : '' }}>
                        {{ $lab->name }}
                    </option>
                @endforeach
            </select>
            @if($selectedLabId)
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Filter Aktif: {{ $labs->firstWhere('id', $selectedLabId)->name ?? 'Unknown' }}
                </span>
            @endif
            <a href="{{ route('admin.inventory.export', ['lab_id' => $selectedLabId ?: null]) }}" class="inline-flex items-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition-all ml-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <!-- Global Summary Stats -->
    <div class="bg-white rounded-xl p-6 mb-8 border border-gray-200 shadow-sm">
        <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            Data Barang Keseluruhan
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:border-purple-200 transition-colors">
                <div class="text-sm text-gray-500 mb-1 font-medium">Total Jenis Barang</div>
                <div class="text-3xl font-bold text-gray-800">{{ $globalTotals['total_items'] }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-200 hover:border-gray-300 transition-colors">
                <div class="text-sm text-gray-600 mb-1 font-medium">Baik</div>
                <div class="text-3xl font-bold text-gray-700">{{ $globalTotals['BAIK'] }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-200 hover:border-gray-300 transition-colors">
                <div class="text-sm text-gray-600 mb-1 font-medium">Rusak</div>
                <div class="text-3xl font-bold text-gray-700">{{ $globalTotals['RUSAK'] }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 hover:border-gray-300 transition-colors">
                <div class="text-sm text-gray-500 mb-1 font-medium">Hilang</div>
                <div class="text-3xl font-bold text-gray-700">{{ $globalTotals['HILANG'] }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 border border-gray-200 hover:border-gray-300 transition-colors">
                <div class="text-sm text-gray-600 mb-1 font-medium">Maintenance</div>
                <div class="text-3xl font-bold text-gray-700">{{ $globalTotals['MAINTENANCE'] }}</div>
            </div>
        </div>
    </div>

    <!-- Tracking Mode-wise Inventory -->
    <div class="space-y-8">
        @foreach($groupedItems as $typeName => $items)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between bg-white">
                    <div class="flex items-center mb-2 md:mb-0">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">{{ $typeName }}</h2>
                            <p class="text-gray-500 text-sm mt-0.5">{{ $items->count() }} item dalam kategori ini</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            {{ $items->unique('name')->count() }} Jenis Barang
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left table-fixed">
                        <thead class="bg-gray-50/50 text-gray-600 font-semibold text-sm border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 w-16 text-center">No</th>
                                <th class="px-6 py-4">Barang</th>
                                <th class="px-6 py-4 text-center w-32">Jumlah Total</th>
                                <th class="px-6 py-4 text-center w-48">Tahun Pengadaan</th>
                                <th class="px-6 py-4 text-center w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($items as $index => $item)
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
                                        <div class="flex items-center gap-3">
                                            @if($item->image_path)
                                                <img src="{{ route('admin.secure-file', ['path' => $item->image_path]) }}" alt="{{ $item->name }}" class="w-10 h-10 object-cover rounded-lg border border-gray-200 shrink-0">
                                            @else
                                                <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                        <span class="font-bold text-gray-800 truncate inline-block" title="{{ $item->name }}">
                                            {{ $item->name }}
                                        </span>
                                        @if($item->description)
                                            <div class="text-sm text-gray-500 mt-0.5 truncate" title="{{ $item->description }}">{{ $item->description }}</div>
                                        @endif
                                        <div class="mt-1">
                                            @if($item->category)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $item->category }}
                                                </span>
                                            @endif
                                            @if($item->tracking_mode === \App\Enums\TrackingModeEnum::STRUCTURED_TAG)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    Structured Tag
                                                </span>
                                            @elseif($item->tracking_mode === \App\Enums\TrackingModeEnum::SEAT_NUMBER)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Seat Number
                                                </span>
                                            @elseif($item->tracking_mode === \App\Enums\TrackingModeEnum::AGGREGATE)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    Aggregate
                                                </span>
                                            @endif
                                        </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-50 text-blue-700">
                                            {{ $totalUnits }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-600 font-mono text-sm">
                                        {{ $years ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.items.show', $item) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-50 text-yellow-700 text-sm font-semibold rounded-lg hover:bg-yellow-100 border border-yellow-200 transition-colors" title="Lihat Detail {{ $item->name }}">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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

    @push('scripts')
    <script>
        document.getElementById('labFilter').addEventListener('change', function() {
            const labId = this.value;
            const url = new URL(window.location.href);
            
            if (labId) {
                url.searchParams.set('lab_id', labId);
            } else {
                url.searchParams.delete('lab_id');
            }
            
            window.location.href = url.toString();
        });
    </script>
    @endpush
@endsection
