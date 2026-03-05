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
        {{-- <div class="absolute inset-0 bg-grid-white/10"></div> --}}
        <div class="container mx-auto px-4 lg:px-8 py-12 lg:py-24 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl lg:text-5xl font-bold mb-3 lg:mb-6 leading-tight drop-shadow-md animate-fade-in-up animation-delay-100">
                    Jadwal & Manajemen<br class="hidden lg:block"/> Laboratorium Digital
                </h2>
                <p class="text-base lg:text-xl text-yellow-50 max-w-2xl mx-auto drop-shadow mb-6 lg:mb-10 animate-fade-in-up animation-delay-200">
                    Pantau jadwal praktikum dan ajukan penggunaan fasilitas laboratorium secara terintegrasi
                </p>
                
                <!-- CTA Buttons - Center untuk semua device -->
                <div class="flex flex-wrap items-center justify-center gap-3 lg:gap-4 animate-fade-in-up animation-delay-300">
                    <!-- Button Peminjaman Lab -->
                    <a href="{{ route('booking.create') }}" class="inline-flex items-center px-7 py-3 lg:px-9 lg:py-4 bg-white text-yellow-600 font-bold rounded-full hover:bg-yellow-50 hover:shadow-2xl transition-all duration-300 shadow-xl text-sm lg:text-base hover:scale-110">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Peminjaman Lab
                    </a>

                    <!-- Button Lab Bloomberg -->
                    <a href="{{ route('bloomberg.index') }}" class="inline-flex items-center px-7 py-3 lg:px-9 lg:py-4 bg-white text-yellow-600 font-bold rounded-full hover:bg-yellow-50 hover:shadow-2xl transition-all duration-300 shadow-xl text-sm lg:text-base hover:scale-110">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Lab Bloomberg
                    </a>

                    <!-- Button Pinjam Barang -->
                    <a href="{{ route('asset-borrowing.create') }}" class="inline-flex items-center px-7 py-3 lg:px-9 lg:py-4 bg-white text-yellow-600 font-bold rounded-full hover:bg-yellow-50 hover:shadow-2xl transition-all duration-300 shadow-xl text-sm lg:text-base hover:scale-110">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Pinjam Barang
                    </a>

                    <!-- Button Peminjaman Data -->
                    <a href="{{ route('data.index') }}" class="inline-flex items-center px-7 py-3 lg:px-9 lg:py-4 bg-white/20 backdrop-blur-sm text-white border-2 border-white font-bold rounded-full hover:bg-white hover:text-yellow-600 hover:shadow-2xl transition-all duration-300 shadow-xl text-sm lg:text-base hover:scale-110">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="text-center mb-6 animate-fade-in-up animation-delay-400">
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

            {{-- Pass PHP data to JS --}}
            @php
                $jsSchedules = [];
                $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                foreach ($days as $i => $day) {
                    $dayData = $schedules[$day] ?? ['date'=>'','items'=>[]];
                    $date = $dayData['date'] ?? '';
                    $dd = $date ? \Carbon\Carbon::parse($date)->format('d/m') : '-';
                    foreach (($dayData['items'] ?? []) as $item) {
                        $jsSchedules[] = [
                            'day'          => $day,
                            'date'         => $date,
                            'dateStr'      => $dd,
                            'lab_id'       => $item['lab_id']     ?? null,
                            'lab'          => $item['lab']        ?? '',
                            'course'       => $item['course']     ?? '-',
                            'start_time'   => $item['start_time'] ?? '00:00',
                            'end_time'     => $item['end_time']   ?? '00:00',
                            'lecturer'     => $item['lecturer']   ?? '',
                            'booking_type' => $item['booking_type'] ?? 'perkuliahan_tetap',
                        ];
                    }
                }
                $jsLabs = $labs->map(fn($l) => ['id'=>$l->id,'name'=>$l->name])->values()->toArray();
                $jsDayMeta = [];
                foreach ($days as $i => $day) {
                    $date = $schedules[$day]['date'] ?? '';
                    $jsDayMeta[$day] = $date ? \Carbon\Carbon::parse($date)->format('d/m') : '-';
                }
            @endphp

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-fade-in-up animation-delay-500" style="display:flex;flex-direction:column;">

                <!-- Day Tabs -->
                <div class="border-b border-slate-200 flex-shrink-0">
                    <div id="landingDayTabs" class="flex w-full"></div>
                </div>

                <!-- Timetable grid (x scroll only, full height) -->
                <div id="landingTimetableWrap" class="overflow-x-auto flex-1">
                    <div id="landingTimetableContent"></div>
                </div>

                <!-- Legend -->
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex-shrink-0 text-xs text-slate-600">
                    <div class="font-semibold text-slate-700 mb-1.5">Keterangan Warna:</div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm inline-block flex-shrink-0" style="background:#eab308;"></span>
                            <span><strong>Kuning:</strong> Perkuliahan Tetap</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm inline-block flex-shrink-0" style="background:#6366f1;"></span>
                            <span><strong>Ungu:</strong> Perkuliahan Tidak Tetap</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-sm inline-block flex-shrink-0" style="background:#10b981;"></span>
                            <span><strong>Hijau:</strong> Non-Perkuliahan</span>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script>
    (function() {
        // ── Data from PHP ──────────────────────────────────────
        const LANDING_SCHEDULES = @json($jsSchedules);
        const LANDING_LABS      = @json($jsLabs);
        const LANDING_DAY_META  = @json($jsDayMeta);
        const DAYS = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        // ── Grid Config ────────────────────────────────────────
        const TIME_START  = 5;
        const TIME_END    = 23;
        const SLOT_MINS   = 10;
        const PX_PER_SLOT = 10;
        const PX_PER_HOUR = (60 / SLOT_MINS) * PX_PER_SLOT; // 60px/hour
        const TIME_COL_W  = 64;
        const LAB_MIN_W   = 140;
        const TOTAL_H     = (TIME_END - TIME_START) * PX_PER_HOUR;

        const TYPE_COLORS = {
            perkuliahan_tetap:       { bg:'#fef9c3', accent:'#ca8a04', text:'#713f12' },
            perkuliahan_tidak_tetap: { bg:'#c7d2fe', accent:'#4338ca', text:'#1e1b4b' },
            non_perkuliahan:         { bg:'#a7f3d0', accent:'#059669', text:'#064e3b' },
            pribadi:                 { bg:'#fed7aa', accent:'#c2410c', text:'#431407' },
        };

        const toMins = t => { const p = t.split(':'); return +p[0]*60 + +p[1]; };
        const fmtT   = t => { const p = t.split(':'); return p[0]+':'+p[1]; };
        const esc    = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

        function todayName() {
            return ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][new Date().getDay()];
        }

        let activeDay = null;

        // ── Render Tabs ────────────────────────────────────────
        function renderTabs() {
            document.getElementById('landingDayTabs').innerHTML = DAYS.map(day => {
                const active  = day === activeDay;
                const dateStr = LANDING_DAY_META[day] || '-';
                return `
                <button onclick="landingSelectDay('${day}')" data-lday="${day}"
                    class="landing-day-tab flex-1 py-4 text-center border-b-2 transition-colors
                           ${active ? 'border-yellow-500 text-yellow-700 bg-yellow-50'
                                    : 'border-transparent text-slate-600 hover:text-yellow-600 hover:bg-yellow-50/50'}">
                    <div class="font-semibold text-sm">${day}</div>
                    <div class="text-xs mt-0.5 ${active ? 'text-yellow-500' : 'text-slate-400'}">${dateStr}</div>
                </button>`;
            }).join('');
        }

        function updateTabs() {
            document.querySelectorAll('.landing-day-tab').forEach(btn => {
                const active = btn.dataset.lday === activeDay;
                btn.className = `landing-day-tab flex-1 py-4 text-center border-b-2 transition-colors
                    ${active ? 'border-yellow-500 text-yellow-700 bg-yellow-50'
                             : 'border-transparent text-slate-600 hover:text-yellow-600 hover:bg-yellow-50/50'}`;
                const sub = btn.querySelectorAll('div')[1];
                if (sub) sub.className = `text-xs mt-0.5 ${active ? 'text-yellow-500' : 'text-slate-400'}`;
            });
        }

        // ── Render Timetable ───────────────────────────────────
        function renderTimetable(day) {
            const content = document.getElementById('landingTimetableContent');
            const items   = LANDING_SCHEDULES.filter(s => s.day === day);
            const labs    = LANDING_LABS;
            if (!labs.length) { content.innerHTML = '<div class="text-center py-12 text-slate-400 text-sm">Tidak ada data ruangan</div>'; return; }

            const minW = TIME_COL_W + labs.length * LAB_MIN_W;

            // Time labels — every 30 min
            let timeLabels = '';
            for (let h = TIME_START; h < TIME_END; h++) {
                const tH = ((h - TIME_START) * 60 / SLOT_MINS) * PX_PER_SLOT;
                const tG = tH + (30 / SLOT_MINS) * PX_PER_SLOT;
                timeLabels += `
                    <div style="position:absolute;top:${tH}px;right:6px;font-size:11px;font-weight:600;color:#64748b;line-height:${PX_PER_SLOT}px;">${String(h).padStart(2,'0')}:00</div>
                    <div style="position:absolute;top:${tG}px;right:6px;font-size:10px;color:#94a3b8;line-height:${PX_PER_SLOT}px;">${String(h).padStart(2,'0')}:30</div>`;
            }

            // Gridlines — 10-min slots
            let gridlines = '';
            const totalSlots = (TIME_END - TIME_START) * 60 / SLOT_MINS;
            for (let slot = 0; slot <= totalSlots; slot++) {
                const mins = slot * SLOT_MINS;
                const top  = slot * PX_PER_SLOT;
                if (mins % 60 === 0) {
                    gridlines += `<div style="position:absolute;top:${top}px;left:0;right:0;height:1px;background:#e2e8f0;"></div>`;
                } else if (mins % 30 === 0) {
                    gridlines += `<div style="position:absolute;top:${top}px;left:0;right:0;border-top:1px dashed #e2e8f0;"></div>`;
                } else {
                    gridlines += `<div style="position:absolute;top:${top}px;left:0;right:0;border-top:1px solid #f8fafc;"></div>`;
                }
            }

            // Lab columns
            const labCols = labs.map(lab => {
                const blocks = items.filter(i => i.lab_id == lab.id).map(buildBlock).join('');
                return `<div style="flex:1;min-width:${LAB_MIN_W}px;position:relative;height:${TOTAL_H}px;border-left:1px solid #f1f5f9;">${gridlines}${blocks}</div>`;
            }).join('');

            // Header
            const headerCols = [`<div style="width:${TIME_COL_W}px;flex-shrink:0;background:#1e293b;"></div>`]
                .concat(labs.map(lab =>
                    `<div style="flex:1;min-width:${LAB_MIN_W}px;background:#1e293b;color:#fff;text-align:center;padding:12px 6px;font-weight:700;font-size:13px;border-left:1px solid #334155;">${esc(lab.name)}</div>`
                )).join('');

            content.innerHTML = `
                <div style="min-width:${minW}px;">
                    <div style="display:flex;position:sticky;top:0;z-index:20;">${headerCols}</div>
                    <div style="display:flex;height:${TOTAL_H}px;">
                        <div style="width:${TIME_COL_W}px;flex-shrink:0;position:relative;height:${TOTAL_H}px;background:#f8fafc;border-right:1px solid #e2e8f0;">${timeLabels}</div>
                        ${labCols}
                    </div>
                </div>`;

            const wrap = document.getElementById('landingTimetableWrap');
            setTimeout(() => { wrap.scrollTop = ((7 - TIME_START) * 60 / SLOT_MINS) * PX_PER_SLOT; }, 60);
        }

        function buildBlock(item) {
            const startOffset = toMins(item.start_time) - TIME_START*60;
            const duration    = toMins(item.end_time)   - toMins(item.start_time);
            if (startOffset < 0 || duration <= 0) return '';
            const top    = (startOffset / SLOT_MINS) * PX_PER_SLOT;
            const height = (duration    / SLOT_MINS) * PX_PER_SLOT;
            const type   = item.booking_type || 'perkuliahan_tetap';
            const c      = TYPE_COLORS[type] || TYPE_COLORS.perkuliahan_tetap;
            const isKul  = ['perkuliahan_tetap','perkuliahan_tidak_tetap'].includes(type);
            const course = esc(item.course || '-');
            const lec    = isKul && item.lecturer ? esc(item.lecturer) : '';
            const s = fmtT(item.start_time), e = fmtT(item.end_time);
            const tip = esc(`${item.course||''}${item.lecturer?'\n'+item.lecturer:''}\n${s} - ${e}`);
            return `
            <div title="${tip}" style="position:absolute;top:${top}px;height:${height}px;left:3px;right:3px;
                        background:${c.bg};border-radius:7px;overflow:hidden;z-index:5;box-shadow:0 1px 4px rgba(0,0,0,.1);">
                <div style="position:absolute;top:0;bottom:0;left:0;width:5px;background:${c.accent};border-radius:7px 0 0 7px;"></div>
                <div style="padding:3px 4px 3px 9px;height:100%;display:flex;flex-direction:column;gap:1px;overflow:hidden;">
                    <div style="font-size:11px;font-weight:700;color:${c.text};line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${course}</div>
                    ${height>36?`<div style="font-size:10px;color:${c.text};opacity:.85;display:flex;align-items:center;gap:2px;white-space:nowrap;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>${s} - ${e}
                    </div>`:''}
                    ${height>52&&lec?`<div style="font-size:10px;color:${c.text};opacity:.75;display:flex;align-items:center;gap:2px;overflow:hidden;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${lec}</span>
                    </div>`:''}
                </div>
            </div>`;
        }

        // ── Public API ─────────────────────────────────────────
        window.landingSelectDay = function(day) {
            activeDay = day;
            updateTabs();
            renderTimetable(day);
        };

        // ── Init ───────────────────────────────────────────────
        const today = todayName();
        activeDay = DAYS.includes(today) ? today : 'Senin';
        renderTabs();
        renderTimetable(activeDay);
    })();
    </script>

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

    <!-- Utility Scripts -->
    <script>
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

        // Feedback Modal Functions
        function openFeedbackModal() {
            const modal = document.getElementById('feedbackModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeFeedbackModal() {
            const modal = document.getElementById('feedbackModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            // Reset form
            document.getElementById('feedback_title').value = '';
            document.getElementById('feedback_detail').value = '';
        }

        // Close modal when clicking outside
        document.getElementById('feedbackModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeFeedbackModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFeedbackModal();
            }
        });

    </script>

</body>
</html>
