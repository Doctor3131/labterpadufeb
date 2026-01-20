@extends('layouts.admin')

@section('title', 'Tambah Alat - Lab Digital FEB UNDIP')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Alat Baru</h1>
            <p class="text-sm text-gray-600">Masukkan detail alat laboratorium baru</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 md:p-8">
            <form action="{{ route('admin.inventories.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ID Barang -->
                    <div>
                        <label for="item_code" class="block text-sm font-medium text-gray-700 mb-2">ID Barang / Kode Aset</label>
                        <input type="text" name="item_code" id="item_code" value="{{ old('item_code') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('item_code') border-red-500 @enderror"
                            placeholder="Contoh: LAB-001-PC">
                        @error('item_code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Barang -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('name') border-red-500 @enderror"
                            placeholder="Contoh: PC All-in-One HP">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('quantity') border-red-500 @enderror"
                            placeholder="0">
                        @error('quantity')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kondisi -->
                    <div>
                        <label for="condition" class="block text-sm font-medium text-gray-700 mb-2">Kondisi</label>
                        <select name="condition" id="condition" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('condition') border-red-500 @enderror">
                            <option value="">Pilih Kondisi</option>
                            <option value="Baik" {{ old('condition') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ old('condition') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Perbaikan" {{ old('condition') == 'Perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                        </select>
                        @error('condition')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Harga -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Harga Satuan (Rp)</label>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" required min="0" step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('price') border-red-500 @enderror"
                            placeholder="0">
                        @error('price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber -->
                    <div>
                        <label for="source" class="block text-sm font-medium text-gray-700 mb-2">Sumber Pengadaan</label>
                        <input type="text" name="source" id="source" value="{{ old('source') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('source') border-red-500 @enderror"
                            placeholder="Contoh: APBN 2024, Hibah, dll">
                        @error('source')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi (Full Width) -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi / Spesifikasi</label>
                        <textarea name="description" id="description" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('description') border-red-500 @enderror"
                            placeholder="Spesifikasi teknis atau catatan tambahan...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('admin.inventories.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
