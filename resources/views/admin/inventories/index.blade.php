@extends('layouts.admin')

@section('title', 'Inventaris Alat - Lab Digital FEB UNDIP')

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
            <h1 class="text-2xl font-bold text-gray-800">Inventaris Alat Laboratorium</h1>
            <p class="text-sm text-gray-600">Kelola data peralatan dan inventaris laboratorium</p>
        </div>
        <a href="{{ route('admin.inventories.create') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Barang
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-r-lg shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Content Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        
        <!-- Mobile Card View -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:hidden p-4">
            @forelse($inventories as $item)
                <div class="bg-white border rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $item->item_code }}</span>
                            <h3 class="font-bold text-gray-800 text-lg mt-1">{{ $item->name }}</h3>
                        </div>
                        @php
                            $conditionColor = match($item->condition) {
                                'Baik' => 'bg-green-100 text-green-800',
                                'Rusak' => 'bg-red-100 text-red-800',
                                'Perbaikan' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="{{ $conditionColor }} text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap">
                            {{ $item->condition }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex justify-between border-b border-gray-50 pb-1">
                            <span>Jumlah:</span>
                            <span class="font-semibold text-gray-900">{{ $item->quantity }} unit</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-1">
                            <span>Harga:</span>
                            <span class="font-semibold text-gray-900">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-1">
                            <span>Sumber:</span>
                            <span class="text-gray-900">{{ $item->source }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <a href="{{ route('admin.inventories.edit', $item->id) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        <form action="{{ route('admin.inventories.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-1 sm:col-span-2 text-center py-12 text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-lg font-medium">Belum ada data inventaris</p>
                        <p class="text-sm">Silakan tambahkan data alat baru</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider border-b border-gray-200">
                        <th class="px-6 py-4 font-semibold">ID Barang</th>
                        <th class="px-6 py-4 font-semibold">Nama Barang</th>
                        <th class="px-6 py-4 font-semibold text-center">Jumlah</th>
                        <th class="px-6 py-4 font-semibold">Kondisi</th>
                        <th class="px-6 py-4 font-semibold">Harga</th>
                        <th class="px-6 py-4 font-semibold">Sumber</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inventories as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->item_code }}</td>
                            <td class="px-6 py-4 text-gray-800">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $item->quantity }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $conditionColor = match($item->condition) {
                                        'Baik' => 'bg-green-100 text-green-800',
                                        'Rusak' => 'bg-red-100 text-red-800',
                                        'Perbaikan' => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="{{ $conditionColor }} text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    {{ $item->condition }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $item->source }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.inventories.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 p-1 rounded-md hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.inventories.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 p-1 rounded-md hover:bg-red-50 transition-colors" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <p class="text-lg font-medium">Belum ada data inventaris</p>
                                    <p class="text-sm">Silakan tambahkan data alat baru</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($inventories->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>
@endsection
