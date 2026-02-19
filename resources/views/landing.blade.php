<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal & Manajemen Lab FEB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    
    <!-- Sticky Navbar -->
    <nav class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Left: Brand -->
                <a href="{{ route('landing') }}" class="flex items-center space-x-3 group cursor-pointer">
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-12 lg:h-16 w-auto object-contain group-hover:scale-105 transition-all duration-300">
                </a>

                <!-- Right: Navigation Links -->
                <div class="flex items-center gap-2 lg:gap-4">
                    <!-- Desktop Menu -->
                    <!-- Laporkan Masalah Button -->
                    <button onclick="openFeedbackModal()" class="hidden lg:inline-flex items-center px-4 py-2 text-slate-600 font-semibold hover:text-yellow-700 hover:bg-yellow-50 rounded-lg hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-fade-in-down animation-delay-200">
                        <svg class="w-5 h-5 mr-1 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Report
                    </button>

                    <a href="{{ route('schedules.index') }}" class="hidden lg:inline-flex items-center px-4 py-2 text-yellow-600 font-semibold hover:text-yellow-700 hover:bg-yellow-50 rounded-lg hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-fade-in-down animation-delay-300">
                        <svg class="w-5 h-5 mr-1 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Jadwal
                    </a>

                    <!-- Login/Dashboard Button -->
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 lg:px-6 py-2 border-2 border-yellow-500 text-yellow-600 font-semibold rounded-lg hover:bg-yellow-500 hover:text-white hover:shadow-xl hover:scale-110 transition-all duration-300 text-sm lg:text-base whitespace-nowrap animate-fade-in-down animation-delay-400">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 lg:px-6 py-2 border-2 border-yellow-500 text-yellow-600 font-semibold rounded-lg hover:bg-yellow-500 hover:text-white hover:shadow-xl hover:scale-110 transition-all duration-300 text-sm lg:text-base whitespace-nowrap animate-fade-in-down animation-delay-400">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 mx-4 lg:mx-8 mt-4 rounded-r-lg shadow-sm animate-pulse">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Hero Section -->
    <section class="relative bg-yellow-500 text-white overflow-hidden">
        <div class="container mx-auto px-4 lg:px-8 py-12 lg:py-24 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl lg:text-5xl font-bold mb-3 lg:mb-6 leading-tight drop-shadow-md animate-fade-in-up animation-delay-100">
                    Jadwal & Manajemen<br class="hidden lg:block"/> Laboratorium Digital
                </h2>
                <p class="text-base lg:text-xl text-yellow-50 max-w-2xl mx-auto drop-shadow mb-6 lg:mb-10 animate-fade-in-up animation-delay-200">
                    Pantau jadwal praktikum dan ajukan penggunaan fasilitas laboratorium secara terintegrasi
                </p>
                
                <!-- CTA Buttons - Center untuk semua device -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in-up animation-delay-300">
                    <!-- Button Peminjaman Lab -->
                    <a href="{{ route('booking.create') }}" class="inline-flex items-center px-8 py-3 lg:px-10 lg:py-4 bg-white text-yellow-600 font-bold rounded-full hover:bg-yellow-50 hover:shadow-2xl transition-all duration-300 shadow-xl text-sm lg:text-lg hover:scale-110">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 mr-2.5 transition-transform group-hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Peminjaman Lab
                    </a>
                    
                    <!-- Button Peminjaman Data -->
                    <a href="{{ route('data.index') }}" class="inline-flex items-center px-8 py-3 lg:px-10 lg:py-4 bg-white/20 backdrop-blur-sm text-white border-2 border-white font-bold rounded-full hover:bg-white hover:text-yellow-600 hover:shadow-2xl transition-all duration-300 shadow-xl text-sm lg:text-lg hover:scale-110">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 mr-2.5 transition-transform group-hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                        Peminjaman Data
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Section -->
    <section class="py-8 lg:py-16">
        <div class="container mx-auto px-4 lg:px-8">
            <!-- Week Info -->
            <div class="text-center mb-6 animate-fade-in-up animation-delay-400 relative z-10">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">Jadwal Laboratorium</h2>
                <p id="calWeekLabel" class="text-slate-600">Memuat...</p>
                <div class="relative inline-block">
                    <button id="btnOpenCalendar" class="inline-flex items-center text-sm text-yellow-600 hover:text-yellow-700 font-semibold mt-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Lihat tanggal lain
                    </button>
                    <!-- Mini Calendar Popup -->
                    <div id="miniCalendarPopup" class="hidden absolute z-[100] mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 p-4 w-[300px]" style="left:50%; transform:translateX(-50%);">
                        <div class="flex items-center justify-between mb-3">
                            <button id="calPrevMonth" class="p-1 hover:bg-slate-100 rounded-lg transition">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span id="calMonthLabel" class="font-bold text-slate-800"></span>
                            <button id="calNextMonth" class="p-1 hover:bg-slate-100 rounded-lg transition">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-500 mb-1">
                            <div>SN</div><div>SL</div><div>RB</div><div>KM</div><div>JM</div><div>SB</div><div>MG</div>
                        </div>
                        <div id="calDaysGrid" class="grid grid-cols-7 gap-1 text-center text-sm"></div>
                        <div class="mt-3 pt-3 border-t border-slate-200 flex justify-between">
                            <button id="calToday" class="text-xs text-yellow-600 hover:text-yellow-700 font-semibold">Hari Ini</button>
                            <button id="calClose" class="text-xs text-slate-500 hover:text-slate-700 font-semibold">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 animate-fade-in-up animation-delay-500">
                
                <!-- Day Tabs -->
                <div class="border-b border-slate-200 overflow-x-auto sticky top-16 lg:top-20 z-40 bg-white shadow-sm">
                    <div id="dayTabsContainer" class="flex min-w-max lg:min-w-0 lg:justify-center">
                        <!-- Tabs rendered by JS -->
                    </div>
                </div>

                <!-- Desktop: Calendar Grid View -->
                <div id="calendarGridContainer" class="hidden lg:block">
                    <div id="calendarGrid" class="relative" style="min-width: 800px;">
                        <!-- Grid rendered by JS -->
                        <div class="text-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-500 mx-auto"></div>
                            <p class="text-slate-500 mt-4">Memuat jadwal...</p>
                        </div>
                    </div>
                </div>

                <!-- Mobile: Card View Fallback -->
                <div id="mobileScheduleContainer" class="lg:hidden p-4 space-y-3 bg-slate-50">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-yellow-500 mx-auto"></div>
                        <p class="text-slate-500 mt-3 text-sm">Memuat jadwal...</p>
                    </div>
                </div>

                <!-- Legend -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 text-sm text-slate-600">
                    <div class="font-semibold mb-2">Keterangan Warna:</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-yellow-300"></span>
                            <span><strong>Kuning:</strong> Perkuliahan Tetap</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-indigo-500"></span>
                            <span><strong>Ungu:</strong> Perkuliahan Tidak Tetap</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-emerald-500"></span>
                            <span><strong>Hijau:</strong> Non-Perkuliahan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Download PDF Section -->
    <section class="py-8 lg:py-16 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-full mb-4 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">Download Ulang Dokumen</h2>
                    <p class="text-slate-600">Masukkan kode booking untuk download ulang dokumen peminjaman</p>
                </div>

                <!-- Download Form -->
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 lg:p-8">
                    <form onsubmit="return handleDownloadSubmit(event)">
                        <div class="space-y-4">
                            <div>
                                <label for="booking_token" class="block text-sm font-semibold text-slate-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Kode Booking
                                </label>
                                <input 
                                    type="text" 
                                    id="booking_token" 
                                    name="booking_token"
                                    class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all text-center font-mono text-lg tracking-wider uppercase"
                                    placeholder="Contoh: ABC123XYZ456"
                                    required
                                    maxlength="50"
                                >
                            </div>

                            <button 
                                type="submit" 
                                class="w-full py-3.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center group"
                            >
                                <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Dokumen PDF
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="bg-yellow-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Laporkan Masalah</h3>
                        <p class="text-sm text-gray-500">Sampaikan masalah atau feedback Anda. Tim kami akan meninjau laporan Anda.</p>
                    </div>
                </div>
                <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('feedback.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <!-- Judul Laporan -->
                <div>
                    <label for="feedback_title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Laporan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="feedback_title" 
                        name="title" 
                        placeholder="Masukkan judul laporan"
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition"
                    >
                </div>

                <!-- Detail Laporan -->
                <div>
                    <label for="feedback_detail" class="block text-sm font-semibold text-gray-700 mb-2">
                        Detail Laporan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="feedback_detail" 
                        name="detail" 
                        rows="5" 
                        placeholder="Jelaskan detail laporan Anda..."
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition resize-none"
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-2">
                    <button 
                        type="submit"
                        class="px-6 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-800 text-slate-400 py-5">
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
            <p class="text-sm lg:text-base">&copy; {{ date('Y') }} Laboratorium Digital FEB UNDIP. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
    (function() {
        // ==================== CONFIG ====================
        const TIME_START = 5;  // 05:00
        const TIME_END = 23;   // 23:00
        const SLOT_MINUTES = 10;
        const TOTAL_SLOTS = ((TIME_END - TIME_START) * 60) / SLOT_MINUTES; // 108 slots
        const ROW_HEIGHT = 10; // px per 10-min slot
        const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const DAYS_ID = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        // ==================== STATE ====================
        let weekData = null;      // Full week schedule data from API
        let selectedDay = null;   // Currently selected day name
        let selectedDate = null;  // Currently selected date string
        let calViewMonth = null;  // Mini-calendar current month (Date)
        let allLabs = [];         // Labs list

        // ==================== COLOR MAPPING ====================
        const TYPE_COLORS = {
            'perkuliahan_tetap':       { bg: 'bg-yellow-300', accent: 'bg-yellow-600', border: 'border-yellow-500', shadow: 'shadow-yellow-100', text: 'text-yellow-900' },
            'perkuliahan_tidak_tetap': { bg: 'bg-indigo-400', accent: 'bg-indigo-700', border: 'border-indigo-500', shadow: 'shadow-indigo-100', text: 'text-indigo-900' },
            'non_perkuliahan':         { bg: 'bg-emerald-400', accent: 'bg-emerald-700', border: 'border-emerald-500', shadow: 'shadow-emerald-100', text: 'text-emerald-900' }
        };

        // ==================== HELPERS ====================
        function formatTime(t) {
            if (!t) return '';
            // API now returns 'HH:mm' strings directly
            if (typeof t === 'string') {
                const match = t.match(/(\d{1,2}):(\d{2})/);
                if (match) return match[1].padStart(2,'0') + ':' + match[2];
            }
            return '';
        }

        function timeToMinutes(t) {
            const str = formatTime(t);
            if (!str) return 0;
            const [h, m] = str.split(':').map(Number);
            return h * 60 + m;
        }

        function dateStr(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
        }

        // ==================== API ====================
        function loadSchedules(targetDate) {
            let url = `{{ route('schedules.week') }}`;
            if (targetDate) {
                url += `?date=${targetDate}`;
            }

            // Show loading
            document.getElementById('calendarGrid').innerHTML = `
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-500 mx-auto"></div>
                    <p class="text-slate-500 mt-4">Memuat jadwal...</p>
                </div>`;
            document.getElementById('mobileScheduleContainer').innerHTML = `
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-yellow-500 mx-auto"></div>
                    <p class="text-slate-500 mt-3 text-sm">Memuat jadwal...</p>
                </div>`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    weekData = data;
                    allLabs = data.labs || [];

                    // Update week label
                    document.getElementById('calWeekLabel').innerHTML = 
                        data.week_label + ' <span class="text-xs text-slate-400 ml-1">(WIB)</span>';

                    // Build day tabs
                    renderDayTabs(data);

                    // Select the target day
                    if (targetDate) {
                        const target = new Date(targetDate + 'T00:00:00');
                        const dayOfWeek = target.getDay(); // 0=Sun, 1=Mon, ...
                        const idx = dayOfWeek === 0 ? 5 : dayOfWeek - 1; // Map to DAYS_ID index
                        if (idx >= 0 && idx < 6) {
                            selectDay(DAYS_ID[idx], targetDate);
                        } else {
                            selectDay(DAYS_ID[0], data.week_start);
                        }
                    } else {
                        // Default: select today if within week, else Monday
                        const today = new Date();
                        const todayStr = dateStr(today);
                        if (todayStr >= data.week_start && todayStr <= data.week_end) {
                            const dow = today.getDay();
                            const idx = dow === 0 ? -1 : dow - 1;
                            if (idx >= 0 && idx < 6) {
                                selectDay(DAYS_ID[idx], todayStr);
                            } else {
                                selectDay(DAYS_ID[0], data.week_start);
                            }
                        } else {
                            selectDay(DAYS_ID[0], data.week_start);
                        }
                    }
                })
                .catch(err => {
                    console.error('Error loading schedules:', err);
                    document.getElementById('calendarGrid').innerHTML = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-orange-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-orange-600 font-semibold">Gagal memuat jadwal</p>
                        </div>`;
                });
        }

        // ==================== DAY TABS ====================
        function renderDayTabs(data) {
            const container = document.getElementById('dayTabsContainer');
            const ws = new Date(data.week_start + 'T00:00:00');
            let html = '';
            DAYS_ID.forEach((day, idx) => {
                const d = new Date(ws);
                d.setDate(d.getDate() + idx);
                const dayDate = dateStr(d);
                const dd = d.getDate();
                const mm = d.getMonth() + 1;
                html += `
                    <button data-day="${day}" data-date="${dayDate}"
                        class="day-tab px-6 lg:px-8 py-4 font-semibold text-sm lg:text-base transition border-b-2 border-transparent text-slate-600 hover:text-yellow-600 hover:bg-yellow-50">
                        <div class="flex flex-col items-center">
                            <span>${day}</span>
                            <span class="text-xs font-normal text-slate-400 mt-1">${String(dd).padStart(2,'0')}/${String(mm).padStart(2,'0')}</span>
                        </div>
                    </button>`;
            });
            container.innerHTML = html;

            // Bind click events
            container.querySelectorAll('.day-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    selectDay(this.dataset.day, this.dataset.date);
                });
            });
        }

        function selectDay(day, date) {
            selectedDay = day;
            selectedDate = date;

            // Update tab styles
            document.querySelectorAll('.day-tab').forEach(tab => {
                if (tab.dataset.day === day) {
                    tab.classList.add('border-yellow-500', 'text-yellow-600', 'bg-yellow-50');
                    tab.classList.remove('border-transparent', 'text-slate-600');
                } else {
                    tab.classList.remove('border-yellow-500', 'text-yellow-600', 'bg-yellow-50');
                    tab.classList.add('border-transparent', 'text-slate-600');
                }
            });

            // Filter schedules for selected day (exclude pribadi)
            const daySchedules = (weekData.schedules || []).filter(s => {
                if (s.date !== date) return false;
                if (s.booking_type === 'pribadi') return false;
                return true;
            });

            renderGrid(daySchedules);
            renderMobileCards(daySchedules);
        }

        // ==================== GRID RENDERING ====================
        function renderGrid(schedules) {
            const container = document.getElementById('calendarGrid');
            if (allLabs.length === 0) {
                container.innerHTML = '<div class="text-center py-12 text-slate-500">Tidak ada lab tersedia</div>';
                return;
            }

            const totalHeight = TOTAL_SLOTS * ROW_HEIGHT;
            const labCount = allLabs.length;

            // Build HTML
            let html = '';

            // ---- Header row: Time col + Lab cols ----
            // Calculate sticky offset dynamically from actual element heights
            const navEl = document.querySelector('nav.sticky');
            const tabsEl = document.querySelector('#dayTabsContainer')?.closest('.sticky');
            const stickyTop = (navEl ? navEl.offsetHeight : 0) + (tabsEl ? tabsEl.offsetHeight : 0);
            html += `<div class="flex border-b-2 border-slate-300 bg-slate-800 text-white sticky z-30" style="top:${stickyTop}px">`;
            html += '<div class="flex-shrink-0" style="width:70px;"></div>';
            allLabs.forEach(lab => {
                html += `<div class="flex-1 text-center py-3 px-2 font-bold text-sm border-l border-slate-600">${lab.name}</div>`;
            });
            html += '</div>';

            // ---- Grid body ----
            html += `<div class="flex relative" style="height:${totalHeight}px;">`;

            // Time labels column
            html += '<div class="flex-shrink-0 relative bg-slate-50 border-r border-slate-300" style="width:70px;">';
            for (let h = TIME_START; h < TIME_END; h++) {
                // HH:00 label
                const topHour = ((h - TIME_START) * 60 / SLOT_MINUTES) * ROW_HEIGHT;
                html += `<div class="absolute text-xs font-semibold text-slate-500 pr-2 text-right w-full" style="top:${topHour}px; line-height:${ROW_HEIGHT}px;">${String(h).padStart(2,'0')}:00</div>`;
                // HH:30 label
                const topHalf = topHour + (30 / SLOT_MINUTES) * ROW_HEIGHT;
                html += `<div class="absolute text-[10px] text-slate-400 pr-2 text-right w-full" style="top:${topHalf}px; line-height:${ROW_HEIGHT}px;">${String(h).padStart(2,'0')}:30</div>`;
            }
            html += '</div>';

            // Lab columns
            allLabs.forEach((lab, labIdx) => {
                html += `<div class="flex-1 relative border-l border-slate-200" data-lab-id="${lab.id}">`;

                // Horizontal gridlines: 60min=solid, 30min=dashed, 10min=dotted
                for (let slot = 0; slot <= TOTAL_SLOTS; slot++) {
                    const mins = slot * SLOT_MINUTES;
                    const topPx = slot * ROW_HEIGHT;
                    if (mins % 60 === 0) {
                        html += `<div class="absolute w-full border-t border-slate-200" style="top:${topPx}px;"></div>`;
                    } else if (mins % 30 === 0) {
                        html += `<div class="absolute w-full border-t border-dashed border-slate-100" style="top:${topPx}px;"></div>`;
                    } else {
                        html += `<div class="absolute w-full" style="top:${topPx}px; border-top: 1px dotted #e2e8f0;"></div>`;
                    }
                }

                // Schedule blocks for this lab
                const labSchedules = schedules.filter(s => s.lab_id === lab.id);
                labSchedules.forEach(s => {
                    const startMin = timeToMinutes(s.start_time);
                    const endMin = timeToMinutes(s.end_time);
                    const startOffset = startMin - (TIME_START * 60);
                    const duration = endMin - startMin;

                    if (startOffset < 0 || duration <= 0) return;

                    const topPx = (startOffset / SLOT_MINUTES) * ROW_HEIGHT;
                    const heightPx = (duration / SLOT_MINUTES) * ROW_HEIGHT;
                    const colors = TYPE_COLORS[s.booking_type] || TYPE_COLORS['perkuliahan_tetap'];
                    const startTimeStr = formatTime(s.start_time);
                    const endTimeStr = formatTime(s.end_time);
                    const isKuliah = s.booking_type === 'perkuliahan_tetap' || s.booking_type === 'perkuliahan_tidak_tetap';
                    const lecturerDisplay = (isKuliah && s.lecturer) ? s.lecturer : '';

                 // Icons
                const iconClock = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                const iconUser = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>`;

                // Build tooltip
                const tooltipParts = [s.course || '-'];
                if (lecturerDisplay) tooltipParts.push(lecturerDisplay);
                tooltipParts.push(startTimeStr + ' - ' + endTimeStr);

                html += `
                    <div class="absolute left-1 right-1 ${colors.bg} rounded-lg shadow-sm border border-slate-200/50 overflow-hidden cursor-pointer hover:shadow-md hover:z-20 transition-all duration-200 group"
                            style="top:${topPx}px; height:${heightPx}px; z-index:5;"
                            title="${tooltipParts.join('\n')}">
                        
                        <!-- Accent Band -->
                        <div class="absolute top-0 bottom-0 left-0 w-2 ${colors.accent}"></div>
                        
                        <!-- Content -->
                        <div class="pl-3 pr-2 py-1.5 h-full flex flex-col justify-start">
                            <!-- Title (Primary) -->
                            <div class="text-xs font-bold ${colors.text} leading-tight truncate mb-0.5">${s.course || '-'}</div>
                            
                            <!-- Time (Secondary) -->
                            ${heightPx > 35 ? `
                            <div class="flex items-center gap-1.5 text-[10px] font-medium ${colors.text} leading-none mb-0.5 opacity-90">
                                ${iconClock}
                                <span class="truncate">${startTimeStr} - ${endTimeStr}</span>
                            </div>` : ''}

                            <!-- Lecturer (Tertiary) -->
                            ${heightPx > 50 && lecturerDisplay ? `
                            <div class="flex items-center gap-1.5 text-[10px] ${colors.text} leading-none opacity-80">
                                ${iconUser}
                                <span class="truncate">${lecturerDisplay}</span>
                            </div>` : ''}
                        </div>
                    </div>`;
                });

                html += '</div>';
            });

            html += '</div>';

            // Empty state
            if (schedules.length === 0) {
                html += `
                    <div class="absolute inset-0 flex items-center justify-center" style="top:50px; pointer-events:none;">
                        <div class="text-center pointer-events-auto">
                            <svg class="w-16 h-16 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-slate-400 font-semibold text-lg">Tidak ada jadwal hari ini</p>
                        </div>
                    </div>`;
            }

            container.innerHTML = html;
        }

        // ==================== MOBILE CARDS ====================
        function renderMobileCards(schedules) {
            const container = document.getElementById('mobileScheduleContainer');

            if (schedules.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 px-4">
                        <div class="bg-white rounded-full p-4 inline-flex mb-4 shadow-sm">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-slate-500 font-medium">Tidak ada jadwal hari ini</p>
                    </div>`;
                return;
            }

            let html = '';
            schedules.sort((a, b) => timeToMinutes(a.start_time) - timeToMinutes(b.start_time));
            schedules.forEach(s => {
                const colors = TYPE_COLORS[s.booking_type] || TYPE_COLORS['perkuliahan_tetap'];
                const startTime = formatTime(s.start_time);
                const endTime = formatTime(s.end_time);
                const isKuliah = s.booking_type === 'perkuliahan_tetap' || s.booking_type === 'perkuliahan_tidak_tetap';

                html += `
                    <div class="bg-white rounded-xl border p-4 shadow-sm hover:shadow-md transition-all border-l-4 ${colors.border} ${colors.shadow || 'shadow-slate-100'}">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded ${colors.bg} ${colors.text}">${s.lab}</span>
                            </div>
                            <div class="flex items-center text-slate-700 font-bold text-sm">
                                <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                ${startTime} - ${endTime}
                            </div>
                        </div>
                        <h4 class="font-bold text-slate-900 text-base mb-1">${s.course || '-'}</h4>
                        ${isKuliah && s.lecturer ? `<div class="text-xs text-slate-600 font-medium">Dosen: ${s.lecturer}</div>` : ''}
                        ${s.komting ? `<div class="text-xs text-slate-500 mt-1">${isKuliah ? 'Komting' : 'PIC'}: ${s.komting}</div>` : ''}
                        ${s.student_count ? `<div class="text-xs text-slate-500 mt-0.5">${s.student_count} peserta</div>` : ''}
                    </div>`;
            });
            container.innerHTML = html;
        }

        // ==================== MINI CALENDAR ====================
        function openCalendar() {
            const popup = document.getElementById('miniCalendarPopup');
            popup.classList.toggle('hidden');
            if (!popup.classList.contains('hidden')) {
                // Set current month based on selected date
                calViewMonth = selectedDate ? new Date(selectedDate + 'T00:00:00') : new Date();
                calViewMonth.setDate(1);
                renderCalendar();
            }
        }

        function renderCalendar() {
            const year = calViewMonth.getFullYear();
            const month = calViewMonth.getMonth();
            document.getElementById('calMonthLabel').textContent = MONTHS_ID[month] + ' ' + year;

            const firstDay = new Date(year, month, 1);
            let startDow = firstDay.getDay(); // 0=Sun
            startDow = startDow === 0 ? 6 : startDow - 1; // Convert to Mon=0
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            const today = new Date();
            const todayStr = dateStr(today);
            const grid = document.getElementById('calDaysGrid');
            let html = '';

            // Empty cells before first day
            for (let i = 0; i < startDow; i++) {
                html += '<div></div>';
            }

            // Day cells
            for (let d = 1; d <= daysInMonth; d++) {
                const cellDate = new Date(year, month, d);
                const cellStr = dateStr(cellDate);
                const isToday = cellStr === todayStr;
                const isSelected = cellStr === selectedDate;
                const isSunday = cellDate.getDay() === 0;

                let classes = 'py-1.5 rounded-lg cursor-pointer transition text-sm ';
                if (isSelected) {
                    classes += 'bg-yellow-500 text-white font-bold shadow-md ';
                } else if (isToday) {
                    classes += 'bg-yellow-100 text-yellow-800 font-bold ring-2 ring-yellow-400 ';
                } else if (isSunday) {
                    classes += 'text-slate-300 cursor-default ';
                } else {
                    classes += 'hover:bg-slate-100 text-slate-700 ';
                }

                html += `<div class="${classes}" data-date="${cellStr}" ${isSunday ? '' : `onclick="pickDate('${cellStr}')"`}>${d}</div>`;
            }

            grid.innerHTML = html;
        }

        // Global function for onclick
        window.pickDate = function(d) {
            document.getElementById('miniCalendarPopup').classList.add('hidden');
            loadSchedules(d);
        };

        // ==================== INIT ====================
        document.addEventListener('DOMContentLoaded', function() {
            // Load current week
            loadSchedules(null);

            // Mini calendar toggle
            document.getElementById('btnOpenCalendar').addEventListener('click', function(e) {
                e.stopPropagation();
                openCalendar();
            });
            document.getElementById('calClose').addEventListener('click', () => {
                document.getElementById('miniCalendarPopup').classList.add('hidden');
            });
            document.getElementById('calToday').addEventListener('click', () => {
                document.getElementById('miniCalendarPopup').classList.add('hidden');
                loadSchedules(dateStr(new Date()));
            });
            document.getElementById('calPrevMonth').addEventListener('click', () => {
                calViewMonth.setMonth(calViewMonth.getMonth() - 1);
                renderCalendar();
            });
            document.getElementById('calNextMonth').addEventListener('click', () => {
                calViewMonth.setMonth(calViewMonth.getMonth() + 1);
                renderCalendar();
            });

            // Close calendar when clicking outside
            document.addEventListener('click', function(e) {
                const popup = document.getElementById('miniCalendarPopup');
                const btn = document.getElementById('btnOpenCalendar');
                if (!popup.contains(e.target) && !btn.contains(e.target)) {
                    popup.classList.add('hidden');
                }
            });
        });

        // ==================== DOWNLOAD PDF ====================
        window.handleDownloadSubmit = function(event) {
            event.preventDefault();
            const token = document.getElementById('booking_token').value.trim();
            if (!token) { alert('❌ Masukkan kode booking!'); return false; }
            window.open(`/booking/print/${token}`, '_blank');
            return false;
        };

        // ==================== FEEDBACK MODAL ====================
        window.openFeedbackModal = function() {
            const modal = document.getElementById('feedbackModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };
        window.closeFeedbackModal = function() {
            const modal = document.getElementById('feedbackModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            document.getElementById('feedback_title').value = '';
            document.getElementById('feedback_detail').value = '';
        };
        document.getElementById('feedbackModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeFeedbackModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeFeedbackModal();
        });
    })();
    </script>

</body>
</html>
