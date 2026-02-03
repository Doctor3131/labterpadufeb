@extends('layouts.admin')

@section('title', 'Catatan Pembukuan - ' . $lab->name)

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
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="bg-blue-50 p-3 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-semibold text-gray-900 mb-1">Catatan Pembukuan Inventaris</h1>
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $lab->name }}
                    </span>
                    <span class="text-gray-400">•</span>
                    <span class="font-medium text-gray-900">{{ $transactions->total() }} transaksi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 shadow-sm">
        <form method="GET" action="{{ route('admin.labs.inventory.ledger', $lab) }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Transaksi</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Tipe</option>
                    <option value="RECEIPT" {{ request('type') == 'RECEIPT' ? 'selected' : '' }}>Penerimaan Barang</option>
                    <option value="CONDITION_CHANGE" {{ request('type') == 'CONDITION_CHANGE' ? 'selected' : '' }}>Perubahan Kondisi</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.labs.inventory.ledger', $lab) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions Timeline -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        @if($transactions->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($transactions as $transaction)
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                @if($transaction->type->value === 'RECEIPT')
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                @elseif($transaction->type->value === 'CONDITION_CHANGE')
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </div>
                                @elseif($transaction->type->value === 'TRANSFER')
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4 mb-2">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">
                                            @if($transaction->type->value === 'RECEIPT')
                                                Penerimaan Barang
                                            @elseif($transaction->type->value === 'CONDITION_CHANGE')
                                                Perubahan Kondisi
                                            @elseif($transaction->type->value === 'TRANSFER')
                                                Transfer Barang
                                            @else
                                                Penyesuaian Inventaris
                                            @endif
                                        </h3>
                                        <div class="flex items-center gap-2 mt-1 text-sm text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                                            @if($transaction->user)
                                                <span class="text-gray-400">•</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                <span>{{ $transaction->user->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                        {{ $transaction->type->value === 'RECEIPT' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $transaction->type->value === 'CONDITION_CHANGE' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $transaction->type->value === 'TRANSFER' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $transaction->type->value === 'ADJUSTMENT' ? 'bg-orange-100 text-orange-800' : '' }}">
                                        #{{ $transaction->id }}
                                    </span>
                                </div>

                                @if($transaction->notes)
                                    <p class="text-sm text-gray-700 mb-3 bg-gray-50 p-3 rounded-lg">
                                        {{ $transaction->notes }}
                                    </p>
                                @endif

                                <!-- Transaction Lines -->
                                @if($transaction->lines->count() > 0)
                                    <div class="mt-3 space-y-2">
                                        @foreach($transaction->lines as $line)
                                            <div class="flex items-center gap-3 text-sm bg-white border border-gray-200 rounded-lg p-3">
                                                @if($line->asset_unit_id)
                                                    <!-- For Unit-based transactions -->
                                                    <span class="font-mono font-semibold text-gray-900">{{ $line->assetUnit->asset_tag }}</span>
                                                    @if($line->from_condition && $line->to_condition)
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                        </svg>
                                                        <span class="{{ $line->from_condition->colorClass() }} px-2 py-0.5 rounded text-xs font-medium">
                                                            {{ $line->from_condition->label() }}
                                                        </span>
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                        </svg>
                                                        <span class="{{ $line->to_condition->colorClass() }} px-2 py-0.5 rounded text-xs font-medium">
                                                            {{ $line->to_condition->label() }}
                                                        </span>
                                                    @endif
                                                @elseif($line->inventory_balance_id)
                                                    <!-- For Aggregate transactions -->
                                                    <span class="font-medium text-gray-900">{{ $line->inventoryBalance->batch->item->name }}</span>
                                                    @if($line->quantity)
                                                        <span class="text-gray-600">{{ $line->quantity }} unit</span>
                                                    @endif
                                                    @if($line->from_condition && $line->to_condition)
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                        </svg>
                                                        <span class="{{ $line->from_condition->colorClass() }} px-2 py-0.5 rounded text-xs font-medium">
                                                            {{ $line->from_condition->label() }}
                                                        </span>
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                        </svg>
                                                        <span class="{{ $line->to_condition->colorClass() }} px-2 py-0.5 rounded text-xs font-medium">
                                                            {{ $line->to_condition->label() }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500 text-lg">Belum ada catatan transaksi</p>
                <p class="text-gray-400 text-sm mt-1">Transaksi akan tercatat secara otomatis saat Anda mengelola inventaris</p>
            </div>
        @endif
    </div>
@endsection
