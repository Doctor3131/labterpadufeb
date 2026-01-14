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
            <div class="flex items-center justify-between h-20">
                <!-- Left: Brand -->
                <a href="{{ route('landing') }}" class="flex items-center space-x-3 group cursor-pointer">
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-14 lg:h-16 w-auto object-contain group-hover:scale-105 transition-all duration-300">
                </a>

                <!-- Right: Navigation Links -->
                <div class="flex items-center gap-3 lg:gap-4 ml-4">
                    <!-- Desktop Menu -->
                    <a href="{{ route('schedules.index') }}" class="hidden lg:inline-flex items-center px-4 py-2 text-yellow-600 font-semibold hover:text-yellow-700 hover:bg-yellow-50 rounded-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Jadwal
                    </a>

                    <!-- Login/Dashboard Button -->
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 lg:px-6 py-2 border-2 border-yellow-500 text-yellow-600 font-semibold rounded-lg hover:bg-yellow-500 hover:text-white hover:shadow-lg hover:scale-105 transition-all duration-300 text-xs sm:text-sm lg:text-base whitespace-nowrap">
                            Dashboard Asisten
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 lg:px-6 py-2 border-2 border-yellow-500 text-yellow-600 font-semibold rounded-lg hover:bg-yellow-500 hover:text-white hover:shadow-lg hover:scale-105 transition-all duration-300 text-xs sm:text-sm lg:text-base whitespace-nowrap">
                            Login Asisten
                        </a>
                    @endauth

                    <!-- Mobile Hamburger Menu -->
                    <div class="relative lg:hidden">
                        <button id="mobile-menu-btn" class="p-2 text-slate-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="mobile-menu-dropdown" class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 hidden transform origin-top-right transition-all duration-200 z-50">
                            <div class="py-2">
                                <a href="{{ route('schedules.index') }}" class="flex items-center px-4 py-3 text-slate-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors">
                                    <svg class="w-5 h-5 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-medium">Lihat Jadwal</span>
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="{{ route('booking.create') }}" class="flex items-center px-4 py-3 text-slate-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors">
                                    <svg class="w-5 h-5 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="font-medium">Ajukan Peminjaman</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-yellow-500 text-white overflow-hidden">
        {{-- <div class="absolute inset-0 bg-grid-white/10"></div> --}}
        <div class="container mx-auto px-4 lg:px-8 py-12 lg:py-24 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl lg:text-5xl font-bold mb-3 lg:mb-6 leading-tight drop-shadow-md">
                    Jadwal & Manajemen<br class="hidden lg:block"/> Laboratorium Terpadu
                </h2>
                <p class="text-base lg:text-xl text-yellow-50 max-w-2xl mx-auto drop-shadow mb-6 lg:mb-10">
                    Pantau jadwal praktikum dan ajukan penggunaan fasilitas laboratorium secara terintegrasi
                </p>
                
                <!-- CTA Button - Center untuk semua device -->
                <div class="flex justify-center">
                    <a href="{{ route('booking.create') }}" class="inline-flex items-center px-8 py-3 lg:px-12 lg:py-5 bg-white text-yellow-600 font-bold rounded-full hover:bg-yellow-50 hover:shadow-2xl transition-all duration-300 shadow-xl text-sm lg:text-lg hover:scale-105">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajukan Peminjaman
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Schedule Section -->
    <section class="py-8 lg:py-16">
        <div class="container mx-auto px-4 lg:px-8">
            <!-- Week Info -->
            <div class="text-center mb-6">
                <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">Jadwal Minggu Ini</h2>
                <p class="text-slate-600">
                    {{ $startOfWeek->isoFormat('D MMMM') }} - {{ $startOfWeek->copy()->endOfWeek()->isoFormat('D MMMM Y') }}
                    <span class="text-xs text-slate-400 ml-2">(WIB)</span>
                </p>
                <a href="{{ route('schedules.index') }}" class="inline-flex items-center text-sm text-yellow-600 hover:text-yellow-700 font-semibold mt-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lihat jadwal minggu lain
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                
                <!-- Tabs -->
                <div class="border-b border-slate-200 overflow-x-auto">
                    <div class="flex min-w-max lg:min-w-0 lg:justify-center">
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                        <button 
                            onclick="showSchedule('{{ $day }}')" 
                            id="tab-{{ $day }}"
                            class="schedule-tab px-6 lg:px-8 py-4 font-semibold text-sm lg:text-base transition border-b-2 {{ $day === 'Senin' ? 'border-yellow-500 text-yellow-600 bg-yellow-50' : 'border-transparent text-slate-600 hover:text-yellow-600 hover:bg-yellow-50' }}">
                            <div class="flex flex-col items-center">
                                <span>{{ $day }}</span>
                                <span class="text-xs font-normal text-slate-400 mt-1">
                                    {{ isset($schedules[$day]['date']) ? \Carbon\Carbon::parse($schedules[$day]['date'])->format('d/m') : '-' }}
                                </span>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                    <div id="schedule-{{ $day }}" class="schedule-content {{ $day !== 'Senin' ? 'hidden' : '' }}">
                        <!-- Date Header -->
                        <div class="bg-gradient-to-r from-yellow-50 to-white px-6 py-3 border-b">
                            <p class="text-sm font-semibold text-slate-700">
                                📅 {{ $schedules[$day]['date_formatted'] ?? $day }}
                            </p>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden lg:block">
                            <table class="w-full min-w-[800px]">
                                <thead class="bg-slate-100 text-slate-700 text-sm">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-semibold">Waktu</th>
                                        <th class="px-6 py-4 text-left font-semibold">Ruang</th>
                                        <th class="px-6 py-4 text-left font-semibold">Kegiatan / Mata Kuliah</th>
                                        <th class="px-6 py-4 text-left font-semibold">Dosen / PIC</th>
                                        <th class="px-6 py-4 text-left font-semibold">Info</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm lg:text-base">
                                    @forelse(($schedules[$day]['items'] ?? []) as $item)
                                    <tr class="border-t border-slate-200 hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($item['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($item['end_time'])->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <x-room-badge :lab="$item['lab']" :type="$item['booking_type'] ?? 'perkuliahan_tetap'" class="text-sm" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-slate-700 font-medium">{{ $item['course'] }}</div>
                                            @if($item['type'] === 'booking' || isset($item['booking_type']))
                                                <x-booking-badge :type="$item['booking_type'] ?? 'perkuliahan_tetap'" class="mt-1" />
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-slate-600">{{ $item['lecturer'] }}</div>
                                            @if($item['komting'])
                                                <div class="text-xs text-slate-400 mt-1">{{ in_array($item['booking_type'], ['pribadi', 'non_perkuliahan']) ? 'Peminjam' : 'Komting' }}: {{ $item['komting'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 text-xs">
                                            @if($item['student_count'])
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    {{ $item['student_count'] }} peserta
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <p class="text-lg font-medium">Tidak ada jadwal untuk hari {{ $day }}</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden p-4 space-y-3 bg-slate-50">
                            @forelse(($schedules[$day]['items'] ?? []) as $item)
                            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-all">
                                <!-- Header: Time & Room -->
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center text-slate-800 font-bold text-sm bg-slate-100 px-2.5 py-1 rounded-lg">
                                        <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($item['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($item['end_time'])->format('H:i') }}
                                    </div>
                                    <x-room-badge :lab="$item['lab']" :type="$item['booking_type'] ?? 'perkuliahan_tetap'" class="text-xs shadow-sm" />
                                </div>

                                <!-- Body: Course Name -->
                                <div class="mb-3">
                                    <h4 class="font-bold text-slate-900 text-base leading-snug mb-1.5">{{ $item['course'] }}</h4>
                                    @if($item['type'] === 'booking' || isset($item['booking_type']))
                                        <div class="inline-block">
                                            <x-booking-badge :type="$item['booking_type'] ?? 'perkuliahan_tetap'" class="text-[10px] px-2 py-0.5" />
                                        </div>
                                    @endif
                                </div>

                                <!-- Footer: Lecturer & Info -->
                                <div class="border-t border-slate-100 pt-3 flex flex-col gap-3">
                                    <!-- Lecturer -->
                                    <div class="flex items-start text-xs text-slate-600">
                                        <svg class="w-4 h-4 mr-2 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="font-medium leading-relaxed">{{ $item['lecturer'] }}</span>
                                    </div>
                                    
                                    <!-- Meta Info (Student Count & PIC) -->
                                    @if($item['student_count'] || $item['komting'])
                                    <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
                                        @if($item['student_count'])
                                            <div class="flex-shrink-0 flex items-center bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100">
                                                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <span class="font-medium text-slate-600">{{ $item['student_count'] }} Peserta</span>
                                            </div>
                                        @endif

                                        @if($item['komting'])
                                            <div class="flex-1 min-w-0 text-right">
                                                <span class="text-slate-400 mr-1">PIC:</span>
                                                <span class="font-medium text-slate-700 break-words leading-tight" title="{{ $item['komting'] }}">
                                                    {{ $item['komting'] }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12 px-4">
                                <div class="bg-white rounded-full p-4 inline-flex mb-4 shadow-sm">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-slate-500 font-medium">Tidak ada jadwal</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Legend / Notice -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 text-sm text-slate-600">
                    <div class="font-semibold mb-2">Keterangan Warna:</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-yellow-500"></span>
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
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded bg-orange-500"></span>
                            <span><strong>Oranye:</strong> Peminjaman Pribadi</span>
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
                                <p class="mt-2 text-xs text-slate-500">
                                    💡 Kode booking ada di halaman sukses setelah mengajukan peminjaman
                                </p>
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

                    <!-- Info Box -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-blue-900 mb-1">Informasi</h3>
                                <p class="text-xs text-blue-800">
                                    Bawa dokumen yang sudah di-print ke kantor Lab Terpadu FEB untuk konfirmasi dan tanda tangan.
                                </p>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-800 text-slate-400 py-5">
        <div class="container mx-auto px-4 lg:px-8 text-center">
            <!-- Title -->
            <p class="text-white text-sm font-medium mb-3">Hubungi Kami</p>
            <!-- Contact Info - Horizontal -->
            <div class="flex flex-wrap items-center justify-center gap-4 lg:gap-6 mb-4 text-sm">
                <!-- Instagram -->
                <a href="https://instagram.com/upkfeb" target="_blank" rel="noopener noreferrer" 
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
            <p class="text-sm lg:text-base">&copy; {{ date('Y') }} Laboratorium Terpadu FEB UNDIP. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for Tabs -->
    <script>
        function showSchedule(day) {
            // Hide all schedules
            document.querySelectorAll('.schedule-content').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Remove active state from all tabs
            document.querySelectorAll('.schedule-tab').forEach(tab => {
                tab.classList.remove('border-yellow-500', 'text-yellow-600', 'bg-yellow-50');
                tab.classList.add('border-transparent', 'text-slate-600');
            });
            
            // Show selected schedule
            document.getElementById('schedule-' + day).classList.remove('hidden');
            
            // Activate selected tab
            const activeTab = document.getElementById('tab-' + day);
            activeTab.classList.add('border-yellow-500', 'text-yellow-600', 'bg-yellow-50');
            activeTab.classList.remove('border-transparent', 'text-slate-600');
        }

        // Handle download form submit
        function handleDownloadSubmit(event) {
            event.preventDefault();
            const token = document.getElementById('booking_token').value.trim();
            
            if (!token) {
                alert('❌ Masukkan kode booking!');
                return false;
            }
            
            // Redirect to print page with token
            window.open(`/booking/print/${token}`, '_blank');
            return false;
        }
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu-dropdown');
            
            if(btn && menu) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });
                
                // Close when clicking outside
                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && !btn.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                });
            }
        });
    </script>

</body>
</html>
