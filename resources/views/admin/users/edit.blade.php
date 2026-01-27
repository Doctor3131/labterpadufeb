@extends('layouts.admin')

@section('title', 'Edit User - Lab Digital FEB UNDIP')

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-4 md:p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Edit User</h1>
                    <p class="text-xs md:text-sm text-blue-100">Ubah informasi akun {{ $user->name }}</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm px-4 py-2 rounded-xl text-white font-medium transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden max-w-2xl">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
            <h2 class="font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Data User
            </h2>
        </div>
        
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Name -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Masukkan nama lengkap">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email (Read Only) -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" value="{{ $user->email }}" disabled
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">Email tidak dapat diubah</p>
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                <select name="role" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror"
                    {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin (Aslab)</option>
                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
                @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <p class="mt-1 text-xs text-yellow-600">Tidak dapat mengubah role sendiri</p>
                @endif
                @error('role')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex space-x-3">
                <a href="{{ route('admin.users.index') }}" class="flex-1 px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all text-center">
                    Batal
                </a>
                <button type="submit" class="flex-1 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Info Card -->
    <div class="mt-6 bg-yellow-50 rounded-2xl p-4 max-w-2xl">
        <p class="text-sm text-yellow-800">
            <strong>Catatan:</strong> Untuk mengganti password user ini, gunakan tombol "Reset Password" di halaman daftar user.
        </p>
    </div>
@endsection
