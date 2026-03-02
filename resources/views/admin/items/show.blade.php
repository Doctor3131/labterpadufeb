@extends('layouts.admin')

@section('title', 'Detail Barang: ' . $item->name . ' - Lab Digital FEB UNDIP')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-colors group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Inventaris Global
        </a>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $item->name }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                @if($item->category)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                        {{ $item->category }}
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 border border-purple-200">
                    {{ $item->tracking_mode->label() }}
                </span>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl px-6 py-4 text-center min-w-[150px]">
            <div class="text-sm text-blue-600 font-semibold mb-1">Total Keseluruhan</div>
            <div class="text-4xl font-black text-blue-700">{{ $totalUnits }}</div>
        </div>
    </div>

    <!-- Metadata Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Detail Informasi Barang
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="text-sm font-medium text-gray-500 mb-1">Merk / Brand</div>
                <div class="font-semibold text-gray-900">{{ $item->brand ?: '-' }}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 md:col-span-1 lg:col-span-3">
                <div class="text-sm font-medium text-gray-500 mb-1">Spesifikasi</div>
                <div class="font-semibold text-gray-900">{{ $item->description ?: '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Stock Locations -->
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Lokasi & Ketersediaan
    </h2>

    @if($item->hasIndividualUnits())
        @forelse($unitsByLab as $labName => $units)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $labName }}
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-white text-gray-700 border border-gray-200 shadow-sm">
                        {{ $units->count() }} Unit
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left table-fixed min-w-[1000px]">
                        <thead class="bg-white text-gray-600 font-semibold text-sm border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 w-16 text-center">No</th>
                                <th class="px-6 py-4 w-48">Asset Tag</th>
                                <th class="px-6 py-4 w-40 text-center">Kondisi</th>
                                <th class="px-6 py-4 w-40">Status</th>
                                <th class="px-6 py-4">Deskripsi Sumber</th>
                                <th class="px-6 py-4 w-48 text-right">Harga per Unit</th>
                                <th class="px-6 py-4 w-32 text-right">Tahun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($units as $index => $unit)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-center text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-mono text-sm font-bold text-gray-700">
                                        {{ $unit->asset_tag ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $conditionColors = [
                                                'BAIK' => 'bg-green-100 text-green-800 border-green-200',
                                                'RUSAK' => 'bg-red-100 text-red-800 border-red-200',
                                                'HILANG' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                'MAINTENANCE' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            ];
                                            $colorClass = $conditionColors[$unit->condition->value] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $colorClass }}">
                                            {{ $unit->condition->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($unit->status === 'borrowed')
                                            <span class="text-orange-600 font-medium text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Dipinjam
                                            </span>
                                        @elseif($unit->is_available)
                                            <span class="text-green-600 font-medium text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Tersedia
                                            </span>
                                        @else
                                            <span class="text-gray-500 font-medium text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                Tidak Tersedia
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 text-sm truncate max-w-xs" title="{{ $unit->batch->source_description }}">
                                        {{ $unit->batch->source_description ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-700 font-medium text-sm">
                                        {{ $unit->batch->unit_price ? 'Rp ' . number_format($unit->batch->unit_price, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-600 font-mono text-sm">
                                        @php
                                            $mmyy = $unit->batch->arrival_mmyy;
                                            $year = strlen($mmyy) == 4 ? '20' . substr($mmyy, 2, 2) : $mmyy;
                                        @endphp
                                        {{ $year ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 rounded-xl p-8 text-center border border-gray-200">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <div class="text-lg font-medium text-gray-900">Belum Ada Lokasi</div>
                <p class="text-gray-500 mt-1">Belum ada unit barang yang tercatat pada inventaris.</p>
            </div>
        @endforelse
    @else
        @forelse($balancesByLab as $labName => $balances)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $labName }}
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-white text-gray-700 border border-gray-200 shadow-sm">
                        {{ $balances->sum('quantity') }} Unit
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left table-fixed min-w-[800px]">
                        <thead class="bg-white text-gray-600 font-semibold text-sm border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 w-16 text-center">No</th>
                                <th class="px-6 py-4 w-40 text-center">Kondisi</th>
                                <th class="px-6 py-4 text-center w-32">Jumlah</th>
                                <th class="px-6 py-4">Deskripsi Sumber</th>
                                <th class="px-6 py-4 w-48 text-right">Harga per Unit</th>
                                <th class="px-6 py-4 text-right w-32">Tahun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($balances as $index => $balance)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-center text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $conditionColors = [
                                                'BAIK' => 'bg-green-100 text-green-800 border-green-200',
                                                'RUSAK' => 'bg-red-100 text-red-800 border-red-200',
                                                'HILANG' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                'MAINTENANCE' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            ];
                                            $colorClass = $conditionColors[$balance->condition->value] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $colorClass }}">
                                            {{ $balance->condition->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-bold text-gray-800">{{ $balance->quantity }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 text-sm truncate max-w-xs" title="{{ $balance->batch->source_description }}">
                                        {{ $balance->batch->source_description ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-700 font-medium text-sm">
                                        {{ $balance->batch->unit_price ? 'Rp ' . number_format($balance->batch->unit_price, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-600 font-mono text-sm">
                                        @php
                                            $mmyy = $balance->batch->arrival_mmyy;
                                            $year = strlen($mmyy) == 4 ? '20' . substr($mmyy, 2, 2) : $mmyy;
                                        @endphp
                                        {{ $year ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-gray-50 rounded-xl p-8 text-center border border-gray-200">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <div class="text-lg font-medium text-gray-900">Belum Ada Lokasi</div>
                <p class="text-gray-500 mt-1">Belum ada stok barang yang tercatat pada inventaris.</p>
            </div>
        @endforelse
    @endif
</div>
@endsection
