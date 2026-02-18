@extends('layouts.admin')

@section('title', 'Dashboard Admin - Lab Digital FEB UNDIP')

@push('styles')
    <style>
        /* Mobile-friendly animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .booking-card {
            animation: fadeInUp 0.4s ease-out;
        }
        .booking-card:nth-child(1) { animation-delay: 0.05s; }
        .booking-card:nth-child(2) { animation-delay: 0.1s; }
        .booking-card:nth-child(3) { animation-delay: 0.15s; }
        .booking-card:nth-child(4) { animation-delay: 0.2s; }
        .booking-card:nth-child(5) { animation-delay: 0.25s; }
    </style>
@endpush

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-yellow-500 rounded-2xl p-4 md:p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Manajemen Peminjaman</h1>
                    <p class="text-xs md:text-sm text-yellow-50">Kelola permintaan peminjaman lab dan barang</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-2 md:p-3 rounded-xl">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            
            <!-- Toggle Buttons -->
            <div class="flex gap-2 md:gap-3">
                <button onclick="switchView('lab')" id="btn-view-lab" class="view-toggle flex-1 px-4 py-2.5 md:py-3 bg-white text-yellow-700 rounded-xl font-bold shadow-lg transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="hidden sm:inline">Peminjaman Lab</span>
                    <span class="sm:hidden">Lab</span>
                    <span class="px-2 py-0.5 bg-yellow-500 text-white rounded-full text-xs font-bold">{{ $pendingBookings->total() }}</span>
                </button>
                <button onclick="switchView('asset')" id="btn-view-asset" class="view-toggle flex-1 px-4 py-2.5 md:py-3 bg-white/20 hover:bg-white/30 text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <span class="hidden sm:inline">Peminjaman Barang</span>
                    <span class="sm:hidden">Barang</span>
                    <span class="px-2 py-0.5 bg-yellow-500 text-white rounded-full text-xs font-bold">{{ $pendingAssetBorrowings->total() }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 md:px-6 py-4 rounded-r-lg shadow-sm animate-pulse">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium text-sm md:text-base">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- LAB BORROWING SECTION -->
    <div id="lab-section" class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="border-b-2 border-gray-100 overflow-x-auto">
            <!-- Tab Title -->
            <div class="bg-yellow-50 px-4 py-2 border-b border-yellow-200">
                <h3 class="text-sm font-bold text-yellow-800">📚 Peminjaman Laboratorium</h3>
            </div>
            <nav class="flex px-2 min-w-max" aria-label="Tabs">
                <button onclick="showTab('pending')" class="tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-yellow-500 text-yellow-700" data-tab="pending">
                    <div class="flex items-center justify-center">
                        <div class="bg-yellow-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Pending</span>
                    <span class="mt-1 px-2 py-0.5 bg-yellow-500 text-white rounded-full text-xs font-bold">{{ $pendingBookings->total() }}</span>
                </button>
                <button onclick="showTab('approved')" class="tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-transparent text-gray-500" data-tab="approved">
                    <div class="flex items-center justify-center">
                        <div class="bg-gray-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Disetujui</span>
                    <span class="mt-1 px-2 py-0.5 bg-gray-300 text-gray-700 rounded-full text-xs font-bold">{{ $approvedBookings->total() }}</span>
                </button>
                <button onclick="showTab('rejected')" class="tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-transparent text-gray-500" data-tab="rejected">
                    <div class="flex items-center justify-center">
                        <div class="bg-gray-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Ditolak</span>
                    <span class="mt-1 px-2 py-0.5 bg-gray-300 text-gray-700 rounded-full text-xs font-bold">{{ $rejectedBookings->total() }}</span>
                </button>
            </nav>
        </div>

    <!-- Pending Bookings -->
    <div id="pending-tab" class="tab-content hidden p-3 md:p-6">
        @forelse($pendingBookings as $booking)
            <div class="booking-card bg-yellow-50 rounded-2xl shadow-lg hover:shadow-2xl mb-3 p-4 border-l-4 border-yellow-500 transition-all">
                <!-- Header dengan badges dan tanggal -->
                <div class="flex items-start justify-between gap-2 mb-0">
                    <div class="flex flex-wrap items-center gap-2 flex-1">
                        <x-room-badge :lab="$booking->lab->name" :type="$booking->booking_type" class="px-3 py-1.5 text-xs lg:text-sm" />
                        <x-booking-badge :type="$booking->booking_type" class="px-3 py-1.5 text-xs font-semibold rounded-lg" />
                        <!-- Waktu Dibuat - Inline -->
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                            {{ $booking->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <!-- Tanggal & Waktu - Compact -->
                    <div class="flex flex-col items-end text-right flex-shrink-0">
                        <span class="text-xs font-bold text-gray-800">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('D MMM YYYY') }}
                        </span>
                        <span class="text-xs text-purple-600 font-medium">
                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                        </span>
                    </div>
                </div>
                
                <!-- Judul -->
                <h3 class="text-base md:text-lg font-bold text-gray-800 mb-1.5 mt-2">
                    @if($booking->booking_type === 'non_perkuliahan')
                        {{ $booking->activity_name }}
                    @elseif($booking->booking_type === 'pribadi')
                        {{ $booking->purpose ?? 'Peminjaman Pribadi' }}
                    @else
                        {{ $booking->course_name }}
                    @endif
                </h3>
                
                <!-- Info Detail - Grid Layout yang Lebih Rapi -->
                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <div class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span><strong>Peminjam:</strong> {{ $booking->pic_name }}</span>
                    </div>
                    @if($booking->study_program)
                    <div class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                        <span><strong>Prodi:</strong> {{ $booking->study_program }}</span>
                    </div>
                    @endif
                    @if($booking->booking_type !== 'non_perkuliahan' && $booking->booking_type !== 'pribadi')
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                            <span><strong>Dosen:</strong> {{ $booking->lecturer_name }}</span>
                        </div>
                    @endif
                    <div class="flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                        <span><strong>Peserta:</strong> {{ $booking->participant_count }} orang</span>
                    </div>
                    @if($booking->booking_type === 'non_perkuliahan' && $booking->activity_type)
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>Jenis Kegiatan:</strong> {{ $booking->activity_type }}</span>
                        </div>
                    @endif
                    @if($booking->booking_type === 'pribadi')
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>Status:</strong> 
                                @if($booking->applicant_status === 'Lainnya' && $booking->custom_status)
                                    {{ $booking->custom_status }}
                                @else
                                    {{ $booking->applicant_status }}
                                @endif
                                @if($booking->applicant_status === 'Mahasiswa' && $booking->class_year)
                                    • Angkatan {{ $booking->class_year }}
                                @endif
                            </span>
                        </div>
                        @if($booking->purpose)
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>Keperluan:</strong> {{ $booking->purpose }}</span>
                        </div>
                        @endif
                    @endif
                </div>

                <!-- Action Buttons - Full width on mobile, stacked -->
                <div class="flex flex-col sm:flex-row gap-2 pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.booking.show', $booking->id) }}" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Detail</span>
                    </a>
                    <button onclick="approveBooking({{ $booking->id }})" class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Setujui</span>
                    </button>
                    <button onclick="showRejectModal({{ $booking->id }})" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Tolak</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-yellow-50 rounded-2xl shadow-inner p-8 md:p-16 text-center">
                <div class="inline-block p-5 bg-yellow-100 rounded-2xl mb-4 shadow-lg">
                    <svg class="w-12 h-12 md:w-16 md:h-16 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Semua Telah Diproses</h3>
                <p class="text-sm text-gray-600">Tidak ada peminjaman yang menunggu persetujuan</p>
            </div>
        @endforelse
        
        {{-- Pagination Links --}}
        @if($pendingBookings->hasPages())
            <div class="mt-4 px-2">
                {{ $pendingBookings->links() }}
            </div>
        @endif
    </div>

    <!-- Approved Bookings -->
    <div id="approved-tab" class="tab-content hidden p-3 md:p-6">
        @forelse($approvedBookings as $booking)
            <div class="booking-card bg-green-50 rounded-2xl shadow-lg mb-3 p-4 border-l-4 border-green-500 hover:shadow-xl transition-all">
                <!-- Header dengan badges dan tanggal -->
                <div class="flex items-start justify-between gap-2 mb-0">
                    <div class="flex flex-wrap items-center gap-2 flex-1">
                        <span class="px-3 py-1.5 bg-green-500 text-white text-xs font-bold rounded-lg shadow-sm flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Disetujui
                        </span>
                        <x-room-badge :lab="$booking->lab->name" :type="$booking->booking_type" class="px-3 py-1.5 text-xs lg:text-sm" />
                        <x-booking-badge :type="$booking->booking_type" class="px-3 py-1.5 text-xs font-semibold rounded-lg" />
                    </div>
                    <!-- Tanggal & Waktu - Compact -->
                    <div class="flex flex-col items-end text-right flex-shrink-0">
                        <span class="text-xs font-bold text-gray-800">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('D MMM YYYY') }}
                        </span>
                        <span class="text-xs text-gray-600 font-medium">
                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                        </span>
                    </div>
                </div>

                <!-- Judul -->
                <h3 class="text-base md:text-lg font-bold text-gray-800 mb-1.5 mt-2">
                    @if($booking->booking_type === 'non_perkuliahan')
                        {{ $booking->activity_name }}
                    @elseif($booking->booking_type === 'pribadi')
                        {{ $booking->purpose ?? 'Peminjaman Pribadi' }}
                    @else
                        {{ $booking->course_name }}
                    @endif
                </h3>
                
                <!-- Info Detail -->
                <div class="space-y-1 mb-3">
                    @if($booking->booking_type === 'perkuliahan_tetap' || $booking->booking_type === 'perkuliahan_tidak_tetap')
                        <p class="text-sm text-gray-600">
                            <strong>Dosen:</strong> {{ $booking->lecturer_name }}
                        </p>
                    @elseif($booking->booking_type === 'non_perkuliahan')
                        <p class="text-sm text-gray-600">
                            <strong>{{ $booking->position }}</strong> • {{ $booking->activity_type }}
                        </p>
                    @elseif($booking->booking_type === 'pribadi')
                        <p class="text-sm text-gray-600">
                            <strong>Status:</strong> 
                            @if($booking->applicant_status === 'Lainnya' && $booking->custom_status)
                                {{ $booking->custom_status }}
                            @else
                                {{ $booking->applicant_status }}
                            @endif
                            @if($booking->applicant_status === 'Mahasiswa' && $booking->class_year)
                                • Angkatan {{ $booking->class_year }}
                            @endif
                        </p>
                    @endif
                    <p class="text-sm text-gray-600">
                        <strong>{{ $booking->pic_name }}</strong> • {{ $booking->participant_count }} orang
                    </p>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 pt-3 border-t border-gray-100">
                    @if($booking->handler)
                        <span class="text-xs text-purple-600 font-medium flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            {{ $booking->handler->name }}
                        </span>
                    @else
                        <div></div>
                    @endif
                    <div class="flex flex-wrap items-center gap-2">
                        @if($booking->document_path)
                            <a href="{{ asset('storage/' . $booking->document_path) }}" target="_blank" class="flex items-center text-sm text-yellow-600 hover:text-yellow-800 font-medium transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Dokumen Pendukung
                            </a>
                        @endif
                        @if($booking->booking_type !== 'pribadi')
                            <a href="{{ route('booking.print', $booking->tracking_token) }}" target="_blank" class="flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-green-50 rounded-2xl shadow-inner p-8 md:p-16 text-center">
                <div class="inline-block p-5 bg-green-100 rounded-2xl mb-4 shadow-lg">
                    <svg class="w-12 h-12 md:w-16 md:h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Belum Ada Peminjaman Disetujui</h3>
                <p class="text-sm text-gray-600">Approve peminjaman untuk melihatnya di sini</p>
            </div>
        @endforelse
        
        {{-- Pagination Links --}}
        @if($approvedBookings->hasPages())
            <div class="mt-4 px-2">
                {{ $approvedBookings->links() }}
            </div>
        @endif
    </div>

    <!-- Rejected Bookings -->
    <div id="rejected-tab" class="tab-content hidden p-3 md:p-6">
        @forelse($rejectedBookings as $booking)
            <div class="booking-card bg-red-50 rounded-2xl shadow-lg mb-3 p-4 border-l-4 border-red-500 hover:shadow-xl transition-all">
                <!-- Header dengan badges dan tanggal -->
                <div class="flex items-start justify-between gap-2 mb-0">
                    <div class="flex flex-wrap items-center gap-2 flex-1">
                        <span class="px-3 py-1.5 bg-red-500 text-white text-xs font-bold rounded-lg shadow-sm flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Ditolak
                        </span>
                        <x-room-badge :lab="$booking->lab->name" :type="$booking->booking_type" class="px-3 py-1.5 text-xs lg:text-sm" />
                        <x-booking-badge :type="$booking->booking_type" class="px-3 py-1.5 text-xs font-semibold rounded-lg" />
                    </div>
                    <!-- Tanggal & Waktu - Compact -->
                    <div class="flex flex-col items-end text-right flex-shrink-0">
                        <span class="text-xs font-bold text-gray-800">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('D MMM YYYY') }}
                        </span>
                        <span class="text-xs text-gray-600 font-medium">
                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                        </span>
                    </div>
                </div>

                <!-- Judul -->
                <h3 class="text-base md:text-lg font-bold text-gray-800 mb-1.5 mt-2">
                    @if($booking->booking_type === 'non_perkuliahan')
                        {{ $booking->activity_name }}
                    @elseif($booking->booking_type === 'pribadi')
                        {{ $booking->purpose ?? 'Peminjaman Pribadi' }}
                    @else
                        {{ $booking->course_name }}
                    @endif
                </h3>
                
                <!-- Info Detail -->
                <div class="space-y-1 mb-3">
                    @if($booking->booking_type === 'perkuliahan_tetap' || $booking->booking_type === 'perkuliahan_tidak_tetap')
                        <p class="text-sm text-gray-600">
                            <strong>Dosen:</strong> {{ $booking->lecturer_name }}
                        </p>
                    @elseif($booking->booking_type === 'non_perkuliahan')
                        <p class="text-sm text-gray-600">
                            <strong>{{ $booking->position }}</strong> • {{ $booking->activity_type }}
                        </p>
                    @elseif($booking->booking_type === 'pribadi')
                        <p class="text-sm text-gray-600">
                            <strong>Status:</strong> 
                            @if($booking->applicant_status === 'Lainnya' && $booking->custom_status)
                                {{ $booking->custom_status }}
                            @else
                                {{ $booking->applicant_status }}
                            @endif
                            @if($booking->applicant_status === 'Mahasiswa' && $booking->class_year)
                                • Angkatan {{ $booking->class_year }}
                            @endif
                        </p>
                    @endif
                    <p class="text-sm text-gray-600">
                        <strong>{{ $booking->pic_name }}</strong> • {{ $booking->participant_count }} orang
                    </p>
                </div>

                <!-- Alasan Penolakan -->
                @if($booking->rejection_reason)
                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-xs font-semibold text-red-600 mb-1">Alasan Penolakan:</p>
                        <p class="text-sm text-red-700">{{ $booking->rejection_reason }}</p>
                    </div>
                @endif
                
                <!-- Footer - Handler Info -->
                @if($booking->handler)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <span class="text-xs text-purple-600 font-medium flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Ditolak oleh: <span class="font-semibold ml-1">{{ $booking->handler->name }}</span>
                        </span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-red-50 rounded-2xl shadow-inner p-8 md:p-16 text-center">
                <div class="inline-block p-5 bg-red-100 rounded-2xl mb-4 shadow-lg">
                    <svg class="w-12 h-12 md:w-16 md:h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Tidak Ada Peminjaman Ditolak</h3>
                <p class="text-sm text-gray-600">Semua peminjaman telah disetujui</p>
            </div>
        @endforelse
        
        {{-- Pagination Links --}}
        @if($rejectedBookings->hasPages())
            <div class="mt-4 px-2">
                {{ $rejectedBookings->links() }}
            </div>
        @endif
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
                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all flex items-center"
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

<!-- ========================================= -->
<!-- ASSET BORROWING SECTION (PURPLE THEME) -->
<!-- ========================================= -->
<div id="asset-section" class="mb-6 mt-8 hidden">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="border-b-2 border-gray-100 overflow-x-auto">
            <!-- Tab Title -->
            <div class="bg-yellow-50 px-4 py-2 border-b border-yellow-200">
                <h3 class="text-sm font-bold text-yellow-800">📦 Peminjaman Barang</h3>
            </div>
            <nav class="flex px-2 min-w-max" aria-label="Tabs">
                <button onclick="showTab('asset-pending')" class="tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-purple-500 text-purple-700" data-tab="asset-pending">
                    <div class="flex items-center justify-center">
                        <div class="bg-purple-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Pending</span>
                    <span class="mt-1 px-2 py-0.5 bg-purple-500 text-white rounded-full text-xs font-bold">{{ $pendingAssetBorrowings->total() }}</span>
                </button>
                <button onclick="showTab('asset-approved')" class="tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-transparent text-gray-500" data-tab="asset-approved">
                    <div class="flex items-center justify-center">
                        <div class="bg-gray-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Diproses</span>
                    <span class="mt-1 px-2 py-0.5 bg-gray-300 text-gray-700 rounded-full text-xs font-bold">{{ $approvedAssetBorrowings->total() }}</span>
                </button>
                <button onclick="showTab('asset-completed')" class="tab-button flex-1 flex flex-col items-center px-3 py-3 text-xs md:text-sm font-semibold border-b-3 border-transparent text-gray-500" data-tab="asset-completed">
                    <div class="flex items-center justify-center">
                        <div class="bg-gray-100 p-2 rounded-lg mb-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="hidden md:inline">Selesai</span>
                    <span class="mt-1 px-2 py-0.5 bg-gray-300 text-gray-700 rounded-full text-xs font-bold">{{ $completedAssetBorrowings->total() }}</span>
                </button>
            </nav>
        </div>

        <!-- Asset Pending Tab -->
        <div id="asset-pending-tab" class="tab-content p-3 md:p-6">
            @forelse($pendingAssetBorrowings as $borrowing)
                <div class="booking-card bg-yellow-50 rounded-2xl shadow-lg hover:shadow-2xl mb-3 p-4 border-l-4 border-yellow-500 transition-all">
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex flex-wrap items-center gap-2 flex-1">
                            <span class="px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-bold rounded-lg">
                                📦 BARANG #{{ $borrowing->id }}
                            </span>

                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                                {{ $borrowing->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="flex flex-col items-end text-right flex-shrink-0">
                            <span class="text-xs font-bold text-gray-800">
                                {{ \Carbon\Carbon::parse($borrowing->borrow_date)->locale('id')->isoFormat('D MMM') }} - {{ \Carbon\Carbon::parse($borrowing->return_date)->locale('id')->isoFormat('D MMM YYYY') }}
                            </span>
                            <span class="text-xs text-purple-600 font-medium">
                                {{ \Carbon\Carbon::parse($borrowing->borrow_date)->diffInDays(\Carbon\Carbon::parse($borrowing->return_date)) + 1 }} hari
                            </span>
                        </div>
                    </div>

                    <!-- Info Detail -->
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>Peminjam:</strong> {{ $borrowing->borrower_name }} ({{ ucfirst($borrowing->borrower_type) }})</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            <span>{{ $borrowing->phone_number }} • {{ $borrowing->email }}</span>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>Tujuan:</strong> {{ $borrowing->purpose }}</span>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-2 mt-2">
                            <strong class="text-xs text-purple-700">Barang yang Dipinjam:</strong>
                            <div class="mt-1 space-y-1">
                                @php
                                    $groupedItems = $borrowing->borrowedItems->groupBy(function($item) {
                                        return $item->item->category ?? $item->item->name;
                                    })->map(function($items) {
                                        $first = $items->first();
                                        return [
                                            'name' => $first->item->category ?? $first->item->name,
                                            'quantity' => $items->sum('quantity')
                                        ];
                                    });
                                @endphp
                                @foreach($groupedItems->take(3) as $item)
                                    <div class="text-xs text-gray-700">• {{ $item['name'] }} ({{ $item['quantity'] }}x)</div>
                                @endforeach
                                @if($groupedItems->count() > 3)
                                    <div class="text-xs text-purple-600 font-medium">+{{ $groupedItems->count() - 3 }} item lainnya</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('admin.asset-borrowings.show', $borrowing->id) }}" 
                                class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Detail & Proses
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-purple-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Tidak ada peminjaman barang pending</p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($pendingAssetBorrowings->hasPages())
                <div class="mt-6">
                    {{ $pendingAssetBorrowings->links() }}
                </div>
            @endif
        </div>

        <!-- Asset Approved Tab -->
        <div id="asset-approved-tab" class="tab-content p-3 md:p-6 hidden">
            @forelse($approvedAssetBorrowings as $borrowing)
                <div class="booking-card bg-green-50 rounded-2xl shadow-lg hover:shadow-2xl mb-3 p-4 border-l-4 border-green-500 transition-all">
                    <!-- Similar structure but with handout/receive buttons -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex flex-wrap items-center gap-2 flex-1">
                            <span class="px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-lg">
                                📦 BARANG #{{ $borrowing->id }}
                            </span>
                            <span class="px-3 py-1.5 {{ $borrowing->status === 'approved' ? 'bg-green-500' : 'bg-blue-600' }} text-white text-xs font-bold rounded-lg">
                                {{ $borrowing->status === 'approved' ? 'Disetujui' : 'Dipinjam' }}
                            </span>

                        </div>
                        <div class="flex flex-col items-end text-right flex-shrink-0">
                            <span class="text-xs font-bold text-gray-800">
                                {{ \Carbon\Carbon::parse($borrowing->borrow_date)->locale('id')->isoFormat('D MMM') }} - {{ \Carbon\Carbon::parse($borrowing->return_date)->locale('id')->isoFormat('D MMM YYYY') }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <span><strong>Peminjam:</strong> {{ $borrowing->borrower_name }}</span>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-2">
                            <strong class="text-xs text-blue-700">Barang:</strong>
                            <div class="mt-1 space-y-1">
                                @php
                                    $groupedItems = $borrowing->borrowedItems->groupBy(function($item) {
                                        return $item->item->category ?? $item->item->name;
                                    })->map(function($items) {
                                        $first = $items->first();
                                        return [
                                            'name' => $first->item->category ?? $first->item->name,
                                            'quantity' => $items->sum('quantity')
                                        ];
                                    });
                                @endphp
                                @foreach($groupedItems->take(2) as $item)
                                    <div class="text-xs text-gray-700">• {{ $item['name'] }} ({{ $item['quantity'] }}x)</div>
                                @endforeach
                                @if($groupedItems->count() > 2)
                                    <div class="text-xs text-blue-600 font-medium">+{{ $groupedItems->count() - 2 }} item lainnya</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('admin.asset-borrowings.show', $borrowing->id) }}" 
                                class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-center transition-all">
                            Detail
                        </a>
                        @if($borrowing->status === 'approved')
                            <button onclick="handoutAssetBorrowing({{ $borrowing->id }})" 
                                    class="flex-1 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center">
                                📤 Serahkan
                            </button>
                        @else
                            <button onclick="receiveAssetBorrowing({{ $borrowing->id }})" 
                                    class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition-all flex items-center justify-center">
                                📥 Terima Kembali
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-blue-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Tidak ada peminjaman barang yang diproses</p>
                </div>
            @endforelse

            @if($approvedAssetBorrowings->hasPages())
                <div class="mt-6">
                    {{ $approvedAssetBorrowings->links() }}
                </div>
            @endif
        </div>

        <!-- Asset Completed Tab -->
        <div id="asset-completed-tab" class="tab-content p-3 md:p-6 hidden">
            @forelse($completedAssetBorrowings as $borrowing)
                <div class="booking-card bg-green-50 rounded-2xl shadow-lg hover:shadow-2xl mb-3 p-4 border-l-4 border-green-500 transition-all">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex flex-wrap items-center gap-2 flex-1">
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg">
                                📦 BARANG #{{ $borrowing->id }}
                            </span>
                            <span class="px-3 py-1.5 {{ $borrowing->getStatusBadgeColor() }} text-xs font-bold rounded-lg">
                                {{ $borrowing->getStatusLabel() }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $borrowing->updated_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div><strong>Peminjam:</strong> {{ $borrowing->borrower_name }}</div>
                        @if($borrowing->rejection_reason)
                            <div class="bg-red-50 border border-red-200 rounded p-2 text-xs text-red-700">
                                <strong>Alasan Ditolak:</strong> {{ $borrowing->rejection_reason }}
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('admin.asset-borrowings.show', $borrowing->id) }}" 
                       class="block px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-center transition-all">
                        Lihat Detail
                    </a>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Tidak ada peminjaman barang yang selesai</p>
                </div>
            @endforelse

            @if($completedAssetBorrowings->hasPages())
                <div class="mt-6">
                    {{ $completedAssetBorrowings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Handout Asset Modal -->
<div id="handoutAssetModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6 rounded-t-2xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 backdrop-blur-sm p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Serahkan Barang</h3>
                    <p class="text-sm text-purple-100">Konfirmasi penyerahan barang</p>
                </div>
            </div>
            <button onclick="closeHandoutModal()" class="text-white/80 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="handoutAssetForm" method="POST">
            @csrf
            <div class="p-6">
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        📦 Catatan Kondisi Barang <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea 
                        name="borrow_condition_notes" 
                        rows="4" 
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" 
                        placeholder="Contoh: Semua barang dalam kondisi baik, tidak ada kerusakan..."
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Catat kondisi barang saat diserahkan untuk dokumentasi
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeHandoutModal()" 
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-semibold transition-all"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Konfirmasi Serahkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Receive Asset Modal -->
<div id="receiveAssetModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6 rounded-t-2xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 backdrop-blur-sm p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Terima Kembali Barang</h3>
                    <p class="text-sm text-green-100">Konfirmasi pengembalian barang</p>
                </div>
            </div>
            <button onclick="closeReceiveModal()" class="text-white/80 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="receiveAssetForm" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        📥 Catatan Kondisi Barang <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea 
                        name="return_condition_notes" 
                        rows="3" 
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all" 
                        placeholder="Contoh: Semua barang dikembalikan dalam kondisi baik..."
                    ></textarea>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="isDamagedCheck"
                            name="is_damaged"
                            value="1"
                            onchange="toggleDamageDescription()"
                            class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500"
                        >
                        <span class="text-sm font-semibold text-gray-700">
                            ⚠️ Ada barang yang rusak/hilang?
                        </span>
                    </label>
                </div>

                <div id="damageDescriptionField" class="hidden">
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        🔧 Deskripsi Kerusakan/Kehilangan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="damage_description" 
                        id="damageDescription"
                        rows="3" 
                        class="w-full px-4 py-3 border-2 border-red-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all" 
                        placeholder="Jelaskan detail kerusakan atau barang yang hilang..."
                    ></textarea>
                    <p class="mt-2 text-xs text-red-600">
                        Wajib diisi jika ada kerusakan/kehilangan
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3 pt-2">
                    <button 
                        type="button" 
                        onclick="closeReceiveModal()" 
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-semibold transition-all"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Konfirmasi Terima
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Asset Borrowing Modal -->
<div id="rejectAssetModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white p-6 rounded-t-2xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 backdrop-blur-sm p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Tolak Peminjaman</h3>
                    <p class="text-sm text-red-100">Berikan alasan penolakan</p>
                </div>
            </div>
            <button onclick="closeRejectModal()" class="text-white/80 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="rejectAssetForm" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-3">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="rejection_reason" 
                        id="rejectionReasonInput"
                        rows="4" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all" 
                        placeholder="Jelaskan alasan penolakan peminjaman ini..."
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Alasan akan dikirimkan kepada peminjam
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3 pt-2">
                    <button 
                        type="button" 
                        onclick="closeRejectModal()" 
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-semibold transition-all"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak Peminjaman
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // View switching between Lab and Asset
    function switchView(view) {
        const labSection = document.getElementById('lab-section');
        const assetSection = document.getElementById('asset-section');
        const btnLab = document.getElementById('btn-view-lab');
        const btnAsset = document.getElementById('btn-view-asset');
        
        if (view === 'lab') {
            // Show lab section
            labSection.classList.remove('hidden');
            assetSection.classList.add('hidden');
            
            // Update button styles
            btnLab.classList.remove('bg-white/20', 'hover:bg-white/30', 'text-white');
            btnLab.classList.add('bg-white', 'text-yellow-700', 'shadow-lg');
            
            btnAsset.classList.remove('bg-white', 'text-yellow-700', 'shadow-lg');
            btnAsset.classList.add('bg-white/20', 'hover:bg-white/30', 'text-white');
        } else {
            // Show asset section
            labSection.classList.add('hidden');
            assetSection.classList.remove('hidden');
            
            // Update button styles
            btnAsset.classList.remove('bg-white/20', 'hover:bg-white/30', 'text-white');
            btnAsset.classList.add('bg-white', 'text-yellow-700', 'shadow-lg');
            
            btnLab.classList.remove('bg-white', 'text-yellow-700', 'shadow-lg');
            btnLab.classList.add('bg-white/20', 'hover:bg-white/30', 'text-white');
        }
        
        // Smooth scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Tab switching with smooth animations
    function showTab(tabName) {
        // Determine which section this tab belongs to
        const isLabTab = ['pending', 'approved', 'rejected'].includes(tabName);
        const isAssetTab = ['asset-pending', 'asset-approved', 'asset-completed'].includes(tabName);
        
        // Hide only tabs in the same section
        if (isLabTab) {
            // Hide all lab booking tabs
            document.getElementById('pending-tab').classList.add('hidden');
            document.getElementById('approved-tab').classList.add('hidden');
            document.getElementById('rejected-tab').classList.add('hidden');
            
            // Remove active state from all lab tab buttons
            document.querySelectorAll('#lab-section .tab-button').forEach(btn => {
                btn.classList.remove('border-yellow-500', 'text-yellow-700', 'border-green-500', 'text-green-700', 'border-red-500', 'text-red-700');
                btn.classList.add('border-transparent', 'text-gray-500');
                
                // Reset icon backgrounds
                const iconBg = btn.querySelector('div.bg-yellow-100, div.bg-green-100, div.bg-red-100');
                if (iconBg) {
                    iconBg.classList.remove('bg-yellow-100', 'bg-green-100', 'bg-red-100');
                    iconBg.classList.add('bg-gray-100');
                }
                
                // Update badge colors
                const badge = btn.querySelector('span.rounded-full');
                if (badge) {
                    badge.classList.remove('bg-yellow-500', 'text-white', 'bg-green-500', 'bg-red-500');
                    badge.classList.add('bg-gray-300', 'text-gray-700');
                }
            });
        } else if (isAssetTab) {
            // Hide all asset borrowing tabs
            document.getElementById('asset-pending-tab').classList.add('hidden');
            document.getElementById('asset-approved-tab').classList.add('hidden');
            document.getElementById('asset-completed-tab').classList.add('hidden');
            
            // Remove active state from all asset tab buttons
            document.querySelectorAll('#asset-section .tab-button').forEach(btn => {
                btn.classList.remove('border-purple-500', 'text-purple-700', 'border-blue-500', 'text-blue-700', 'border-green-500', 'text-green-700');
                btn.classList.add('border-transparent', 'text-gray-500');
                
                // Reset icon backgrounds
                const iconBg = btn.querySelector('div.bg-purple-100, div.bg-blue-100, div.bg-green-100');
                if (iconBg) {
                    iconBg.classList.remove('bg-purple-100', 'bg-blue-100', 'bg-green-100');
                    iconBg.classList.add('bg-gray-100');
                }
                
                // Update badge colors
                const badge = btn.querySelector('span.rounded-full');
                if (badge) {
                    badge.classList.remove('bg-purple-500', 'text-white', 'bg-blue-500', 'bg-green-500');
                    badge.classList.add('bg-gray-300', 'text-gray-700');
                }
            });
        }
        
        // Show selected tab
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        
        // Add active state to clicked button
        const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
        const iconBg = activeBtn.querySelector('div.p-2');
        const badge = activeBtn.querySelector('span.rounded-full');
        
        if (tabName === 'pending') {
            activeBtn.classList.add('border-yellow-500', 'text-yellow-700');
            if (iconBg) {
                iconBg.classList.remove('bg-gray-100');
                iconBg.classList.add('bg-yellow-100');
            }
            if (badge) {
                badge.classList.remove('bg-gray-300', 'text-gray-700');
                badge.classList.add('bg-yellow-500', 'text-white');
            }
        } else if (tabName === 'approved') {
            activeBtn.classList.add('border-green-500', 'text-green-700');
            if (iconBg) {
                iconBg.classList.remove('bg-gray-100');
                iconBg.classList.add('bg-green-100');
            }
            if (badge) {
                badge.classList.remove('bg-gray-300', 'text-gray-700');
                badge.classList.add('bg-green-500', 'text-white');
            }
        } else if (tabName === 'rejected') {
            activeBtn.classList.add('border-red-500', 'text-red-700');
            if (iconBg) {
                iconBg.classList.remove('bg-gray-100');
                iconBg.classList.add('bg-red-100');
            }
            if (badge) {
                badge.classList.remove('bg-gray-300', 'text-gray-700');
                badge.classList.add('bg-red-500', 'text-white');
            }
        } else if (tabName === 'asset-pending') {
            activeBtn.classList.add('border-purple-500', 'text-purple-700');
            if (iconBg) {
                iconBg.classList.remove('bg-gray-100');
                iconBg.classList.add('bg-purple-100');
            }
            if (badge) {
                badge.classList.remove('bg-gray-300', 'text-gray-700');
                badge.classList.add('bg-purple-500', 'text-white');
            }
        } else if (tabName === 'asset-approved') {
            activeBtn.classList.add('border-blue-500', 'text-blue-700');
            if (iconBg) {
                iconBg.classList.remove('bg-gray-100');
                iconBg.classList.add('bg-blue-100');
            }
            if (badge) {
                badge.classList.remove('bg-gray-300', 'text-gray-700');
                badge.classList.add('bg-blue-500', 'text-white');
            }
        } else if (tabName === 'asset-completed') {
            activeBtn.classList.add('border-green-500', 'text-green-700');
            if (iconBg) {
                iconBg.classList.remove('bg-gray-100');
                iconBg.classList.add('bg-green-100');
            }
            if (badge) {
                badge.classList.remove('bg-gray-300', 'text-gray-700');
                badge.classList.add('bg-green-500', 'text-white');
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

    // Detect active tab from URL parameters on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Check which pagination parameter exists for Lab section
        if (urlParams.has('approved_page')) {
            showTab('approved');
        } else if (urlParams.has('rejected_page')) {
            showTab('rejected');
        } else if (urlParams.has('pending_page')) {
            showTab('pending');
        } else {
            // Default: show pending tab for lab section
            showTab('pending');
        }
        
        // Check which pagination parameter exists for Asset section
        if (urlParams.has('asset_pending_page')) {
            showTab('asset-pending');
        } else if (urlParams.has('asset_approved_page')) {
            showTab('asset-approved');
        } else if (urlParams.has('asset_completed_page')) {
            showTab('asset-completed');
        } else {
            // Default: show asset-pending tab for asset section
            showTab('asset-pending');
        }
    });

    // Asset Borrowing Actions - defined in window scope
    window.approveAssetBorrowing = function(id) {
        console.log('Approve clicked for ID:', id);
        
        if (!confirm('✅ Setujui peminjaman barang ini?')) {
            return;
        }
        
        console.log('Creating form for approve...');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/asset-borrowings/${id}/approve`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        console.log('Submitting form...', form);
        form.submit();
    };

    window.rejectAssetBorrowing = function(id) {
        console.log('Reject clicked for ID:', id);
        
        // Open reject modal
        document.getElementById('rejectAssetModal').classList.remove('hidden');
        document.getElementById('rejectAssetModal').classList.add('flex');
        document.getElementById('rejectAssetForm').action = `/admin/asset-borrowings/${id}/reject`;
        document.getElementById('rejectionReasonInput').value = '';
        document.getElementById('rejectionReasonInput').focus();
    };

    // Close reject modal
    function closeRejectModal() {
        document.getElementById('rejectAssetModal').classList.add('hidden');
        document.getElementById('rejectAssetModal').classList.remove('flex');
        document.getElementById('rejectAssetForm').reset();
    }

    window.handoutAssetBorrowing = function(id) {
        // Open handout modal
        document.getElementById('handoutAssetModal').classList.remove('hidden');
        document.getElementById('handoutAssetModal').classList.add('flex');
        document.getElementById('handoutAssetForm').action = `/admin/asset-borrowings/${id}/handout`;
    };

    window.receiveAssetBorrowing = function(id) {
        // Open receive modal
        document.getElementById('receiveAssetModal').classList.remove('hidden');
        document.getElementById('receiveAssetModal').classList.add('flex');
        document.getElementById('receiveAssetForm').action = `/admin/asset-borrowings/${id}/receive`;
    };

    // Close handout modal
    function closeHandoutModal() {
        document.getElementById('handoutAssetModal').classList.add('hidden');
        document.getElementById('handoutAssetModal').classList.remove('flex');
        document.getElementById('handoutAssetForm').reset();
    }

    // Close receive modal
    function closeReceiveModal() {
        document.getElementById('receiveAssetModal').classList.add('hidden');
        document.getElementById('receiveAssetModal').classList.remove('flex');
        document.getElementById('receiveAssetForm').reset();
        document.getElementById('isDamagedCheck').checked = false;
        toggleDamageDescription();
    }

    // Toggle damage description field
    function toggleDamageDescription() {
        const checkbox = document.getElementById('isDamagedCheck');
        const field = document.getElementById('damageDescriptionField');
        const textarea = document.getElementById('damageDescription');
        
        if (checkbox.checked) {
            field.classList.remove('hidden');
            textarea.required = true;
        } else {
            field.classList.add('hidden');
            textarea.required = false;
            textarea.value = '';
        }
    }

    // Validate receive form before submit
    document.getElementById('receiveAssetForm')?.addEventListener('submit', function(e) {
        const isDamaged = document.getElementById('isDamagedCheck').checked;
        const damageDesc = document.getElementById('damageDescription').value.trim();
        
        if (isDamaged && !damageDesc) {
            e.preventDefault();
            alert('Deskripsi kerusakan/kehilangan harus diisi!');
            document.getElementById('damageDescription').focus();
        }
    });
</script>
@endpush
