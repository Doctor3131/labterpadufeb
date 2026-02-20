@extends('layouts.admin')

@section('title', 'Pengaturan Akun - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-2xl p-4 md:p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Pengaturan Akun</h1>
                    <p class="text-xs md:text-sm text-gray-300">Kelola informasi profil dan keamanan akun Anda</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-2 md:p-3 rounded-xl">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-green-700 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Info Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                <h2 class="font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informasi Profil
                </h2>
            </div>
            
            <form method="POST" action="{{ route('profile.update') }}" class="p-6">
                @csrf
                @method('PUT')
                
                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
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

                <!-- Role (Read Only) -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                    <div class="px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50">
                        @if($user->role === 'super_admin')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Super Admin
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Admin
                            </span>
                        @endif
                    </div>
                </div>

                <button type="submit" class="w-full px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-yellow-100 border-b border-yellow-200">
                <h2 class="font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Ubah Password
                </h2>
            </div>
            
            <form method="POST" action="{{ route('profile.password') }}" class="p-6">
                @csrf
                @method('PUT')
                
                <!-- Current Password -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password Lama</label>
                    <input type="password" name="current_password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('current_password') border-red-500 @enderror"
                        placeholder="Masukkan password lama">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('password') border-red-500 @enderror"
                        placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="Ketik ulang password baru">
                </div>

                <button type="submit" class="w-full px-6 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Ubah Password
                </button>
            </form>
        </div>
    </div>

    <!-- Account Info -->
    <div class="mt-6 bg-white rounded-2xl shadow-lg p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Informasi Akun</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-500 mb-1">Akun Dibuat</p>
                <p class="font-medium text-gray-800">{{ $user->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-500 mb-1">Terakhir Diupdate</p>
                <p class="font-medium text-gray-800">{{ $user->updated_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-500 mb-1">ID Akun</p>
                <p class="font-medium text-gray-800">#{{ $user->id }}</p>
            </div>
        </div>
    </div>
@endsection
