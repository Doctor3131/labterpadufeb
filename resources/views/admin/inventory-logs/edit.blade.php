@extends('layouts.admin')

@section('title', 'Edit Arus Barang Global - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.inventory.logs.index') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Arus Barang Global
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Arus Barang</h1>
        <p class="text-sm text-gray-600">Edit informasi barang masuk atau keluar secara global.</p>
    </div>

    <!-- Errors -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.inventory.logs.update', $log) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal *</label>
                    <input type="date" name="date" value="{{ old('date', $log->date->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                </div>
                
                <!-- Flow -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status (Masuk / Keluar) *</label>
                    <select name="flow" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 bg-white">
                        <option value="IN" {{ old('flow', $log->flow) === 'IN' ? 'selected' : '' }}>Barang Masuk</option>
                        <option value="OUT" {{ old('flow', $log->flow) === 'OUT' ? 'selected' : '' }}>Barang Keluar</option>
                    </select>
                </div>
                
                <!-- Recipient / PIC -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penerima / Penyerah *</label>
                    <input type="text" name="recipient" value="{{ old('recipient', $log->recipient) }}" required placeholder="Contoh: Pak Ilham, UPK, Dosen"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                </div>

                <!-- Items Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Barang & Jumlah *</label>
                    <textarea name="items" rows="4" required placeholder="Contoh:&#10;HDMI 50M (5 unit)&#10;Kabel Power AIO (2 unit)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">{{ old('items', $log->items) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Sertakan nama barang dan jumlahnya sedetail mungkin.</p>
                </div>

                <!-- Proof File -->
                <div class="md:col-span-2 p-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 text-center">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Foto Barang (Opsional)</label>
                    
                    @if($log->proof_file)
                        <div class="mb-4">
                            <span class="text-sm text-green-600 block mb-2 font-medium">Bukti foto saat ini:</span>
                            <a href="{{ route('admin.secure-file', ['path' => $log->proof_file]) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Lihat File
                            </a>
                        </div>
                    @endif

                    <input type="file" name="proof_file" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-yellow-50 file:text-yellow-700
                        hover:file:bg-yellow-100
                        cursor-pointer mx-auto max-w-sm
                    ">
                    <p class="text-xs text-gray-500 mt-2">Upload file baru untuk mengganti yang lama. Format: JPG, PNG (Maks. 2MB)</p>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="{{ route('admin.inventory.logs.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
