@extends('layouts.admin')

@section('title', 'Unit ' . $item->name . ' - ' . $lab->name)

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.labs.inventory', $lab) }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all duration-300 group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Inventaris
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-xl p-6 mb-6 shadow-lg border-t-4 border-gradient-to-r from-amber-600 to-amber-400">
        <div class="flex items-start gap-4">
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-3 rounded-lg shadow-sm">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-amber-900 mb-1">{{ $item->name }}</h1>
                <div class="flex items-center gap-3 text-sm text-amber-800">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $lab->name }}
                    </span>
                    <span class="text-amber-300">•</span>
                    <span>{{ $lab->code }}</span>
                    <span class="text-amber-300">•</span>
                    <span class="font-semibold text-amber-900">{{ $units->total() }} unit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Specifications -->
    <div class="bg-white rounded-xl p-6 mb-6 shadow-lg border-t-4 border-gradient-to-r from-amber-600 to-amber-400">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b-2 border-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-lg font-bold text-amber-900">Spesifikasi Barang</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <!-- Nama Barang -->
            <div class="flex flex-col space-y-1">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Nama Barang</span>
                <span class="text-sm font-medium text-amber-900">{{ $item->name }}</span>
            </div>

            <!-- Kode Tipe Aset -->
            @if($item->assetTypeCode)
            <div class="flex flex-col space-y-1">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Kode Tipe Aset</span>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-mono font-semibold text-amber-900">{{ $item->assetTypeCode->code }}</span>
                    <span class="text-xs text-gray-500">({{ $item->assetTypeCode->name }})</span>
                </div>
            </div>
            @endif

            <!-- Mode Tracking -->
            <div class="flex flex-col space-y-1">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Mode Tracking</span>
                <span class="inline-flex items-center w-fit px-3 py-1 rounded-md text-xs font-medium border
                    {{ $item->tracking_mode->value === 'STRUCTURED_TAG' ? 'bg-purple-50 text-purple-700 border-purple-200' : 
                       ($item->tracking_mode->value === 'SEAT_NUMBER' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-orange-50 text-orange-700 border-orange-200') }}">
                    @if($item->tracking_mode->value === 'STRUCTURED_TAG')
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Structured Tag
                    @elseif($item->tracking_mode->value === 'SEAT_NUMBER')
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Seat Number
                    @else
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Aggregate
                    @endif
                </span>
            </div>

            <!-- Can Be Borrowed -->
            @if($item->assetTypeCode)
            <div class="flex flex-col space-y-1">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Status Peminjaman</span>
                <span class="inline-flex items-center w-fit px-3 py-1 rounded-md text-xs font-medium border
                    {{ $item->assetTypeCode->is_borrowable ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-50 text-gray-700 border-gray-200' }}">
                    @if($item->assetTypeCode->is_borrowable)
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Dapat Dipinjam
                    @else
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tidak Dapat Dipinjam
                    @endif
                </span>
            </div>
            @endif

            <!-- Total Units -->
            <div class="flex flex-col space-y-1">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Total Unit</span>
                <span class="text-lg font-bold text-amber-600">{{ $units->total() }} <span class="text-sm font-normal text-amber-700">unit</span></span>
            </div>

            <!-- Description (Full Width) -->
            @if($item->description)
            <div class="flex flex-col space-y-1 md:col-span-2 lg:col-span-3">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Deskripsi</span>
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

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($conditions as $condition)
            @php
                $count = $conditionCounts[$condition->value] ?? 0;
            @endphp
            <div class="{{ $condition->colorClass() }} rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300">
                <div class="text-3xl font-bold mb-1">{{ $count }}</div>
                <div class="text-sm font-semibold">{{ $condition->label() }}</div>
                @if($count > 0)
                    <div class="text-xs opacity-75 mt-1">{{ number_format(($count / $units->total()) * 100, 1) }}%</div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Units Table with Bulk Actions -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden" x-data="bulkActions()">
        <!-- Bulk Action Bar -->
        <div x-show="selectedCount > 0" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-blue-50 border-b border-blue-200 px-6 py-3 flex items-center justify-between">
            <span class="text-sm font-medium text-blue-900">
                <span x-text="selectedCount"></span> unit dipilih
            </span>
            <div class="flex items-center gap-3">
                <!-- Update Condition Form -->
                <form action="{{ route('admin.inventory.bulk-condition') }}" method="POST" class="flex items-center gap-3">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="unit_ids[]" :value="id">
                    </template>
                    <select name="condition" required class="px-3 py-1.5 border border-blue-300 bg-white rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Ubah kondisi ke...</option>
                        @foreach($conditions as $condition)
                            <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="notes" placeholder="Catatan (opsional)" class="px-3 py-1.5 border border-blue-300 bg-white rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition-colors">
                        Update Kondisi
                    </button>
                </form>
                
                <!-- Bulk Delete Button -->
                <button @click="openBulkDeleteModal()" class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md text-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="grid grid-cols-1 gap-4 md:hidden p-4">
            @forelse($units as $unit)
                <div class="bg-white border rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" :value="{{ $unit->id }}" @change="toggleSelection({{ $unit->id }})" class="mt-1 w-4 h-4 text-blue-600 rounded">
                        <div class="flex-1">
                            <div class="font-mono text-sm font-bold text-gray-800">{{ $unit->asset_tag }}</div>
                            @if($unit->subtype)
                                <span class="text-xs font-medium px-2 py-0.5 rounded bg-purple-100 text-purple-800">{{ $unit->subtype }}</span>
                            @endif
                            <div class="mt-2">
                                <span class="{{ $unit->condition->colorClass() }} text-xs font-bold px-2.5 py-1 rounded-full">
                                    {{ $unit->condition->label() }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                Batch: {{ $unit->batch->arrival_formatted }}
                            </div>
                            @php
                                $notes = $unit->getLatestConditionNotes();
                            @endphp
                            @if($notes)
                                <div class="text-xs text-gray-600 mt-2 bg-gray-50 p-2 rounded">
                                    <span class="font-semibold">Catatan:</span> {{ Str::limit($notes, 80) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <p>Tidak ada unit untuk item ini</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th class="px-6 py-3 w-10">
                            <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 font-semibold">Asset Tag</th>
                        <th class="px-6 py-3 font-semibold">Subtype</th>
                        <th class="px-6 py-3 font-semibold">Batch</th>
                        <th class="px-6 py-3 font-semibold">Kondisi</th>
                        <th class="px-6 py-3 font-semibold">Catatan</th>
                        <th class="px-6 py-3 font-semibold text-center">Available</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($units as $unit)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3">
                                <input type="checkbox" :value="{{ $unit->id }}" @change="toggleSelection({{ $unit->id }})" class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-3">
                                <span class="font-mono text-sm font-medium text-gray-900">{{ $unit->asset_tag }}</span>
                            </td>
                            <td class="px-6 py-3">
                                @if($unit->subtype)
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-md bg-purple-100 text-purple-700 border border-purple-200">{{ $unit->subtype }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-col">
                                    <span class="font-mono text-sm font-medium text-gray-900">{{ $unit->batch->proc_source_code }}.{{ $unit->batch->arrival_mmyy }}</span>
                                    <span class="text-xs text-gray-500">({{ $unit->batch->arrival_formatted }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="{{ $unit->condition->colorClass() }} text-xs font-medium px-2.5 py-1 rounded-md">
                                    {{ $unit->condition->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $notes = $unit->getLatestConditionNotes();
                                @endphp
                                @if($notes)
                                    <span class="text-sm text-gray-700" title="{{ $notes }}">
                                        {{ Str::limit($notes, 50) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($unit->is_available)
                                    <svg class="w-5 h-5 text-green-600 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-600 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada unit untuk item ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($units->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $units->links() }}
            </div>
        @endif
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div id="bulkDeleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4 text-center">Hapus Unit Terpilih</h3>
                <div class="mt-2 px-4 py-3">
                    <p class="text-sm text-gray-500 text-center">
                        Apakah Anda yakin ingin menghapus <strong><span id="deleteCount"></span> unit</strong> yang dipilih?
                        <br><span class="text-red-600">Tindakan ini tidak dapat dibatalkan.</span>
                    </p>
                </div>
                <div class="flex gap-3 px-4 py-3">
                    <button onclick="closeBulkDeleteModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none">
                        Batal
                    </button>
                    <form id="bulkDeleteForm" method="POST" action="{{ route('admin.inventory.bulk-delete') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function bulkActions() {
            return {
                selectedIds: [],
                get selectedCount() {
                    return this.selectedIds.length;
                },
                toggleSelection(id) {
                    const index = this.selectedIds.indexOf(id);
                    if (index === -1) {
                        this.selectedIds.push(id);
                    } else {
                        this.selectedIds.splice(index, 1);
                    }
                },
                toggleAll(event) {
                    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                    if (event.target.checked) {
                        this.selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
                    } else {
                        this.selectedIds = [];
                    }
                    checkboxes.forEach(cb => cb.checked = event.target.checked);
                },
                openBulkDeleteModal() {
                    if (this.selectedCount > 0) {
                        // Update modal content
                        document.getElementById('deleteCount').textContent = this.selectedCount;
                        
                        // Clear and populate form with selected IDs
                        const form = document.getElementById('bulkDeleteForm');
                        form.querySelectorAll('input[name="unit_ids[]"]').forEach(input => input.remove());
                        
                        this.selectedIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'unit_ids[]';
                            input.value = id;
                            form.appendChild(input);
                        });
                        
                        // Show modal
                        document.getElementById('bulkDeleteModal').classList.remove('hidden');
                    }
                }
            }
        }

        function closeBulkDeleteModal() {
            document.getElementById('bulkDeleteModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('bulkDeleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeBulkDeleteModal();
            }
        });
    </script>
    @endpush
@endsection
