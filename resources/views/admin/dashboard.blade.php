@extends('layouts.admin')

@section('title', 'Dashboard Admin - Lab Digital FEB UNDIP')

@push('styles')
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .dashboard-card {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }
        .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
        .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
        .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
    </style>
@endpush

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Dashboard Admin</h1>
                    <p class="text-yellow-100">Kelola permintaan layanan Lab Digital FEB UNDIP</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- 3 Service Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Lab Booking Card -->
        <div class="dashboard-card bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-yellow-100 hover:border-yellow-300 transition-all hover:shadow-xl">
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-4">
                <div class="flex items-center justify-between">
                    <div class="bg-white/20 p-3 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span class="text-white/90 text-sm font-medium">Peminjaman</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Lab</h3>
                <p class="text-gray-500 text-sm mb-4">Kelola permintaan peminjaman laboratorium</p>
                
                <div class="flex items-center justify-between mb-4">
                    <span class="text-gray-600 text-sm">Pending:</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold">
                        {{ $labPendingCount }}
                    </span>
                </div>
                
                <a href="{{ route('admin.lab.bookings') }}" class="block w-full text-center px-4 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-xl transition-colors">
                    <span class="flex items-center justify-center">
                        Kelola
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>

        <!-- BPS Data Card -->
        <div class="dashboard-card bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-teal-100 hover:border-teal-300 transition-all hover:shadow-xl">
            <div class="bg-gradient-to-r from-teal-500 to-teal-600 p-4">
                <div class="flex items-center justify-between">
                    <div class="bg-white/20 p-3 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    </div>
                    <span class="text-white/90 text-sm font-medium">Data Request</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">BPS</h3>
                <p class="text-gray-500 text-sm mb-4">Kelola permintaan data BPS</p>
                
                <div class="flex items-center justify-between mb-4">
                    <span class="text-gray-600 text-sm">Menunggu:</span>
                    <span class="px-3 py-1 bg-teal-100 text-teal-800 rounded-full text-sm font-bold">
                        {{ $bpsPendingCount }}
                    </span>
                </div>
                
                <a href="{{ route('admin.bps.requests.index') }}" class="block w-full text-center px-4 py-3 bg-teal-500 hover:bg-teal-600 text-white font-semibold rounded-xl transition-colors">
                    <span class="flex items-center justify-center">
                        Kelola
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>

        <!-- Refinitiv Card -->
        <div class="dashboard-card bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-blue-100 hover:border-blue-300 transition-all hover:shadow-xl">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                <div class="flex items-center justify-between">
                    <div class="bg-white/20 p-3 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <span class="text-white/90 text-sm font-medium">Data Keuangan</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Refinitiv</h3>
                <p class="text-gray-500 text-sm mb-4">Kelola akses data Refinitiv</p>
                
                <div class="flex items-center justify-between mb-4">
                    <span class="text-gray-600 text-sm">Menunggu:</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                        {{ $refinitivPendingCount }}
                    </span>
                </div>
                
                <a href="{{ route('admin.refinitiv.index') }}" class="block w-full text-center px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-colors">
                    <span class="flex items-center justify-center">
                        Kelola
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="mt-8 bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Status Ringkasan
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-500 text-sm border-b">
                        <th class="pb-3 font-semibold">Layanan</th>
                        <th class="pb-3 font-semibold text-center">Status Awal</th>
                        <th class="pb-3 font-semibold text-center">Status Akhir</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="border-b border-gray-100">
                        <td class="py-3 font-medium text-gray-800">Lab</td>
                        <td class="py-3 text-center">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Pending</span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium mr-1">Disetujui</span>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Ditolak</span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 font-medium text-gray-800">BPS</td>
                        <td class="py-3 text-center">
                            <span class="px-2 py-1 bg-teal-100 text-teal-800 rounded-full text-xs font-medium">Menunggu</span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Selesai</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 font-medium text-gray-800">Refinitiv</td>
                        <td class="py-3 text-center">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">Menunggu</span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium mr-1">Datang</span>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Tidak Datang</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
