<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal Laboratorium - Laboratorium dan Fasilitas Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Scrollbar styling */
        .grid-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
        .grid-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        .grid-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .grid-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Day tabs — hide scrollbar */
        .day-tabs-scroll { scrollbar-width: none; }
        .day-tabs-scroll::-webkit-scrollbar { display: none; }

        /* Sticky room header inside scrollable timetable */
        .room-header-row { position: sticky; top: 0; z-index: 20; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- ── Navbar ─────────────────────────────────── -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-3 sm:px-4 md:px-6 py-3 md:py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('landing') }}">
                        <img src="{{ asset('images/LogoUndips.png') }}" alt="Logo Undip" class="h-10 sm:h-12 md:h-16 w-auto object-contain">
                    </a>
                </div>

                <!-- Desktop menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('landing') }}" class="text-gray-600 hover:text-yellow-600 font-semibold transition-all duration-200 hover:scale-105 text-base">Beranda</a>
                    @if(auth()->check())
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-yellow-600 font-semibold transition-all duration-200 hover:scale-105 text-base">Dashboard</a>
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 hover:scale-105 hover:shadow-lg text-base">Ajukan</a>
                    @else
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 hover:scale-105 hover:shadow-lg text-base">Ajukan</a>
                        <a href="{{ route('login') }}" class="text-yellow-600 hover:text-yellow-700 font-semibold border-2 border-yellow-500 px-4 py-2 rounded-lg hover:bg-yellow-50 transition-all duration-200 text-base">Login</a>
                    @endif
                </div>

                <!-- Mobile hamburger -->
                <button id="mobileMenuButton" class="md:hidden p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile dropdown -->
            <div id="mobileMenu" class="hidden md:hidden mt-4 pb-2 border-t border-gray-200 pt-4">
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('landing') }}" class="text-gray-600 hover:text-yellow-600 font-semibold py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors text-base">Beranda</a>
                    @if(auth()->check())
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-yellow-600 font-semibold py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors text-base">Dashboard</a>
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg font-semibold text-center transition-all text-base">Ajukan Peminjaman</a>
                    @else
                        <a href="{{ route('booking.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-3 rounded-lg font-semibold text-center transition-all text-base">Ajukan Peminjaman</a>
                        <a href="{{ route('login') }}" class="text-yellow-600 hover:text-yellow-700 font-semibold border-2 border-yellow-500 px-4 py-3 rounded-lg hover:bg-yellow-50 text-center transition-all text-base">Login</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- ── Page Body ───────────────────────────────── -->
    <div class="container mx-auto px-3 md:px-6 py-6">

        <!-- Page title -->
        <div class="text-center mb-5">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">Jadwal Laboratorium</h1>
            <p class="text-sm text-gray-500">Lihat jadwal penggunaan laboratorium per ruangan</p>
        </div>

        <!-- Week navigation -->
        <div class="bg-white rounded-xl shadow p-4 mb-4">
            <div class="flex items-center gap-4">
                <button id="prevWeek" class="flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </button>

                <div class="flex-1 text-center">
                    <div class="text-xs text-gray-400 mb-0.5">Periode</div>
                    <div id="weekLabel" class="text-base md:text-lg font-bold text-gray-800">Memuat...</div>
                </div>

                <button id="nextWeek" class="flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-colors flex-shrink-0">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <div id="currentWeekBtnWrap" class="hidden justify-center mt-3">
                <button id="currentWeekBtn" class="px-4 py-1.5 text-sm bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition-colors">
                    Kembali ke Minggu Ini
                </button>
            </div>
        </div>

        <!-- Main card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">

            <!-- Day tabs -->
            <div class="border-b border-gray-200">
                <div id="dayTabs" class="flex w-full"></div>
            </div>

            <!-- Timetable container (x scroll only, full height) -->
            <div id="timetableWrap" class="grid-scroll overflow-x-auto">
                <div id="loadingState" class="text-center py-16">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-500 mx-auto"></div>
                    <p class="text-gray-500 mt-4 text-sm">Memuat jadwal...</p>
                </div>
                <div id="timetableContent" class="hidden"></div>
            </div>

            <!-- Legend -->
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-600">
                <div class="font-semibold text-gray-700 mb-1.5">Keterangan Warna:</div>
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

    </div><!-- end container -->

    <!-- ── JavaScript ─────────────────────────────── -->
    <script>
    // ── Constants ──────────────────────────────────────────
    const TIME_START   = 5;    // 05:00
    const TIME_END     = 23;   // 23:00
    const SLOT_MINS    = 10;   // minutes per slot
    const PX_PER_SLOT  = 10;   // px per 10-min slot
    const PX_PER_HOUR  = (60 / SLOT_MINS) * PX_PER_SLOT; // 60px/hour
    const TIME_COL_W   = 64;   // px width of time label column
    const LAB_MIN_W    = 140;  // min px per lab column
    const TOTAL_H      = (TIME_END - TIME_START) * PX_PER_HOUR;
    const DAYS         = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

    const TYPE_COLORS = {
        perkuliahan_tetap:       { bg:'#fef9c3', accent:'#ca8a04', text:'#713f12' },
        perkuliahan_tidak_tetap: { bg:'#c7d2fe', accent:'#4338ca', text:'#1e1b4b' },
        non_perkuliahan:         { bg:'#a7f3d0', accent:'#059669', text:'#064e3b' },
        pribadi:                 { bg:'#fed7aa', accent:'#c2410c', text:'#431407' },
    };

    // ── State ──────────────────────────────────────────────
    let weekOffset = 0;
    let weekData   = null;
    let activeDay  = null;

    // ── Utilities ──────────────────────────────────────────
    const toMins = t => { const p = t.split(':'); return +p[0]*60 + +p[1]; };
    const fmtT   = t => { const p = t.split(':'); return p[0]+':'+p[1]; };
    const esc    = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    function todayName() {
        return ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][new Date().getDay()];
    }

    function buildDayMap(data) {
        const ws  = new Date(data.week_start);
        const map = {};
        DAYS.forEach((day, i) => {
            const d  = new Date(ws);
            d.setDate(d.getDate() + i);
            const dd = String(d.getDate()).padStart(2,'0');
            const mm = String(d.getMonth()+1).padStart(2,'0');
            map[day] = {
                dateStr: dd+'/'+mm,
                items:   data.schedules.filter(s => s.day === day),
            };
        });
        return map;
    }

    // ── Render Tabs ────────────────────────────────────────
    function renderTabs(dayMap) {
        document.getElementById('dayTabs').innerHTML = DAYS.map(day => {
            const active = day === activeDay;
            return `
            <button onclick="selectDay('${day}')" data-day="${day}"
                class="day-tab flex-1 py-4 text-center border-b-2 transition-colors
                       ${active ? 'border-yellow-500 text-yellow-700 bg-yellow-50'
                                : 'border-transparent text-gray-600 hover:text-yellow-600 hover:bg-yellow-50/50'}">
                <div class="font-semibold text-sm">${day}</div>
                <div class="text-xs mt-0.5 ${active ? 'text-yellow-500' : 'text-gray-400'}">${dayMap[day].dateStr}</div>
            </button>`;
        }).join('');
    }

    function updateTabStyles() {
        document.querySelectorAll('.day-tab').forEach(btn => {
            const active = btn.dataset.day === activeDay;
            btn.className = `day-tab flex-1 py-4 text-center border-b-2 transition-colors
                ${active ? 'border-yellow-500 text-yellow-700 bg-yellow-50'
                         : 'border-transparent text-gray-600 hover:text-yellow-600 hover:bg-yellow-50/50'}`;
            btn.querySelectorAll('div')[1].className = `text-xs mt-0.5 ${active ? 'text-yellow-500' : 'text-gray-400'}`;
        });
    }

    // ── Render Timetable ───────────────────────────────────
    function renderTimetable(dayData, labs) {
        const loading = document.getElementById('loadingState');
        const content = document.getElementById('timetableContent');
        loading.classList.add('hidden');
        content.classList.remove('hidden');

        if (!labs || labs.length === 0) {
            content.innerHTML = '<div class="text-center py-16 text-gray-400 text-sm">Tidak ada data ruangan</div>';
            return;
        }

        const items   = dayData.items || [];
        const minW    = TIME_COL_W + labs.length * LAB_MIN_W;

        // Time labels — every 30 min
        let timeLabels = '';
        for (let h = TIME_START; h < TIME_END; h++) {
            const tH = ((h - TIME_START) * 60 / SLOT_MINS) * PX_PER_SLOT;
            const tG = tH + (30 / SLOT_MINS) * PX_PER_SLOT;
            timeLabels += `
                <div style="position:absolute;top:${tH}px;right:6px;font-size:11px;font-weight:600;color:#64748b;line-height:${PX_PER_SLOT}px;">${String(h).padStart(2,'0')}:00</div>
                <div style="position:absolute;top:${tG}px;right:6px;font-size:10px;color:#94a3b8;line-height:${PX_PER_SLOT}px;">${String(h).padStart(2,'0')}:30</div>`;
        }

        // Gridlines template (shared across all lab columns)
        let gridlines = '';
        const totalSlots = (TIME_END - TIME_START) * 60 / SLOT_MINS;
        for (let slot = 0; slot <= totalSlots; slot++) {
            const m   = slot * SLOT_MINS;
            const top = slot * PX_PER_SLOT;
            if (m % 60 === 0) {
                gridlines += `<div style="position:absolute;top:${top}px;left:0;right:0;height:1px;background:#e2e8f0;"></div>`;
            } else if (m % 30 === 0) {
                gridlines += `<div style="position:absolute;top:${top}px;left:0;right:0;border-top:1px dashed #e2e8f0;"></div>`;
            } else {
                gridlines += `<div style="position:absolute;top:${top}px;left:0;right:0;height:1px;background:#f8fafc;"></div>`;
            }
        }

        // Build lab columns
        let labCols = labs.map(lab => {
            const blocks = items
                .filter(i => i.lab_id == lab.id)
                .map(buildEventBlock)
                .join('');
            return `<div style="flex:1;min-width:${LAB_MIN_W}px;position:relative;height:${TOTAL_H}px;border-left:1px solid #f1f5f9;">
                        ${gridlines}${blocks}
                    </div>`;
        }).join('');

        // Build header columns (room names)
        const headerCols = [`<div style="width:${TIME_COL_W}px;flex-shrink:0;background:#1e293b;"></div>`]
            .concat(labs.map(lab =>
                `<div style="flex:1;min-width:${LAB_MIN_W}px;background:#1e293b;color:#fff;text-align:center;padding:12px 6px;font-weight:700;font-size:13px;border-left:1px solid #334155;">${esc(lab.name)}</div>`
            )).join('');

        content.innerHTML = `
            <div style="min-width:${minW}px;">
                <div class="room-header-row" style="display:flex;">${headerCols}</div>
                <div style="display:flex;height:${TOTAL_H}px;">
                    <div style="width:${TIME_COL_W}px;flex-shrink:0;position:relative;height:${TOTAL_H}px;background:#f8fafc;border-right:1px solid #e2e8f0;">
                        ${timeLabels}
                    </div>
                    ${labCols}
                </div>
            </div>`;

        // Scroll to 07:00
        const wrap = document.getElementById('timetableWrap');
        setTimeout(() => { wrap.scrollTop = ((7 - TIME_START) * 60 / SLOT_MINS) * PX_PER_SLOT; }, 60);
    }

    function buildEventBlock(item) {
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
        const s      = fmtT(item.start_time);
        const e      = fmtT(item.end_time);
        const tip    = esc(`${item.course||''}${item.lecturer?'\n'+item.lecturer:''}\n${s} - ${e}`);

        return `
        <div title="${tip}"
             style="position:absolute;top:${top}px;height:${height}px;left:3px;right:3px;
                    background:${c.bg};border-radius:7px;overflow:hidden;z-index:5;
                    box-shadow:0 1px 4px rgba(0,0,0,.1);">
            <div style="position:absolute;top:0;bottom:0;left:0;width:5px;background:${c.accent};border-radius:7px 0 0 7px;"></div>
            <div style="padding:3px 4px 3px 9px;height:100%;display:flex;flex-direction:column;gap:1px;overflow:hidden;">
                <div style="font-size:11px;font-weight:700;color:${c.text};line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${course}</div>
                ${height>36?`
                <div style="font-size:10px;color:${c.text};opacity:.85;display:flex;align-items:center;gap:2px;white-space:nowrap;">
                    <svg width="9" height="9" style="flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>${s} - ${e}
                </div>`:''}
                ${height>52&&lec?`
                <div style="font-size:10px;color:${c.text};opacity:.75;display:flex;align-items:center;gap:2px;overflow:hidden;">
                    <svg width="9" height="9" style="flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${lec}</span>
                </div>`:''}
            </div>
        </div>`;
    }

    // ── Select Day ─────────────────────────────────────────
    function selectDay(day) {
        activeDay = day;
        updateTabStyles();
        const dayMap = buildDayMap(weekData);
        renderTimetable(dayMap[day], weekData.labs);
    }

    // ── Load Week ──────────────────────────────────────────
    function loadSchedules(offset) {
        weekOffset = offset;
        document.getElementById('weekLabel').textContent = 'Memuat...';
        document.getElementById('loadingState').classList.remove('hidden');
        document.getElementById('timetableContent').classList.add('hidden');

        fetch(`{{ route('schedules.week') }}?week_offset=${offset}`)
            .then(r => r.json())
            .then(data => {
                weekData = data;
                document.getElementById('weekLabel').textContent = data.week_label;

                // "Back to current week" button
                const bw = document.getElementById('currentWeekBtnWrap');
                offset === 0 ? bw.classList.add('hidden') : bw.classList.remove('hidden');
                bw.style.display = offset !== 0 ? 'flex' : 'none';

                // Pick active day
                const today = todayName();
                if (offset === 0 && DAYS.includes(today)) {
                    activeDay = today;
                } else if (!DAYS.includes(activeDay)) {
                    activeDay = 'Senin';
                }

                const dayMap = buildDayMap(data);
                renderTabs(dayMap);
                renderTimetable(dayMap[activeDay], data.labs);
            })
            .catch(() => {
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('timetableContent').classList.remove('hidden');
                document.getElementById('timetableContent').innerHTML = `
                    <div class="text-center py-16">
                        <svg class="w-14 h-14 text-orange-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-orange-600 font-semibold">Tidak ada jadwal di luar semester</p>
                    </div>`;
            });
    }

    // ── Event Listeners ────────────────────────────────────
    document.getElementById('prevWeek').addEventListener('click', () => loadSchedules(weekOffset - 1));
    document.getElementById('nextWeek').addEventListener('click', () => loadSchedules(weekOffset + 1));
    document.getElementById('currentWeekBtn').addEventListener('click', () => loadSchedules(0));
    document.getElementById('mobileMenuButton').addEventListener('click', () => {
        document.getElementById('mobileMenu').classList.toggle('hidden');
    });

    // ── Init ───────────────────────────────────────────────
    loadSchedules(0);
    </script>

</body>
</html>
