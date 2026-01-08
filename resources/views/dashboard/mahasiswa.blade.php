@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa - LabTerpaduFEB')

@section('content')
<!-- Header with Yellow Background -->
<header class="bg-yellow-500 text-white shadow-md">
    <div class="container mx-auto px-3 sm:px-4 py-3 sm:py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 sm:space-x-3">
                <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-12 sm:h-16 w-auto bg-white rounded-lg p-1">
                <div>
                    <p class="text-xs sm:text-sm text-yellow-100 font-bold">Dashboard Mahasiswa</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 sm:space-x-4">
                <span class="text-xs sm:text-sm hidden sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 sm:px-4 py-2 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition text-xs sm:text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<div class="min-h-screen bg-gray-50 py-3 sm:py-4 md:py-8">
    <div class="container mx-auto px-3 sm:px-4">
        <!-- Welcome Card -->
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6 mb-4 md:mb-8">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="text-sm sm:text-base text-gray-600">Anda login sebagai <span class="font-semibold text-yellow-600">Mahasiswa</span></p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-6 mb-4 md:mb-8">
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

            <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 md:p-6 col-span-2 sm:col-span-1">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="mb-2 sm:mb-0">
                        <p class="text-gray-500 text-xs sm:text-sm mb-1">Terpakai</p>
                        <p class="text-2xl sm:text-3xl font-bold text-red-600">{{ $labs->where('status', 'occupied')->count() }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Labs List -->
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Daftar Ruangan Laboratorium</h3>
            
            @if($labs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Kode</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Nama Ruangan</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Lokasi</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Kapasitas</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labs as $lab)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-medium text-gray-800">{{ $lab->code }}</td>
                            <td class="py-3 px-4 text-gray-700">{{ $lab->name }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $lab->location }}</td>
                            <td class="py-3 px-4 text-gray-600">{{ $lab->capacity }} orang</td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    {{ $lab->status === 'available' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $lab->status === 'occupied' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $lab->status === 'maintenance' ? 'bg-gray-100 text-gray-700' : '' }}
                                ">
                                    {{ $lab->status === 'available' ? 'Tersedia' : '' }}
                                    {{ $lab->status === 'occupied' ? 'Terpakai' : '' }}
                                    {{ $lab->status === 'maintenance' ? 'Maintenance' : '' }}
                                </span>
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
