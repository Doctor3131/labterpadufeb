<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal Laboratorium - Lab Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-3 sm:px-4 md:px-6 py-3 md:py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('landing') }}">
                        <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-10 sm:h-12 md:h-16 w-auto object-contain">
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('landing') }}" class="text-gray-600 hover:text-yellow-600 font-semibold transition-all duration-200 hover:scale-105 text-base">Beranda</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-yellow-600 font-semibold transition-all duration-200 hover:scale-105 text-base">Dashboard</a>
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 hover:scale-105 hover:shadow-lg text-base">
                            Ajukan
                        </a>
                    @else
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 hover:scale-105 hover:shadow-lg text-base">
                            Ajukan
                        </a>
                        <a href="{{ route('login') }}" class="text-yellow-600 hover:text-yellow-700 font-semibold border-2 border-yellow-500 px-4 py-2 rounded-lg hover:bg-yellow-50 transition-all duration-200 hover:scale-105 hover:shadow-md text-base">
                            Login
                        </a>
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="mobileMenuButton" class="md:hidden p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu Dropdown -->
            <div id="mobileMenu" class="hidden md:hidden mt-4 pb-2 border-t border-gray-200 pt-4">
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('landing') }}" class="text-gray-600 hover:text-yellow-600 font-semibold py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors text-base">
                        Beranda
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-yellow-600 font-semibold py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors text-base">
                            Dashboard
                        </a>
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg font-semibold text-center transition-all text-base">
                            Ajukan Peminjaman
                        </a>
                    @else
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg font-semibold text-center transition-all text-base">
                            Ajukan Peminjaman
                        </a>
                        <a href="{{ route('login') }}" class="text-yellow-600 hover:text-yellow-700 font-semibold border-2 border-yellow-500 px-4 py-3 rounded-lg hover:bg-yellow-50 text-center transition-all text-base">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 md:px-6 py-6 md:py-12">
        <!-- Header -->
        <div class="text-center mb-6 md:mb-10">
            <h1 class="text-2xl md:text-4xl font-bold text-gray-800 mb-2 md:mb-3">Jadwal Laboratorium</h1>
            <p class="text-sm md:text-base text-gray-600">Lihat jadwal penggunaan laboratorium</p>
        </div>

        <!-- Week Navigation -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 md:gap-0">
                <button id="prevWeek" class="w-full md:w-auto flex justify-center md:justify-start items-center space-x-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors order-2 md:order-1">
                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="pointer-events-none">Sebelumnya</span>
                </button>

                <div class="text-center order-1 md:order-2">
                    <div class="text-xs md:text-sm text-gray-500">Periode</div>
                    <div id="weekLabel" class="text-lg md:text-xl font-bold text-gray-800">Loading...</div>
                    <div class="text-[10px] md:text-xs text-gray-400 mt-1">WIB (Asia/Jakarta)</div>
                </div>

                <button id="nextWeek" class="w-full md:w-auto flex justify-center md:justify-end items-center space-x-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors order-3">
                    <span class="pointer-events-none">Berikutnya</span>
                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <div class="flex justify-center mt-4" id="currentWeekButtonContainer">
                <button id="currentWeek" class="px-4 py-2 text-sm bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors">
                    Kembali ke Minggu Ini
                </button>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div id="scheduleContainer" class="p-6">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-500 mx-auto"></div>
                    <p class="text-gray-500 mt-4">Memuat jadwal...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentWeekOffset = 0;

        function loadSchedules(weekOffset) {
            const container = document.getElementById('scheduleContainer');
            const weekLabel = document.getElementById('weekLabel');

            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-500 mx-auto"></div>
                    <p class="text-gray-500 mt-4">Memuat jadwal...</p>
                </div>
            `;

            fetch(`{{ route('schedules.week') }}?week_offset=${weekOffset}`)
                .then(response => response.json())
                .then(data => {
                    // Update week label and offset
                    console.log('Week data:', data);
                    console.log('Updating label from:', weekLabel.textContent, 'to:', data.week_label);
                    console.log('Updating offset from:', currentWeekOffset, 'to:', weekOffset);
                    weekLabel.textContent = data.week_label;
                    currentWeekOffset = weekOffset;

                    // Show/Hide "Kembali ke Minggu Ini" button
                    const currentWeekButton = document.getElementById('currentWeekButtonContainer');
                    if (weekOffset === 0) {
                        currentWeekButton.style.display = 'none';
                    } else {
                        currentWeekButton.style.display = 'flex';
                    }

                    if (data.schedules.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-500 text-lg">Tidak ada jadwal untuk minggu ini</p>
                            </div>
                        `;
                        return;
                    }

                    // Group by date
                    const groupedSchedules = {};
                    data.schedules.forEach(schedule => {
                        if (!groupedSchedules[schedule.date]) {
                            groupedSchedules[schedule.date] = {
                                date: schedule.date,
                                date_formatted: schedule.date_formatted,
                                schedules: []
                            };
                        }
                        groupedSchedules[schedule.date].schedules.push(schedule);
                    });

                    let html = '<div class="space-y-6">';
                    
                    Object.values(groupedSchedules).forEach(day => {
                        html += `
                            <div class="border-l-4 border-yellow-500 pl-4">
                                <h3 class="text-xl font-bold text-gray-800 mb-3">${day.date_formatted}</h3>
                                <div class="space-y-3">
                        `;

                        day.schedules.forEach(schedule => {
                            // Format time to HH:MM
                            const formatTime = (time) => {
                                if (!time) return '';
                                // If time is already in HH:MM format, return as is
                                if (time.length <= 5) return time;
                                // Parse datetime and extract time
                                const date = new Date(time);
                                return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
                            };
                            
                            const startTime = formatTime(schedule.start_time);
                            const endTime = formatTime(schedule.end_time);
                            
                            // Get lab badge color based on booking type
                            const getLabBadgeColor = (type) => {
                                const colors = {
                                    'perkuliahan_tetap': 'bg-yellow-100 text-yellow-800',
                                    'perkuliahan_tidak_tetap': 'bg-indigo-100 text-indigo-800',
                                    'non_perkuliahan': 'bg-emerald-100 text-emerald-800',
                                    'pribadi': 'bg-orange-100 text-orange-800'
                                };
                                return colors[type] || 'bg-yellow-100 text-yellow-800';
                            };
                            
                            html += `
                                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span class="inline-block px-3 py-1 ${getLabBadgeColor(schedule.booking_type)} text-sm font-semibold rounded">${schedule.lab}</span>
                                                <span class="text-gray-600 font-medium">${startTime} - ${endTime}</span>
                                            </div>
                                            <h4 class="text-lg font-semibold text-gray-800">${schedule.course}</h4>
                                            <p class="text-gray-600 text-sm mt-1">Dosen: ${schedule.lecturer}</p>
                                            ${schedule.komting ? `<p class="text-gray-500 text-sm">${(schedule.booking_type === 'pribadi' || schedule.booking_type === 'non_perkuliahan') ? 'Peminjam' : 'Komting'}: ${schedule.komting}</p>` : ''}
                                            ${schedule.student_count ? `<p class="text-gray-500 text-sm">Jumlah Mahasiswa: ${schedule.student_count} orang</p>` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading schedules:', error);
                    container.innerHTML = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-orange-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-orange-600 text-lg font-semibold mb-2">Tidak ada jadwal di luar semester</p>
                        </div>
                    `;
                });
        }

        // Event listeners
        document.getElementById('prevWeek').addEventListener('click', () => {
            loadSchedules(currentWeekOffset - 1);
        });

        document.getElementById('nextWeek').addEventListener('click', () => {
            loadSchedules(currentWeekOffset + 1);
        });

        document.getElementById('currentWeek').addEventListener('click', () => {
            loadSchedules(0);
        });

        // Mobile menu toggle
        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

        // Load current week on page load
        loadSchedules(0);
    </script>
</body>
</html>
