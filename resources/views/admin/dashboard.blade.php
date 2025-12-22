<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - Lab Terpadu FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 pb-32">
    <!-- Modern Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 border-b-4 border-yellow-500">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xl font-bold text-gray-800">Lab Terpadu</span>
                            <span class="text-lg text-gray-600">FEB UNDIP</span>
                        </div>
                        <p class="text-xs text-gray-500">Admin Dashboard</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('landing') }}" class="px-4 py-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Lihat Jadwal
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            User Baru
                        </a>
                    @endif
                    <div class="flex items-center space-x-2 px-3 py-2 bg-gray-100 rounded-lg">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 max-w-7xl">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Manajemen Peminjaman Lab</h1>
            <p class="text-gray-600">Kelola dan review semua permintaan peminjaman laboratorium</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-r-lg shadow-sm animate-pulse">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Professional Tabs -->
        <div class="bg-white rounded-xl shadow-lg mb-6 overflow-hidden">
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <nav class="flex -mb-px px-4" aria-label="Tabs">
                    <button onclick="showTab('pending')" class="tab-button flex items-center px-6 py-4 text-sm font-semibold border-b-3 border-yellow-500 text-yellow-700" data-tab="pending">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Menunggu Persetujuan
                        <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">{{ $pendingBookings->count() }}</span>
                    </button>
                    <button onclick="showTab('approved')" class="tab-button flex items-center px-6 py-4 text-sm font-semibold border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="approved">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Disetujui
                        <span class="ml-2 px-2 py-0.5 bg-gray-200 text-gray-700 rounded-full text-xs font-bold">{{ $approvedBookings->count() }}</span>
                    </button>
                    <button onclick="showTab('rejected')" class="tab-button flex items-center px-6 py-4 text-sm font-semibold border-b-3 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="rejected">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ditolak
                        <span class="ml-2 px-2 py-0.5 bg-gray-200 text-gray-700 rounded-full text-xs font-bold">{{ $rejectedBookings->count() }}</span>
                    </button>
                </nav>
            </div>

        <!-- Pending Bookings -->
        <div id="pending-tab" class="tab-content p-6">
            @forelse($pendingBookings as $booking)
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl mb-4 p-6 border-l-4 border-yellow-500 transition-all hover:scale-[1.01]">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-3">
                                <span class="px-3 py-1.5 bg-gradient-to-r from-yellow-400 to-yellow-500 text-white text-xs font-bold rounded-lg shadow-sm">
                                    {{ $booking->lab->name }}
                                </span>
                                <span class="px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg">
                                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}
                                </span>
                                <span class="px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg">
                                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $booking->start_time }} - {{ $booking->end_time }}
                                </span>
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                                    {{ $booking->created_at->diffForHumans() }}
                                </span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-800 mb-2">
                                @if($booking->booking_type === 'non_perkuliahan')
                                    {{ $booking->nama_kegiatan }}
                                @else
                                    {{ $booking->mata_kuliah }}
                                @endif
                            </h3>
                            
                            <div class="grid grid-cols-2 gap-3 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    <strong class="mr-1">Peminjam:</strong> {{ $booking->nama_peminjam }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                    </svg>
                                    <strong class="mr-1">Prodi:</strong> {{ $booking->program_studi }}
                                </div>
                                @if($booking->booking_type !== 'non_perkuliahan')
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                        </svg>
                                        <strong class="mr-1">Dosen:</strong> {{ $booking->dosen_pengampu }}
                                    </div>
                                @endif
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                    </svg>
                                    <strong class="mr-1">Peserta:</strong> {{ $booking->jumlah_peserta }} orang
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col space-y-2 ml-6">
                            <a href="{{ route('admin.booking.show', $booking->id) }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </a>
                            <button onclick="approveBooking({{ $booking->id }})" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Setujui
                            </button>
                            <button onclick="showRejectModal({{ $booking->id }})" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl shadow-inner p-16 text-center">
                    <div class="inline-block p-6 bg-gray-100 rounded-full mb-4">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Peminjaman Pending</h3>
                    <p class="text-gray-500">Semua permintaan peminjaman sudah diproses</p>
                </div>
            @endforelse
        </div>

        <!-- Approved Bookings -->
        <div id="approved-tab" class="tab-content hidden p-6">
            @forelse($approvedBookings as $booking)
                <div class="bg-white rounded-xl shadow-md mb-4 p-6 border-l-4 border-green-500 hover:shadow-lg transition-all">
                    <div class="flex items-center space-x-3 mb-3">
                        <span class="px-3 py-1.5 bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold rounded-lg shadow-sm flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Disetujui
                        </span>
                        <span class="px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-lg">{{ $booking->lab->name }}</span>
                        <span class="text-gray-600 text-sm font-medium">
                            {{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('D MMM YYYY') }} • {{ $booking->start_time }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">
                        @if($booking->booking_type === 'non_perkuliahan')
                            {{ $booking->nama_kegiatan }}
                        @else
                            {{ $booking->mata_kuliah }}
                        @endif
                    </h3>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            <strong>{{ $booking->nama_peminjam }}</strong> • {{ $booking->jumlah_peserta }} orang
                        </p>
                        @if($booking->approvedBy)
                            <span class="text-xs text-gray-500">Approved by {{ $booking->approvedBy->name }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl shadow-inner p-16 text-center">
                    <div class="inline-block p-6 bg-green-100 rounded-full mb-4">
                        <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Peminjaman Disetujui</h3>
                    <p class="text-gray-500">Approve peminjaman untuk melihatnya di sini</p>
                </div>
            @endforelse
        </div>

        <!-- Rejected Bookings -->
        <div id="rejected-tab" class="tab-content hidden p-6">
            @forelse($rejectedBookings as $booking)
                <div class="bg-white rounded-xl shadow-md mb-4 p-6 border-l-4 border-red-500 hover:shadow-lg transition-all">
                    <div class="flex items-center space-x-3 mb-3">
                        <span class="px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold rounded-lg shadow-sm flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Ditolak
                        </span>
                        <span class="px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-lg">{{ $booking->lab->name }}</span>
                        <span class="text-gray-600 text-sm">
                            {{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('D MMM YYYY') }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">
                        @if($booking->booking_type === 'non_perkuliahan')
                            {{ $booking->nama_kegiatan }}
                        @else
                            {{ $booking->mata_kuliah }}
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600 mb-3"><strong>{{ $booking->nama_peminjam }}</strong></p>
                    @if($booking->rejection_reason)
                        <div class="mt-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-xs font-semibold text-red-600 mb-1">Alasan Penolakan:</p>
                            <p class="text-sm text-red-700">{{ $booking->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl shadow-inner p-16 text-center">
                    <div class="inline-block p-6 bg-red-100 rounded-full mb-4">
                        <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Peminjaman Ditolak</h3>
                    <p class="text-gray-500">Semua peminjaman telah disetujui</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- STICKY BOTTOM BAR - Professional Stats Dashboard -->
    <div class="fixed bottom-0 left-0 right-0 bg-gradient-to-br from-slate-900 via-gray-900 to-slate-900 shadow-2xl border-t-4 border-yellow-500 z-40 backdrop-blur-xl">
        <div class="container mx-auto px-6 py-5 max-w-7xl">
            <div class="grid grid-cols-3 gap-6">
                
                <!-- Pending Stats Card -->
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition-opacity"></div>
                    <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-5 border border-yellow-500/30 hover:border-yellow-500 transition-all cursor-pointer transform hover:scale-105 duration-300" onclick="showTab('pending')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-yellow-500 rounded-xl blur-sm"></div>
                                    <div class="relative bg-gradient-to-br from-yellow-400 to-yellow-600 p-3 rounded-xl shadow-xl">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Pending</p>
                                    <p class="text-3xl font-black text-white tabular-nums">{{ $pendingBookings->count() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($pendingBookings->count() > 0)
                                    <div class="flex flex-col items-end space-y-1">
                                        <span class="px-3 py-1.5 bg-yellow-500 text-white text-xs font-bold rounded-lg shadow-lg animate-pulse">
                                            Action Required
                                        </span>
                                        <span class="text-xs text-yellow-400 font-semibold">Needs Review</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 text-xs font-medium">All Clear ✓</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approved Stats Card -->
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition-opacity"></div>
                    <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-5 border border-green-500/30 hover:border-green-500 transition-all cursor-pointer transform hover:scale-105 duration-300" onclick="showTab('approved')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-green-500 rounded-xl blur-sm"></div>
                                    <div class="relative bg-gradient-to-br from-green-400 to-green-600 p-3 rounded-xl shadow-xl">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Approved</p>
                                    <p class="text-3xl font-black text-white tabular-nums">{{ $approvedBookings->count() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex flex-col items-end space-y-1">
                                    <span class="inline-flex items-center px-3 py-1 bg-green-500/20 text-green-400 text-xs font-bold rounded-lg border border-green-500/30">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Active
                                    </span>
                                    <span class="text-xs text-green-400 font-semibold">Bookings Live</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejected Stats Card -->
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-rose-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition-opacity"></div>
                    <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-5 border border-red-500/30 hover:border-red-500 transition-all cursor-pointer transform hover:scale-105 duration-300" onclick="showTab('rejected')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-red-500 rounded-xl blur-sm"></div>
                                    <div class="relative bg-gradient-to-br from-red-400 to-red-600 p-3 rounded-xl shadow-xl">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Rejected</p>
                                    <p class="text-3xl font-black text-white tabular-nums">{{ $rejectedBookings->count() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex flex-col items-end space-y-1">
                                    <span class="inline-flex items-center px-3 py-1 bg-red-500/20 text-red-400 text-xs font-bold rounded-lg border border-red-500/30">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Declined
                                    </span>
                                    <span class="text-xs text-red-400 font-semibold">Not Approved</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Enhanced Footer Info -->
            <div class="mt-4 flex items-center justify-between text-xs border-t border-gray-700/50 pt-4">
                <div class="flex items-center space-x-6">
                    <span class="inline-flex items-center text-gray-300 font-medium">
                        <span class="relative flex h-2.5 w-2.5 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        System Online
                    </span>
                    <span class="text-white font-bold">
                        <span class="text-gray-400 font-normal">Total:</span> 
                        {{ $pendingBookings->count() + $approvedBookings->count() + $rejectedBookings->count() }} Bookings
                    </span>
                    <span class="text-gray-400">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </span>
                </div>
                <span class="text-gray-400">
                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    {{ now()->locale('id')->isoFormat('HH:mm:ss') }} WIB
                </span>
            </div>
        </div>
    </div>

    <!-- Modern Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Tolak Peminjaman</h3>
                        <p class="text-sm text-gray-500">Berikan alasan penolakan</p>
                    </div>
                </div>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="rejection_reason" 
                        rows="4" 
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all" 
                        required 
                        placeholder="Contoh: Jadwal bertabrakan dengan kegiatan lain, dokumen tidak lengkap, dll..."
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Peminjam akan melihat alasan ini
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeRejectModal()" 
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-semibold transition-all"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching with smooth animations
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active state from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-yellow-500', 'text-yellow-700', 'border-green-500', 'text-green-700', 'border-red-500', 'text-red-700');
                btn.classList.add('border-transparent', 'text-gray-500');
                
                // Update badge colors
                const badge = btn.querySelector('span:last-child');
                if (badge) {
                    badge.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
                    badge.classList.add('bg-gray-200', 'text-gray-700');
                }
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            // Add active state to clicked button
            const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
            if (tabName === 'pending') {
                activeBtn.classList.add('border-yellow-500', 'text-yellow-700');
                const badge = activeBtn.querySelector('span:last-child');
                if (badge) {
                    badge.classList.remove('bg-gray-200', 'text-gray-700');
                    badge.classList.add('bg-yellow-100', 'text-yellow-800');
                }
            } else if (tabName === 'approved') {
                activeBtn.classList.add('border-green-500', 'text-green-700');
                const badge = activeBtn.querySelector('span:last-child');
                if (badge) {
                    badge.classList.remove('bg-gray-200', 'text-gray-700');
                    badge.classList.add('bg-green-100', 'text-green-800');
                }
            } else if (tabName === 'rejected') {
                activeBtn.classList.add('border-red-500', 'text-red-700');
                const badge = activeBtn.querySelector('span:last-child');
                if (badge) {
                    badge.classList.remove('bg-gray-200', 'text-gray-700');
                    badge.classList.add('bg-red-100', 'text-red-800');
                }
            }
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
            
            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Approve booking
        function approveBooking(id) {
            if (confirm('✅ Setujui peminjaman ini?\n\nPeminjam akan dapat menggunakan laboratorium sesuai jadwal yang diminta.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/bookings/${id}/approve`;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Show reject modal
        function showRejectModal(id) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/admin/bookings/${id}/reject`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Close reject modal
        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            form.reset();
        }

        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // ESC key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>
