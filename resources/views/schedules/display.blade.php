<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="300"> <!-- Auto refresh every 5 minutes -->
    <title>Jadwal Lab - Display Mode</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Hide scrollbar but allow scrolling */
        .scroll-container::-webkit-scrollbar { display: none; }
        .scroll-container { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Current time highlight */
        .current-row {
            background: linear-gradient(90deg, rgba(234, 179, 8, 0.3), rgba(234, 179, 8, 0.1));
            border-left: 4px solid #eab308;
        }
    </style>
</head>
<body class="bg-white text-slate-800 min-h-screen overflow-hidden">
    
    <!-- Header Bar - Yellow like landing page -->
    <header class="bg-yellow-500 py-4 px-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo" class="h-12">
                <div>
                    <h1 class="text-2xl font-bold text-white">Jadwal Laboratorium Terpadu FEB</h1>
                    <p class="text-yellow-100 text-sm">Hari Ini - {{ now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
            </div>
            <div class="text-right text-white">
                <div id="current-time" class="text-4xl font-bold font-mono"></div>
            </div>
        </div>
    </header>

    <!-- Schedule Display - Today Only -->
    <main id="scroll-container" class="scroll-container overflow-y-auto" style="height: calc(100vh - 130px);">
        <div id="scroll-content">
            @php
                $today = now()->timezone('Asia/Jakarta');
                $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                $todayName = $dayNames[$today->format('l')];
                $todaySchedules = $schedules[$todayName]['items'] ?? collect([]);
            @endphp
            
            @if(count($todaySchedules) > 0)
            <div class="p-6">
                <!-- Schedule Table -->
                <table class="w-full border-collapse table-fixed">
                    <thead class="bg-slate-100 text-slate-600 text-sm uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-4 text-left font-semibold border-b-2 border-slate-200" style="width: 150px;">Waktu</th>
                            <th class="px-4 py-4 text-center font-semibold border-b-2 border-slate-200" style="width: 100px;">Ruang</th>
                            <th class="px-4 py-4 text-left font-semibold border-b-2 border-slate-200">Kegiatan / Mata Kuliah</th>
                            <th class="px-4 py-4 text-center font-semibold border-b-2 border-slate-200" style="width: 200px;">Dosen / PIC</th>
                            <th class="px-4 py-4 text-center font-semibold border-b-2 border-slate-200" style="width: 100px;">Peserta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todaySchedules as $item)
                        @php
                            $now = now()->timezone('Asia/Jakarta');
                            $startTime = \Carbon\Carbon::parse($item['start_time']);
                            $endTime = \Carbon\Carbon::parse($item['end_time']);
                            $scheduleStart = $today->copy()->setTimeFrom($startTime);
                            $scheduleEnd = $today->copy()->setTimeFrom($endTime);
                            $isCurrent = $now->between($scheduleStart, $scheduleEnd);
                            
                            $typeColors = [
                                'perkuliahan_tetap' => 'bg-yellow-500',
                                'perkuliahan_tidak_tetap' => 'bg-indigo-500',
                                'non_perkuliahan' => 'bg-emerald-500',
                                'pribadi' => 'bg-orange-500',
                            ];
                            $bgColor = $typeColors[$item['booking_type']] ?? 'bg-slate-500';
                        @endphp
                        <tr class="border-b border-slate-200 {{ $isCurrent ? 'current-row' : 'hover:bg-slate-50' }} transition">
                            <td class="px-4 py-4 align-middle whitespace-nowrap">
                                <span class="text-lg font-mono font-bold text-slate-800">{{ \Carbon\Carbon::parse($item['start_time'])->format('H:i') }}</span>
                                <span class="text-slate-400 mx-1">-</span>
                                <span class="text-lg font-mono text-slate-600">{{ \Carbon\Carbon::parse($item['end_time'])->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                                <span class="{{ $bgColor }} text-white px-2 py-1 rounded text-xs font-semibold">{{ $item['lab'] }}</span>
                            </td>
                            <td class="px-4 py-4 align-middle">
                                <div class="text-base font-semibold text-slate-800">{{ $item['course'] }}</div>
                                @if($item['komting'])
                                    <div class="text-sm text-slate-500 mt-0.5">
                                        {{ in_array($item['booking_type'], ['pribadi', 'non_perkuliahan']) ? 'Peminjam' : 'Komting' }}: {{ $item['komting'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-middle text-center text-slate-600 text-sm whitespace-nowrap">{{ $item['lecturer'] ?? '-' }}</td>
                            <td class="px-4 py-4 align-middle text-center whitespace-nowrap">
                                @if($item['student_count'])
                                    <span class="text-slate-700 text-sm font-medium">{{ $item['student_count'] }} orang</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <!-- No Schedule Today -->
            <div class="flex flex-col items-center justify-center h-full text-center py-20">
                <svg class="w-24 h-24 text-slate-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-3xl font-bold text-slate-400 mb-2">Tidak Ada Jadwal Hari Ini</h2>
                <p class="text-slate-400">{{ $todayName }}, {{ $today->isoFormat('D MMMM Y') }}</p>
            </div>
            @endif
        </div>
    </main>

    <!-- Footer Status Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-slate-100 border-t border-slate-200 px-6 py-3 flex items-center justify-between text-sm">
        <div class="flex items-center gap-6 text-slate-600">
            <span class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-yellow-500"></span> Perkuliahan Tetap
            </span>
            <span class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-indigo-500"></span> Perkuliahan Tidak Tetap
            </span>
            <span class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-emerald-500"></span> Non-Perkuliahan
            </span>
            <span class="flex items-center gap-2">
                <span class="w-3 h-3 rounded bg-orange-500"></span> Pribadi
            </span>
        </div>
        <div class="text-slate-400 flex items-center gap-2">
            <span class="text-slate-300">|</span>
            Auto-refresh setiap 5 menit | <span id="next-refresh"></span>
        </div>
    </div>

    <script>
        // Update clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('current-time').textContent = timeStr;
        }
        
        // Countdown to next refresh
        let refreshCountdown = 300;
        function updateRefreshCountdown() {
            const mins = Math.floor(refreshCountdown / 60);
            const secs = refreshCountdown % 60;
            document.getElementById('next-refresh').textContent = `Refresh dalam ${mins}:${secs.toString().padStart(2, '0')}`;
            refreshCountdown--;
            if (refreshCountdown < 0) refreshCountdown = 300;
        }
        
        // Smart auto-scroll: only if content exceeds viewport
        function initAutoScroll() {
            const container = document.getElementById('scroll-container');
            const content = document.getElementById('scroll-content');
            
            if (!container || !content) return;
            
            const containerHeight = container.clientHeight;
            const contentHeight = content.scrollHeight;
            
            // Only scroll if content is taller than container
            if (contentHeight <= containerHeight) {
                console.log('Content fits in viewport, no scrolling needed');
                return;
            }
            
            const maxScroll = contentHeight - containerHeight;
            let scrollDirection = 1; // 1 = down, -1 = up
            let currentScroll = 0;
            const scrollSpeed = 1; // pixels per frame
            const pauseAtEnds = 3000; // ms to pause at top/bottom
            let isPaused = false;
            
            function scroll() {
                if (isPaused) return;
                
                currentScroll += scrollDirection * scrollSpeed;
                
                // Reached bottom - pause then reverse
                if (currentScroll >= maxScroll) {
                    currentScroll = maxScroll;
                    isPaused = true;
                    setTimeout(() => {
                        scrollDirection = -1;
                        isPaused = false;
                    }, pauseAtEnds);
                }
                
                // Reached top - pause then reverse
                if (currentScroll <= 0) {
                    currentScroll = 0;
                    isPaused = true;
                    setTimeout(() => {
                        scrollDirection = 1;
                        isPaused = false;
                    }, pauseAtEnds);
                }
                
                container.scrollTop = currentScroll;
            }
            
            // Run scroll at 60fps
            setInterval(scroll, 1000 / 60);
        }
        
        // Initialize
        updateClock();
        updateRefreshCountdown();
        setInterval(updateClock, 1000);
        setInterval(updateRefreshCountdown, 1000);
        
        // Start auto-scroll after page loads
        window.addEventListener('load', () => {
            setTimeout(initAutoScroll, 1000);
        });
    </script>
</body>
</html>
