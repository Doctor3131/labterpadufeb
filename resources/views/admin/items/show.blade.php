@extends('layouts.admin')

@section('title', 'Detail Barang: ' . $item->name . ' - Laboratorium dan Fasilitas Digital FEB UNDIP')

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
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 md:col-span-1 lg:col-span-4">
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
                    <div class="flex items-center gap-2">
                        <button id="bulk-unit-btn-{{ $loop->index }}"
                            onclick="openBulkBrandModal({{ $loop->index }})"
                            class="hidden items-center gap-1.5 px-3 py-1.5 bg-orange-500 text-white text-xs font-semibold rounded-lg hover:bg-orange-600 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Ubah Merk (<span id="bulk-unit-count-{{ $loop->index }}">0</span> dipilih)
                        </button>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-white text-gray-700 border border-gray-200 shadow-sm">
                            {{ $units->count() }} Unit
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left table-fixed min-w-[1050px]">
                        <thead class="bg-white text-gray-600 font-semibold text-sm border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-4 w-10 text-center">
                                    <input type="checkbox" id="select-all-unit-{{ $loop->index }}"
                                        onchange="toggleSelectAllUnits({{ $loop->index }}, this.checked)"
                                        class="w-4 h-4 rounded border-gray-300 text-orange-500 cursor-pointer">
                                </th>
                                <th class="px-6 py-4 w-16 text-center">No</th>
                                <th class="px-6 py-4 w-44">Merk</th>
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
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox"
                                            class="bulk-unit-cb w-4 h-4 rounded border-gray-300 text-orange-500 cursor-pointer"
                                            data-group="{{ $loop->parent->index }}"
                                            data-id="{{ $unit->id }}"
                                            onchange="onUnitCheckboxChange({{ $loop->parent->index }})">
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 group">
                                            @if($unit->brand)
                                                <span class="font-medium text-sm text-orange-800 bg-orange-50 px-2 py-1 rounded unit-brand-cell-{{ $unit->id }}">{{ $unit->brand }}</span>
                                            @else
                                                <span class="text-gray-400 text-sm unit-brand-cell-{{ $unit->id }}">-</span>
                                            @endif
                                            <button onclick="openEditBrandModal({{ $unit->id }}, 'unit')"
                                                class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-100 rounded"
                                                title="Edit Merk">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
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
                                <th class="px-6 py-4 w-44">Merk</th>
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
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 group">
                                            @if($balance->brand)
                                                <span class="font-medium text-sm text-orange-800 bg-orange-50 px-2 py-1 rounded bal-brand-cell-{{ $balance->id }}">{{ $balance->brand }}</span>
                                            @else
                                                <span class="text-gray-400 text-sm bal-brand-cell-{{ $balance->id }}">-</span>
                                            @endif
                                            <button onclick="openEditBrandModal({{ $balance->id }}, 'balance')"
                                                class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-gray-100 rounded"
                                                title="Edit Merk">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
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

{{-- Bulk Edit Brand Modal --}}
<div id="bulkBrandModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5);">
    <div class="relative mx-auto w-full max-w-md px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-50 mb-4">
                <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-1">Ubah Merk Massal</h3>
            <p class="text-sm text-gray-500 text-center mb-4">Mengubah merk untuk <span id="bulk-selected-count" class="font-semibold text-orange-600">0</span> unit yang dipilih</p>
            <form id="bulkBrandForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Merk / Brand Baru</label>
                    <input type="text" id="bulkBrandInput"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        placeholder="Contoh: JBL, Sony, Dell, HP">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika ingin menghapus merk dari semua unit terpilih</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeBulkBrandModal()"
                        class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors">
                        Simpan Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Brand Modal --}}
<div id="editBrandModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5);">
    <div class="relative mx-auto w-full max-w-md px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-50 mb-4">
                <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Edit Merk Barang</h3>
            <p class="text-sm text-gray-500 text-center mb-4">Masukkan nama merk untuk batch ini</p>
            <form id="editBrandForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Merk / Brand</label>
                    <input type="text" id="brandInput"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        placeholder="Contoh: JBL, Sony, Dell, HP">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ada merk</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditBrandModal()"
                        class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let _brandEntityId = null;
let _brandEntityType = null;

// ---- Bulk Brand Logic ----
let _bulkGroupIdx = null;

function onUnitCheckboxChange(groupIdx) {
    const checkboxes = document.querySelectorAll('.bulk-unit-cb[data-group="' + groupIdx + '"]');
    const checked = Array.from(checkboxes).filter(cb => cb.checked);
    const btn = document.getElementById('bulk-unit-btn-' + groupIdx);
    const countEl = document.getElementById('bulk-unit-count-' + groupIdx);
    const selectAllCb = document.getElementById('select-all-unit-' + groupIdx);
    if (checked.length > 0) {
        btn.classList.remove('hidden');
        btn.classList.add('inline-flex');
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('inline-flex');
    }
    if (countEl) countEl.textContent = checked.length;
    if (selectAllCb) {
        selectAllCb.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
        selectAllCb.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
    }
}

function toggleSelectAllUnits(groupIdx, checked) {
    document.querySelectorAll('.bulk-unit-cb[data-group="' + groupIdx + '"]').forEach(cb => {
        cb.checked = checked;
    });
    onUnitCheckboxChange(groupIdx);
}

function openBulkBrandModal(groupIdx) {
    _bulkGroupIdx = groupIdx;
    const checked = document.querySelectorAll('.bulk-unit-cb[data-group="' + groupIdx + '"]:checked');
    document.getElementById('bulk-selected-count').textContent = checked.length;
    document.getElementById('bulkBrandInput').value = '';
    document.getElementById('bulkBrandModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('bulkBrandInput').focus(), 100);
}

function closeBulkBrandModal() {
    document.getElementById('bulkBrandModal').classList.add('hidden');
    _bulkGroupIdx = null;
}

document.getElementById('bulkBrandForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    if (_bulkGroupIdx === null) return;
    const brand = document.getElementById('bulkBrandInput').value.trim();
    const checkboxes = document.querySelectorAll('.bulk-unit-cb[data-group="' + _bulkGroupIdx + '"]:checked');
    const unitIds = Array.from(checkboxes).map(cb => parseInt(cb.dataset.id));
    if (!unitIds.length) { closeBulkBrandModal(); return; }
    const submitBtn = this.querySelector('[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    try {
        const res = await fetch('/admin/units/bulk-brand', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ unit_ids: unitIds, brand }),
        });
        const data = await res.json();
        if (data.success) {
            unitIds.forEach(function (id) {
                document.querySelectorAll('.unit-brand-cell-' + id).forEach(function (el) {
                    if (data.brand) {
                        el.textContent = data.brand;
                        el.className = 'font-medium text-sm text-orange-800 bg-orange-50 px-2 py-1 rounded unit-brand-cell-' + id;
                    } else {
                        el.textContent = '-';
                        el.className = 'text-gray-400 text-sm unit-brand-cell-' + id;
                    }
                });
                const cb = document.querySelector('.bulk-unit-cb[data-id="' + id + '"]');
                if (cb) cb.checked = false;
            });
            onUnitCheckboxChange(_bulkGroupIdx);
        }
    } catch (err) {
        console.error(err);
    }
    submitBtn.disabled = false;
    submitBtn.textContent = 'Simpan Semua';
    closeBulkBrandModal();
});

document.getElementById('bulkBrandModal').addEventListener('click', function (e) {
    if (e.target === this) closeBulkBrandModal();
});
// ---- End Bulk Brand Logic ----

function openEditBrandModal(entityId, type) {
    _brandEntityId = entityId;
    _brandEntityType = type;
    const prefix = type === 'unit' ? 'unit-brand-cell-' : 'bal-brand-cell-';
    const displayEl = document.querySelector('.' + prefix + entityId);
    const current = displayEl ? (displayEl.textContent.trim() === '-' ? '' : displayEl.textContent.trim()) : '';
    document.getElementById('brandInput').value = current;
    document.getElementById('editBrandModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('brandInput').focus(), 100);
}

function closeEditBrandModal() {
    document.getElementById('editBrandModal').classList.add('hidden');
    _brandEntityId = null;
    _brandEntityType = null;
}

document.getElementById('editBrandForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!_brandEntityId) return;
    const brand = document.getElementById('brandInput').value.trim();
    const url = _brandEntityType === 'unit'
        ? '/admin/units/' + _brandEntityId + '/brand'
        : '/admin/balances/' + _brandEntityId + '/brand';
    const prefix = _brandEntityType === 'unit' ? 'unit-brand-cell-' : 'bal-brand-cell-';
    try {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ brand }),
        });
        const data = await res.json();
        if (data.success) {
            document.querySelectorAll('.' + prefix + _brandEntityId).forEach(function (el) {
                if (data.brand) {
                    el.textContent = data.brand;
                    el.className = 'font-medium text-sm text-orange-800 bg-orange-50 px-2 py-1 rounded ' + prefix + _brandEntityId;
                } else {
                    el.textContent = '-';
                    el.className = 'text-gray-400 text-sm ' + prefix + _brandEntityId;
                }
            });
        }
    } catch (err) {
        console.error(err);
    }
    closeEditBrandModal();
});

document.getElementById('editBrandModal').addEventListener('click', function (e) {
    if (e.target === this) closeEditBrandModal();
});
</script>
@endpush
