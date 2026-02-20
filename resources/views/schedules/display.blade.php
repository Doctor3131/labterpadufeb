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
        
        /* Current time indicator line */
        .time-indicator {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: #ef4444;
            z-index: 30;
            pointer-events: none;
        }
        .time-indicator::before {
            content: '';
            position: absolute;
            left: 0;
            top: -4px;
            width: 10px;
            height: 10px;
            background: #ef4444;
            border-radius: 50%;
        }
    </style>
</head>
<body class="bg-white text-slate-800 min-h-screen overflow-hidden">
    
    <!-- Header Bar -->
    <header class="bg-yellow-500 py-4 px-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('landing') }}" class="hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo" class="h-12">
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">Jadwal Laboratorium dan Fasilitas Digital FEB</h1>
                    <p class="text-yellow-100 text-sm">Hari Ini - {{ now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
            </div>
            <div class="text-right text-white">
                <div id="current-time" class="text-4xl font-bold font-mono"></div>
            </div>
        </div>
    </header>

    <!-- Timetable Grid -->
    <main id="scroll-container" class="scroll-container overflow-y-auto" style="height: calc(100vh - 130px);">
        <div id="scroll-content">
            @php
                $today = now()->timezone('Asia/Jakarta');
                $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                $todayName = $dayNames[$today->format('l')];
                $todaySchedules = $schedules[$todayName]['items'] ?? collect([]);
                
                // Grid config
                $timeStart = 5;  // 05:00
                $timeEnd = 23;   // 23:00
                $slotMinutes = 10;
                $totalSlots = (($timeEnd - $timeStart) * 60) / $slotMinutes;
                $rowHeight = 10; // px per 10-min slot
                $totalHeight = $totalSlots * $rowHeight;
                
                // Color mapping
                $typeColors = [
                    'perkuliahan_tetap' => ['bg' => 'bg-yellow-300', 'accent' => 'bg-yellow-600', 'text' => 'text-yellow-900'],
                    'perkuliahan_tidak_tetap' => ['bg' => 'bg-indigo-400', 'accent' => 'bg-indigo-700', 'text' => 'text-indigo-900'],
                    'non_perkuliahan' => ['bg' => 'bg-emerald-400', 'accent' => 'bg-emerald-700', 'text' => 'text-emerald-900'],
                    'pribadi' => ['bg' => 'bg-orange-400', 'accent' => 'bg-orange-700', 'text' => 'text-orange-900'],
                ];
            @endphp
            
            @if(count($todaySchedules) > 0)
            <div class="p-4">
                <div class="flex" style="min-width: 800px;">
                    <!-- Time Labels Column -->
                    <div class="flex-shrink-0 relative bg-slate-50 border-r border-slate-300" style="width: 70px; height: {{ $totalHeight }}px;">
                        @for($h = $timeStart; $h < $timeEnd; $h++)
                            @php
                                $topHour = (($h - $timeStart) * 60 / $slotMinutes) * $rowHeight;
                                $topHalf = $topHour + (30 / $slotMinutes) * $rowHeight;
                            @endphp
                            <div class="absolute text-xs font-semibold text-slate-500 pr-2 text-right w-full" style="top: {{ $topHour }}px; line-height: {{ $rowHeight }}px;">
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                            </div>
                            <div class="absolute text-[10px] text-slate-400 pr-2 text-right w-full" style="top: {{ $topHalf }}px; line-height: {{ $rowHeight }}px;">
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:30
                            </div>
                        @endfor
                    </div>

                    <!-- Lab Columns -->
                    @foreach($labs as $lab)
                    <div class="flex-1 relative border-l border-slate-200" style="height: {{ $totalHeight }}px;">
                        <!-- Lab Header (sticky) -->
                        <div class="sticky top-0 z-20 bg-slate-800 text-white text-center py-3 px-2 font-bold text-sm border-l border-slate-600">
                            {{ $lab->name }}
                        </div>

                        <!-- Gridlines -->
                        @for($slot = 0; $slot <= $totalSlots; $slot++)
                            @php
                                $mins = $slot * $slotMinutes;
                                $topPx = $slot * $rowHeight;
                            @endphp
                            @if($mins % 60 === 0)
                                <div class="absolute w-full border-t border-slate-200" style="top: {{ $topPx }}px;"></div>
                            @elseif($mins % 30 === 0)
                                <div class="absolute w-full border-t border-dashed border-slate-100" style="top: {{ $topPx }}px;"></div>
                            @endif
                        @endfor

                        <!-- Schedule Blocks -->
                        @foreach($todaySchedules as $item)
                            @if(($item['lab_id'] ?? null) == $lab->id)
                                @php
                                    $startMin = \Carbon\Carbon::parse($item['start_time'])->hour * 60 + \Carbon\Carbon::parse($item['start_time'])->minute;
                                    $endMin = \Carbon\Carbon::parse($item['end_time'])->hour * 60 + \Carbon\Carbon::parse($item['end_time'])->minute;
                                    $startOffset = $startMin - ($timeStart * 60);
                                    $duration = $endMin - $startMin;
                                    
                                    if ($startOffset < 0 || $duration <= 0) continue;
                                    
                                    $topPx = ($startOffset / $slotMinutes) * $rowHeight;
                                    $heightPx = ($duration / $slotMinutes) * $rowHeight;
                                    $bookingType = $item['booking_type'] ?? $item['type'] ?? 'perkuliahan_tetap';
                                    $colors = $typeColors[$bookingType] ?? $typeColors['perkuliahan_tetap'];
                                    $isKuliah = in_array($bookingType, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']);
                                    
                                    $startTimeStr = \Carbon\Carbon::parse($item['start_time'])->format('H:i');
                                    $endTimeStr = \Carbon\Carbon::parse($item['end_time'])->format('H:i');
                                    $courseName = $item['course'] ?? '-';
                                    $lecturerName = ($isKuliah && !empty($item['lecturer'])) ? $item['lecturer'] : '';
                                @endphp
                                <div class="absolute left-1 right-1 {{ $colors['bg'] }} rounded-lg shadow-sm border border-slate-200/50 overflow-hidden group"
                                     style="top: {{ $topPx }}px; height: {{ $heightPx }}px; z-index: 5;"
                                     title="{{ $courseName }}{{ $lecturerName ? "\n" . $lecturerName : '' }}{{ "\n" . $startTimeStr . ' - ' . $endTimeStr }}">
                                    
                                    <!-- Accent Band -->
                                    <div class="absolute top-0 bottom-0 left-0 w-2 {{ $colors['accent'] }}"></div>

                                    <!-- Content -->
                                    <div class="pl-3 pr-2 py-1.5 h-full flex flex-col justify-start">
                                        <!-- Title -->
                                        <div class="text-xs font-bold {{ $colors['text'] }} leading-tight truncate mb-0.5">{{ $courseName }}</div>
                                        
                                        <!-- Time -->
                                        @if($heightPx > 35)
                                        <div class="flex items-center gap-1.5 text-[10px] font-medium {{ $colors['text'] }} leading-none mb-0.5 opacity-90">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="truncate">{{ $startTimeStr }} - {{ $endTimeStr }}</span>
                                        </div>
                                        @endif

                                        <!-- Lecturer -->
                                        @if($heightPx > 50 && $lecturerName)
                                        <div class="flex items-center gap-1.5 text-[10px] {{ $colors['text'] }} leading-none opacity-80">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            <span class="truncate">{{ $lecturerName }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <!-- Empty state - centered, no scroll needed -->
            <div class="flex flex-col items-center justify-center text-center" style="height: calc(100vh - 180px);">
                <svg class="w-24 h-24 text-slate-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-3xl font-bold text-slate-400 mb-2">Tidak Ada Jadwal Hari Ini</h2>
                <p class="text-slate-400 text-lg">{{ $todayName }}, {{ $today->isoFormat('D MMMM Y') }}</p>
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
        
        // Current time indicator
        function updateTimeIndicator() {
            // Remove existing indicator
            document.querySelectorAll('.time-indicator').forEach(el => el.remove());
            
            const now = new Date();
            const currentMinutes = now.getHours() * 60 + now.getMinutes();
            const timeStart = 5 * 60;  // 05:00
            const timeEnd = 23 * 60;   // 23:00
            
            if (currentMinutes < timeStart || currentMinutes > timeEnd) return;
            
            const slotMinutes = 10;
            const rowHeight = 10;
            const offset = currentMinutes - timeStart;
            const topPx = (offset / slotMinutes) * rowHeight;
            
            // Add indicator to each lab column
            const labCols = document.querySelectorAll('[class*="flex-1 relative border-l"]');
            labCols.forEach(col => {
                const indicator = document.createElement('div');
                indicator.className = 'time-indicator';
                indicator.style.top = topPx + 'px';
                col.appendChild(indicator);
            });
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
                return;
            }
            
            const maxScroll = contentHeight - containerHeight;
            let scrollDirection = 1; // 1 = down, -1 = up
            let currentScroll = 0;
            const scrollSpeed = 2; // pixels per frame
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
        updateTimeIndicator();
        setInterval(updateClock, 1000);
        setInterval(updateRefreshCountdown, 1000);
        setInterval(updateTimeIndicator, 60000); // Update indicator every minute
        
        // Start auto-scroll after page loads
        window.addEventListener('load', () => {
            setTimeout(initAutoScroll, 1000);
        });
    </script>
</body>
</html>
