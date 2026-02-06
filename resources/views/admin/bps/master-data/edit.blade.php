@extends('layouts.admin')

@section('title', 'Edit Kategori BPS - Lab Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.bps.master-data.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6 max-w-2xl">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Edit Kategori: {{ $masterData->name }}</h1>

        <form action="{{ route('admin.bps.master-data.update', $masterData) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">Kode <span class="text-red-500">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code', $masterData->code) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 uppercase">
                    @error('code')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $masterData->name) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $masterData->description) }}</textarea>
                    @error('description')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="has_sub_data" value="1" {{ old('has_sub_data', $masterData->has_sub_data) ? 'checked' : '' }}
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm font-medium text-gray-700">Punya Sub Data</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Jika tidak dicentang, user langsung input variabel tanpa pilih sub data</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $masterData->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm font-medium text-gray-700">Aktif</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-6 py-3 bg-indigo-500 text-white font-semibold rounded-lg hover:bg-indigo-600 transition-colors">
                    Perbarui
                </button>
                <a href="{{ route('admin.bps.master-data.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
