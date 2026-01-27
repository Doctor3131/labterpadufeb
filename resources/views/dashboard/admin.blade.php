@extends('layouts.admin')

@section('title', 'Dashboard Admin - LabDigitalFEB')

@section('content')
<!-- Header with Yellow Background -->


<div class="min-h-screen bg-gray-50 py-3 sm:py-4 md:py-8">
    <div class="container mx-auto px-3 sm:px-4">
        <!-- Welcome Card -->
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-4 md:mb-8">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="text-sm sm:text-base text-gray-600">Anda login sebagai <span class="font-semibold text-yellow-600">Admin</span></p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-4 md:mb-8">
            <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 md:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="mb-2 sm:mb-0">
                        <p class="text-gray-500 text-xs sm:text-sm mb-1">Total Ruangan</p>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ $labs->count() }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 md:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="mb-2 sm:mb-0">
                        <p class="text-gray-500 text-xs sm:text-sm mb-1">Tersedia</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-600">{{ $labs->where('status', 'available')->count() }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 md:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="mb-2 sm:mb-0">
                        <p class="text-gray-500 text-xs sm:text-sm mb-1">Nonaktif</p>
                        <p class="text-2xl sm:text-3xl font-bold text-red-600">{{ $labs->where('status', 'inactive')->count() }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Labs Management -->
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 md:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
                <h3 class="text-lg sm:text-xl font-bold text-gray-800">Manajemen Ruangan Laboratorium</h3>
                <button class="px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition">
                    + Tambah Ruangan
                </button>
            </div>
            
            @if($labs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Nama Ruangan</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Deskripsi</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Kapasitas</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Status</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labs as $lab)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $lab->name }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $lab->description ?: '-' }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $lab->capacity }} orang</td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    {{ $lab->status === 'available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}
                                ">
                                    {{ $lab->status === 'available' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded hover:bg-blue-600 transition">
                                        Edit
                                    </button>
                                    <button class="px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded hover:bg-red-600 transition">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-500 text-center py-8">Belum ada data ruangan laboratorium.</p>
            @endif
        </div>
    </div>
</div>
@endsection
