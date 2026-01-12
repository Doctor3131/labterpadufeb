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
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-10 lg:h-12 w-auto object-contain group-hover:scale-105 transition-all duration-300">
                </a>

                <!-- Right: Navigation Links -->
                <div class="flex items-center space-x-3 lg:space-x-4">
                    <a href="{{ route('schedules.index') }}" class="inline-flex items-center px-3 lg:px-4 py-2 text-yellow-600 font-semibold hover:text-yellow-700 hover:bg-yellow-50 rounded-lg hover:-translate-y-1 hover:shadow-md transition-all duration-200">
                        <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Jadwal
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 lg:px-6 py-2 border-2 border-yellow-500 text-yellow-600 font-semibold rounded-lg hover:bg-yellow-500 hover:text-white hover:shadow-lg hover:scale-105 transition-all duration-300 text-sm lg:text-base whitespace-nowrap">
                            Dashboard Asisten Lab
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 lg:px-6 py-2 border-2 border-yellow-500 text-yellow-600 font-semibold rounded-lg hover:bg-yellow-500 hover:text-white hover:shadow-lg hover:scale-105 transition-all duration-300 text-sm lg:text-base whitespace-nowrap">
                            Login Asisten Lab
                        </a>
                    @endauth
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

                        <table class="w-full min-w-[800px]">
                            <thead class="bg-slate-100 text-slate-700 text-sm">
                                <tr>
                                    <th class="px-4 lg:px-6 py-4 text-left font-semibold">Waktu</th>
                                    <th class="px-4 lg:px-6 py-4 text-left font-semibold">Ruang</th>
                                    <th class="px-4 lg:px-6 py-4 text-left font-semibold">Kegiatan / Mata Kuliah</th>
                                    <th class="px-4 lg:px-6 py-4 text-left font-semibold">Dosen / PIC</th>
                                    <th class="px-4 lg:px-6 py-4 text-left font-semibold">Info</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm lg:text-base">
                                @forelse(($schedules[$day]['items'] ?? []) as $item)
                                <tr class="border-t border-slate-200 hover:bg-slate-50 transition">
                                    <td class="px-4 lg:px-6 py-4 font-medium text-slate-900">
                                        {{ \Carbon\Carbon::parse($item['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($item['end_time'])->format('H:i') }}
                                    </td>
                                    <td class="px-4 lg:px-6 py-4">
                                        <x-room-badge :lab="$item['lab']" :type="$item['booking_type'] ?? 'perkuliahan_tetap'" class="text-xs lg:text-sm" />
                                    </td>
                                    <td class="px-4 lg:px-6 py-4">
                                        <div class="text-slate-700 font-medium">{{ $item['course'] }}</div>
                                        @if($item['type'] === 'booking' || isset($item['booking_type']))
                                            <x-booking-badge :type="$item['booking_type'] ?? 'perkuliahan_tetap'" class="mt-1" />
                                        @endif
                                    </td>
                                    <td class="px-4 lg:px-6 py-4">
                                        <div class="text-slate-600">{{ $item['lecturer'] }}</div>
                                        @if($item['komting'])
                                            <div class="text-xs text-slate-400 mt-1">{{ in_array($item['booking_type'], ['pribadi', 'non_perkuliahan']) ? 'Peminjam' : 'Komting' }}: {{ $item['komting'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 lg:px-6 py-4 text-slate-500 text-xs">
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
    <footer class="bg-slate-800 text-slate-400 py-8 lg:py-12">
        <div class="container mx-auto px-4 lg:px-8 text-center">
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
    </script>

</body>
</html>
