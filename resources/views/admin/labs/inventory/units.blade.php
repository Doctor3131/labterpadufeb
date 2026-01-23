@extends('layouts.admin')

@section('title', 'Unit ' . $item->name . ' - ' . $lab->name)

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
        <p class="text-sm text-gray-600">{{ $lab->name }} ({{ $lab->code }}) • {{ $units->total() }} unit</p>
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
            <div class="{{ $condition->colorClass() }} rounded-xl p-4 border">
                <div class="text-2xl font-bold">{{ $conditionCounts[$condition->value] ?? 0 }}</div>
                <div class="text-sm font-medium">{{ $condition->label() }}</div>
            </div>
        @endforeach
    </div>

    <!-- Units Table with Bulk Actions -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden" x-data="bulkActions()">
        <!-- Bulk Action Bar -->
        <div x-show="selectedCount > 0" class="bg-blue-50 border-b border-blue-200 px-6 py-3 flex items-center justify-between">
            <span class="text-sm font-medium text-blue-800">
                <span x-text="selectedCount"></span> unit dipilih
            </span>
            <form action="{{ route('admin.inventory.bulk-condition') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="unit_ids[]" :value="id">
                </template>
                <select name="condition" required class="px-3 py-1.5 border border-blue-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Ubah ke...</option>
                    @foreach($conditions as $condition)
                        <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                    @endforeach
                </select>
                <input type="text" name="notes" placeholder="Catatan (opsional)" class="px-3 py-1.5 border border-blue-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition-all">
                    Update Kondisi
                </button>
            </form>
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
                    <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 text-blue-600 rounded">
                        </th>
                        <th class="px-6 py-4 font-semibold">Asset Tag</th>
                        <th class="px-6 py-4 font-semibold">Subtype</th>
                        <th class="px-6 py-4 font-semibold">Batch</th>
                        <th class="px-6 py-4 font-semibold">Kondisi</th>
                        <th class="px-6 py-4 font-semibold text-center">Available</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($units as $unit)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="{{ $unit->id }}" @change="toggleSelection({{ $unit->id }})" class="w-4 h-4 text-blue-600 rounded">
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-medium text-gray-900">{{ $unit->asset_tag }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($unit->subtype)
                                    <span class="text-xs font-medium px-2 py-0.5 rounded bg-purple-100 text-purple-800">{{ $unit->subtype }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $unit->batch->proc_source_code }}.{{ $unit->batch->arrival_mmyy }}
                                <span class="text-xs text-gray-400">({{ $unit->batch->arrival_formatted }})</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="{{ $unit->condition->colorClass() }} text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    {{ $unit->condition->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($unit->is_available)
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
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
                }
            }
        }
    </script>
    @endpush
@endsection
