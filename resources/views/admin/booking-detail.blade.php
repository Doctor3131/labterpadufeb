<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - Laboratorium dan Fasilitas Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-20">
        <div class="container mx-auto px-4 md:px-6 py-3 md:py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2 md:space-x-3">
                    <a href="{{ route('landing') }}">
                        <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-8 md:h-12 w-auto object-contain">
                    </a>

                </div>
                <a href="{{ route('admin.dashboard') }}" class="group flex items-center text-sm font-medium text-gray-500 hover:text-yellow-600 transition-colors">
                    <div class="p-1.5 rounded-full group-hover:bg-yellow-50 transition-colors mr-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </div>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 md:px-6 py-4 md:py-8 max-w-6xl pb-32 md:pb-8">
        <!-- Main Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 md:mb-8">
            <div class="w-full md:w-auto">
                <div class="flex flex-wrap items-center gap-2 md:gap-3 mb-2">
                    <h1 class="text-xl md:text-3xl font-extrabold text-gray-900 tracking-tight">Detail Peminjaman</h1>
                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-500 text-[10px] md:text-xs font-mono font-medium border border-gray-200">#{{ $booking->id }}</span>
                </div>
                <p class="text-gray-500 text-xs md:text-sm">Diajukan pada {{ $booking->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 md:gap-3 mt-2 md:mt-0">
                 @if($booking->status === 'pending')
                    <span class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200 shadow-sm">
                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4 mr-1.5 md:mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Menunggu Persetujuan
                    </span>
                    <div class="h-6 w-px bg-gray-300 mx-1 hidden md:block"></div>
                    <!-- Desktop Operations -->
                    <div class="hidden md:flex gap-2 w-full md:w-auto">
                        <form action="{{ route('admin.booking.approve', $booking->id) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" onclick="return confirm('Setujui peminjaman ini?')" class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Setujui
                            </button>
                        </form>
                        <button onclick="showRejectModal()" class="flex-1 md:flex-none w-full md:w-auto inline-flex justify-center items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Tolak
                        </button>
                    </div>
                 @elseif($booking->status === 'approved')
                    <span class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-semibold bg-green-100 text-green-800 border border-green-200 shadow-sm">
                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4 mr-1.5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Disetujui
                    </span>
                 @else
                    <span class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-semibold bg-red-100 text-red-800 border border-red-200 shadow-sm">
                        <svg class="w-3.5 h-3.5 md:w-4 md:h-4 mr-1.5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Ditolak
                    </span>
                 @endif
            </div>
        </div>

        @if($booking->status === 'rejected' && $booking->rejection_reason)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">Alasan Penolakan</h3>
                        <div class="mt-1 text-sm text-red-700">
                            {{ $booking->rejection_reason }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @php
            $isPersonalNoDoc = ($booking->booking_type === 'pribadi' && !$booking->document_path);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Booking Info & Applicant Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Booking Info Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-yellow-500 to-yellow-600">
                        <h3 class="font-bold text-white flex items-center text-lg">
                            <svg class="w-5 h-5 mr-2 text-yellow-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Informasi Peminjaman
                        </h3>
                    </div>
                    <div class="p-4 md:p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Tanggal</label>
                            <p class="text-base md:text-lg">{{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Waktu</label>
                            <p class="text-base md:text-lg">{{ $booking->start_time }} - {{ $booking->end_time }} WIB</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Laboratorium</label>
                            <p class="text-base md:text-lg font-medium text-gray-900">{{ $booking->lab?->name ?? '-' }}</p>
                            @if(!$booking->lab && $booking->booking_type === 'pribadi')
                                <p class="text-xs text-gray-500 mt-1">Ditentukan di tempat oleh asisten lab</p>
                            @endif
                        </div>
                         <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Jumlah Peserta</span>
                                <span class="text-base font-bold text-gray-900">{{ $booking->participant_count }} Orang</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Applicant Card (Render here if NOT moving) -->
                @if(!$isPersonalNoDoc)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                     <div class="px-6 py-4 bg-gradient-to-r from-slate-700 to-slate-800">
                        <h3 class="font-bold text-white flex items-center text-lg">
                            <svg class="w-5 h-5 mr-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Informasi Peminjam
                        </h3>
                    </div>
                    <div class="p-4 md:p-6 space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-base md:text-lg">
                                {{ substr($booking->pic_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-base font-bold text-gray-900">{{ $booking->pic_name }}</p>
                                <p class="text-sm text-gray-500">{{ $booking->nip ?: $booking->nim }}</p>
                            </div>
                        </div>
                        <div class="space-y-3 pt-2">
                            @if($booking->study_program)
                            <div class="flex items-start">
                                <span class="text-xs font-semibold text-gray-500 w-24 shrink-0 mt-0.5">Program Studi</span>
                                <span class="text-sm text-gray-800">{{ $booking->study_program }}</span>
                            </div>
                            @endif
                            <div class="flex items-start">
                                <span class="text-xs font-semibold text-gray-500 w-24 shrink-0 mt-0.5">Telepon</span>
                                <span class="text-sm text-gray-800">{{ $booking->phone_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Activity Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                        <h2 class="font-bold text-lg text-gray-800 flex items-center">
                            <span class="w-1.5 h-6 bg-yellow-500 rounded-full mr-3"></span>
                            Detail Kegiatan
                        </h2>
                        <x-booking-badge :type="$booking->booking_type" class="text-xs font-medium" />
                    </div>
                    
                    <div class="p-6">
                        @if($booking->booking_type === 'non_perkuliahan')
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                <div class="col-span-2 md:col-span-1">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Nama Kegiatan</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $booking->activity_name }}</dd>
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Jenis Kegiatan</dt>
                                    <dd class="text-base text-gray-800">{{ $booking->activity_type }}</dd>
                                </div>
                                @if($booking->position)
                                <div class="col-span-2 md:col-span-1">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Posisi Peminjam</dt>
                                    <dd class="text-base text-gray-800">{{ $booking->position }}</dd>
                                </div>
                                @endif
                                @if($booking->equipment_needs)
                                <div class="col-span-2">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Kebutuhan Peralatan</dt>
                                    <dd class="text-base text-gray-800 bg-gray-50 p-3 rounded-lg">{{ $booking->equipment_needs }}</dd>
                                </div>
                                @endif
                            </dl>
                        @elseif($booking->booking_type === 'pribadi')
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                <div class="col-span-2 {{ ($booking->applicant_status === 'Mahasiswa') ? 'md:col-span-1' : 'md:col-span-1' }}">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Status Peminjam</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        @if($booking->applicant_status === 'Lainnya' && $booking->custom_status)
                                            {{ $booking->custom_status }}
                                        @else
                                            {{ $booking->applicant_status }}
                                        @endif
                                    </dd>
                                </div>
                                
                                @if($booking->applicant_status === 'Mahasiswa')
                                <div class="col-span-2 md:col-span-1">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Angkatan</dt>
                                    <dd class="text-base text-gray-800">{{ $booking->class_year ?: '-' }}</dd>
                                </div>
                                @endif

                                <div class="col-span-2 {{ ($booking->applicant_status !== 'Mahasiswa') ? 'md:col-span-1' : '' }}">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Keperluan</dt>
                                    <dd class="text-base text-gray-800 bg-gray-50 p-3 rounded-lg">{{ $booking->purpose }}</dd>
                                </div>
                            </dl>
                        @else
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                <div class="col-span-2">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Mata Kuliah</dt>
                                    <dd class="text-xl font-bold text-gray-900">{{ $booking->course_name }}</dd>
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Dosen Pengampu</dt>
                                    <dd class="text-base font-medium text-gray-900">{{ $booking->lecturer_name }}</dd>
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">NIP Dosen</dt>
                                    <dd class="text-base text-gray-600 font-mono">{{ $booking->lecturer_nip }}</dd>
                                </div>
                                @if($booking->software_needs)
                                <div class="col-span-2">
                                    <dt class="text-xs font-semibold text-gray-500 mb-1">Software yang Digunakan</dt>
                                    <dd class="text-base text-gray-800 bg-gray-50 p-3 rounded-lg">{{ $booking->software_needs }}</dd>
                                </div>
                                @endif
                            </dl>
                        @endif
                    </div>
                </div>

                <!-- Applicant Card (Render here if moving) -->
                @if($isPersonalNoDoc)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                     <div class="px-6 py-4 bg-gradient-to-r from-slate-700 to-slate-800">
                        <h3 class="font-bold text-white flex items-center text-lg">
                            <svg class="w-5 h-5 mr-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Informasi Peminjam
                        </h3>
                    </div>
                    <div class="p-4 md:p-6 space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 md:h-12 md:w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-base md:text-lg">
                                {{ substr($booking->pic_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-base font-bold text-gray-900">{{ $booking->pic_name }}</p>
                                <p class="text-sm text-gray-500">{{ $booking->nip ?: $booking->nim }}</p>
                            </div>
                        </div>
                        <div class="space-y-3 pt-2">
                            @if($booking->study_program)
                            <div class="flex items-start">
                                <span class="text-xs font-semibold text-gray-500 w-24 shrink-0 mt-0.5">Program Studi</span>
                                <span class="text-sm text-gray-800">{{ $booking->study_program }}</span>
                            </div>
                            @endif
                            <div class="flex items-start">
                                <span class="text-xs font-semibold text-gray-500 w-24 shrink-0 mt-0.5">Telepon</span>
                                <span class="text-sm text-gray-800">{{ $booking->phone_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Documents -->
                 @if($booking->document_path)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <h3 class="font-bold text-gray-800">Dokumen Pendukung</h3>
                    </div>
                    <div class="p-6">
                         <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-xl border border-yellow-100 group hover:border-yellow-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="bg-white p-2 rounded-lg shadow-sm">
                                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 group-hover:text-yellow-700 transition-colors">Dokumen PDF</p>
                                    <p class="text-xs text-yellow-600/70">Klik tombol di samping untuk mengunduh</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $booking->document_path) }}" target="_blank" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded-lg shadow transition-all flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-800 text-slate-400 py-5 mt-12">
        <div class="container mx-auto px-4 lg:px-8 text-center">
            <!-- Title -->
            <p class="text-white text-sm font-medium mb-3">Hubungi Kami</p>
            <!-- Contact Info - Horizontal -->
            <div class="flex flex-wrap items-center justify-center gap-4 lg:gap-6 mb-4 text-sm">
                <!-- Instagram -->
                <a href="https://instagram.com/labdigital_feb" target="_blank" rel="noopener noreferrer" 
                   class="flex items-center gap-2 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                    <span>@labdigital_feb</span>
                </a>
                <!-- WhatsApp -->
                <a href="https://wa.me/6287741191305" target="_blank" rel="noopener noreferrer" 
                   class="flex items-center gap-2 hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span>0877-4119-1305</span>
                </a>
                <!-- Email -->
                <a href="mailto:labdigital@feb.ac.id" class="flex items-center gap-2 hover:text-blue-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>labdigital@feb.ac.id</span>
                </a>
            </div>
            <!-- Copyright -->
            <p class="text-sm lg:text-base">&copy; {{ date('Y') }} Laboratorium dan Fasilitas Digital FEB UNDIP. All rights reserved.</p>
        </div>
    </footer>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center z-50 px-4 transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-lg w-full shadow-2xl transform transition-all scale-100">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="bg-red-50 p-3 rounded-full">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Tolak Peminjaman</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.booking.reject', $booking->id) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="rejection_reason" 
                        rows="4" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow outline-none resize-none text-gray-700 bg-gray-50 focus:bg-white" 
                        required 
                        placeholder="Contoh: Jadwal bertabrakan dengan kegiatan prioritas fakultas..."
                    ></textarea>
                    <p class="mt-2 text-xs text-gray-500 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Alasan akan dikirimkan ke peminjam.
                    </p>
                </div>
                
                <div class="flex justify-end gap-3 bg-gray-50 -mx-6 -mb-8 p-6 rounded-b-2xl mt-8">
                    <button type="button" onclick="closeRejectModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors shadow-sm">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors shadow-md flex items-center">
                        Tolak Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobile Fixed Bottom Action Bar -->
    @if($booking->status === 'pending')
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 md:hidden z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
        <div class="flex gap-3">
             <form action="{{ route('admin.booking.approve', $booking->id) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" onclick="return confirm('Setujui peminjaman ini?')" class="w-full inline-flex justify-center items-center px-4 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-base font-bold rounded-xl shadow-lg transition-all active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Setujui
                </button>
            </form>
            <button onclick="showRejectModal()" class="flex-1 inline-flex justify-center items-center px-4 py-3.5 bg-rose-600 hover:bg-rose-700 text-white text-base font-bold rounded-xl shadow-lg transition-all active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Tolak
            </button>
        </div>
    </div>
    @endif

    <script>
        function showRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Small delay to allow display flex to apply before opacity transition
            setTimeout(() => {
                modal.classList.remove('opacity-0');
            }, 10);
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300); // Match transition duration
        }

        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
        
        // Add opacity class for transition
        document.getElementById('rejectModal').classList.add('opacity-0');
    </script>
</body>
</html>
