@extends('layouts.admin')

@section('title', 'Tambah Ruang Lab - Lab Digital FEB UNDIP')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.labs.index') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Kelola Ruang
            </a>
        </div>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Ruang Lab Baru</h1>
            <p class="text-sm text-gray-600">Masukkan detail ruang laboratorium baru</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 md:p-8">
            <form action="{{ route('admin.labs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Lab -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lab <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('name') border-red-500 @enderror"
                            placeholder="Contoh: Lab Digital">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kapasitas -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Kapasitas (Orang) <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" required min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('capacity') border-red-500 @enderror"
                            placeholder="0">
                        @error('capacity')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description (Full Width) -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
                        <textarea name="description" id="description" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition @error('description') border-red-500 @enderror"
                            placeholder="Deskripsi fasilitas atau informasi tambahan tentang lab...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.labs.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
