@extends('layouts.admin')

@section('title', 'Detail Peminjaman Aset - Lab Digital FEB UNDIP')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <!-- Header with Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" 
           class="inline-flex items-center text-gray-600 hover:text-gray-900 font-medium mb-4 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Detail Peminjaman Aset #{{ $borrowing->id }}</h1>
                <p class="text-gray-600 mt-1">Informasi lengkap peminjaman aset laboratorium</p>
            </div>
            
            @if($borrowing->status === 'pending')
                <span class="px-4 py-2 bg-yellow-500 text-white text-sm font-bold rounded-lg shadow">
                    ⏳ Menunggu Persetujuan
                </span>
            @elseif($borrowing->status === 'approved')
                <span class="px-4 py-2 bg-green-500 text-white text-sm font-bold rounded-lg shadow">
                    ✅ Disetujui
                </span>
            @elseif($borrowing->status === 'borrowed')
                <span class="px-4 py-2 bg-blue-500 text-white text-sm font-bold rounded-lg shadow">
                    📦 Sedang Dipinjam
                </span>
            @elseif($borrowing->status === 'returned')
                <span class="px-4 py-2 bg-gray-500 text-white text-sm font-bold rounded-lg shadow">
                    ✔️ Dikembalikan
                </span>
            @elseif($borrowing->status === 'rejected')
                <span class="px-4 py-2 bg-red-500 text-white text-sm font-bold rounded-lg shadow">
                    ❌ Ditolak
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Borrower Information -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Informasi Peminjam
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Nama Lengkap</label>
                        <p class="font-semibold text-gray-800">{{ $borrowing->borrower_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Tipe</label>
                        <p class="font-semibold text-gray-800">{{ ucfirst($borrowing->borrower_type) }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">NIM/NIP/NIK</label>
                        <p class="font-semibold text-gray-800">{{ $borrowing->borrower_id_number }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">No Telepon</label>
                        <p class="font-semibold text-gray-800">{{ $borrowing->phone_number }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm text-gray-600">Email</label>
                        <p class="font-semibold text-gray-800">{{ $borrowing->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Borrowing Details -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Detail Peminjaman
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Laboratorium</label>
                        <p class="font-semibold text-gray-800">{{ $borrowing->lab->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Tanggal Pinjam</label>
                        <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($borrowing->borrow_date)->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Tanggal Kembali</label>
                        <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($borrowing->return_date)->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Durasi Peminjaman</label>
                        <p class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($borrowing->borrow_date)->diffInDays(\Carbon\Carbon::parse($borrowing->return_date)) + 1 }} hari
                        </p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm text-gray-600">Tujuan Peminjaman</label>
                        <p class="font-semibold text-gray-800">{{ $borrowing->purpose }}</p>
                    </div>
                </div>
            </div>

            <!-- Borrowed Items -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    Aset yang Dipinjam
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Aset</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($borrowing->borrowedItems as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $item->item->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800 text-center font-semibold">{{ $item->quantity }}x</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $item->notes ?: '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($borrowing->admin_notes)
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-6">
                <h3 class="font-bold text-blue-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Catatan Admin
                </h3>
                <p class="text-blue-800">{{ $borrowing->admin_notes }}</p>
            </div>
            @endif

            @if($borrowing->rejection_reason)
            <div class="bg-red-50 rounded-xl border border-red-200 p-6">
                <h3 class="font-bold text-red-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    Alasan Ditolak
                </h3>
                <p class="text-red-800">{{ $borrowing->rejection_reason }}</p>
            </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi</h2>
                
                @if($borrowing->status === 'pending')
                    <div class="space-y-3">
                        <button onclick="approveAssetBorrowing({{ $borrowing->id }})"
                                class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold shadow-lg transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui
                        </button>
                        <button onclick="rejectAssetBorrowing({{ $borrowing->id }})"
                                class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold shadow-lg transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak
                        </button>
                    </div>
                @elseif($borrowing->status === 'approved')
                    <button onclick="handoutAssetBorrowing({{ $borrowing->id }})"
                            class="w-full px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold shadow-lg transition-all flex items-center justify-center">
                        📤 Serahkan Aset
                    </button>
                @elseif($borrowing->status === 'borrowed')
                    <button onclick="receiveAssetBorrowing({{ $borrowing->id }})"
                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold shadow-lg transition-all flex items-center justify-center">
                        📥 Terima Kembali
                    </button>
                @else
                    <p class="text-gray-500 text-center text-sm">Tidak ada aksi yang tersedia</p>
                @endif

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-600 mb-3">Timeline</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Diajukan</p>
                                <p class="text-xs text-gray-600">{{ $borrowing->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</p>
                            </div>
                        </div>
                        
                        @if($borrowing->approved_at)
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Disetujui</p>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($borrowing->approved_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($borrowing->rejected_at)
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Ditolak</p>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($borrowing->rejected_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($borrowing->handed_out_at)
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Diserahkan</p>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($borrowing->handed_out_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($borrowing->returned_at)
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-gray-500 rounded-full mt-1.5 mr-3"></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Dikembalikan</p>
                                <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($borrowing->returned_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Handout Asset Modal -->
<div id="handoutAssetModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeHandoutModal()">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    📤 Serahkan Aset
                </h3>
                <button type="button" onclick="closeHandoutModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="handoutAssetForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="borrowConditionNotes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Kondisi Barang (Opsional)
                    </label>
                    <textarea 
                        id="borrowConditionNotes" 
                        name="borrow_condition_notes" 
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="Misal: Semua barang dalam kondisi baik dan lengkap"
                    ></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="closeHandoutModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-md hover:from-purple-700 hover:to-indigo-700"
                    >
                        Konfirmasi Serahkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receive Asset Modal -->
<div id="receiveAssetModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeReceiveModal()">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    📥 Terima Kembali Aset
                </h3>
                <button type="button" onclick="closeReceiveModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="receiveAssetForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="returnConditionNotes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Kondisi Pengembalian (Opsional)
                    </label>
                    <textarea 
                        id="returnConditionNotes" 
                        name="return_condition_notes" 
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Misal: Semua barang dikembalikan dengan baik"
                    ></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="isDamagedCheck"
                            name="is_damaged"
                            value="1"
                            onchange="toggleDamageDescription()"
                            class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-700">Ada barang yang rusak/hilang</span>
                    </label>
                </div>

                <div id="damageDescriptionField" class="mb-4 hidden">
                    <label for="damageDescription" class="block text-sm font-medium text-red-700 mb-2">
                        Deskripsi Kerusakan/Kehilangan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="damageDescription" 
                        name="damage_description" 
                        rows="3"
                        class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Jelaskan kondisi barang yang rusak/hilang secara detail"
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="closeReceiveModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-md hover:from-green-700 hover:to-emerald-700"
                    >
                        Konfirmasi Terima
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Asset Borrowing Modal -->
<div id="rejectAssetModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white p-6 rounded-t-2xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 backdrop-blur-sm p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Tolak Peminjaman</h3>
                    <p class="text-sm text-red-100">Berikan alasan penolakan</p>
                </div>
            </div>
            <button onclick="closeRejectModal()" class="text-white/80 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="rejectAssetForm" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="rejection_reason" 
                        id="rejectionReasonInput"
                        rows="4" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all" 
                        placeholder="Jelaskan alasan penolakan peminjaman ini..."
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Alasan akan dikirimkan kepada peminjam
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3 pt-2">
                    <button 
                        type="button" 
                        onclick="closeRejectModal()" 
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-semibold transition-all"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak Peminjaman
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Reuse same functions from dashboard
    window.approveAssetBorrowing = function(id) {
        if (!confirm('✅ Setujui peminjaman aset ini?')) {
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/asset-borrowings/${id}/approve`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    };

    window.rejectAssetBorrowing = function(id) {
        // Open reject modal
        document.getElementById('rejectAssetModal').classList.remove('hidden');
        document.getElementById('rejectAssetModal').classList.add('flex');
        document.getElementById('rejectAssetForm').action = `/admin/asset-borrowings/${id}/reject`;
        document.getElementById('rejectionReasonInput').value = '';
        document.getElementById('rejectionReasonInput').focus();
    };

    // Close reject modal
    function closeRejectModal() {
        document.getElementById('rejectAssetModal').classList.add('hidden');
        document.getElementById('rejectAssetModal').classList.remove('flex');
        document.getElementById('rejectAssetForm').reset();
    }

    window.handoutAssetBorrowing = function(id) {
        // Open handout modal
        document.getElementById('handoutAssetModal').classList.remove('hidden');
        document.getElementById('handoutAssetModal').classList.add('flex');
        document.getElementById('handoutAssetForm').action = `/admin/asset-borrowings/${id}/handout`;
    };

    window.receiveAssetBorrowing = function(id) {
        // Open receive modal
        document.getElementById('receiveAssetModal').classList.remove('hidden');
        document.getElementById('receiveAssetModal').classList.add('flex');
        document.getElementById('receiveAssetForm').action = `/admin/asset-borrowings/${id}/receive`;
    };
    
    // Close modals
    function closeHandoutModal() {
        document.getElementById('handoutAssetModal').classList.add('hidden');
        document.getElementById('handoutAssetModal').classList.remove('flex');
        document.getElementById('handoutAssetForm').reset();
    }

    function closeReceiveModal() {
        document.getElementById('receiveAssetModal').classList.add('hidden');
        document.getElementById('receiveAssetModal').classList.remove('flex');
        document.getElementById('receiveAssetForm').reset();
        document.getElementById('isDamagedCheck').checked = false;
        toggleDamageDescription();
    }

    function toggleDamageDescription() {
        const checkbox = document.getElementById('isDamagedCheck');
        const field = document.getElementById('damageDescriptionField');
        const textarea = document.getElementById('damageDescription');
        
        if (checkbox.checked) {
            field.classList.remove('hidden');
            textarea.required = true;
        } else {
            field.classList.add('hidden');
            textarea.required = false;
            textarea.value = '';
        }
    }

    // Validate receive form
    document.getElementById('receiveAssetForm')?.addEventListener('submit', function(e) {
        const isDamaged = document.getElementById('isDamagedCheck').checked;
        const damageDesc = document.getElementById('damageDescription').value.trim();
        
        if (isDamaged && !damageDesc) {
            e.preventDefault();
            alert('Deskripsi kerusakan/kehilangan harus diisi!');
            document.getElementById('damageDescription').focus();
        }
    });
</script>
@endpush
@endsection
