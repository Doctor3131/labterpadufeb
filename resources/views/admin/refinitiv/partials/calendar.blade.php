@php
    $calendarMonthLink = fn (string $month) => route('admin.refinitiv.index', [
        'status' => $status,
        'q' => $search !== '' ? $search : null,
        'sort' => $sort,
        'period' => $period,
        'month' => $month,
    ]);
    $calendarCurrentMonthLink = $calendarMonthLink(now()->format('Y-m'));
    $calendarStatusLabels = ['pending' => 'Menunggu', 'hadir' => 'Hadir', 'tidak_hadir' => 'Tidak hadir'];
@endphp

<section class="refinitiv-calendar min-w-0 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="refinitiv-calendar-title">
    <div class="mb-4 grid gap-3">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Jadwal penggunaan</p>
            <h2 id="refinitiv-calendar-title" class="mt-1 text-lg font-semibold text-slate-900">Kalender peminjaman Refinitiv</h2>
            <p class="mt-1 text-sm text-slate-600">Pilih tanggal untuk melihat sesi dan memfilter daftar di samping.</p>
        </div>
        <nav class="flex items-center justify-between gap-2" aria-label="Navigasi bulan kalender">
            <a href="{{ $calendarMonthLink($previousCalendarMonth) }}" data-refinitiv-calendar-nav data-month="{{ $previousCalendarMonth }}" aria-label="Bulan sebelumnya" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
            </a>
            <span class="min-w-0 flex-1 text-center text-sm font-semibold capitalize text-slate-900" data-refinitiv-calendar-month>{{ $calendarMonthLabel }}</span>
            <a href="{{ $calendarMonthLink($nextCalendarMonth) }}" data-refinitiv-calendar-nav data-month="{{ $nextCalendarMonth }}" aria-label="Bulan berikutnya" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg>
            </a>
            <a href="{{ $calendarCurrentMonthLink }}" data-refinitiv-calendar-nav data-month="{{ now()->format('Y-m') }}" class="inline-flex min-h-9 shrink-0 items-center justify-center rounded-lg border border-blue-200 px-3 text-xs font-semibold text-blue-800 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">Bulan ini</a>
        </nav>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-2" aria-label="Ringkasan peminjaman bulan ini">
        <div class="rounded-lg bg-blue-50 px-3 py-2.5">
            <p class="text-xs font-medium text-blue-800">Total pemohon</p>
            <p class="mt-0.5 text-xl font-semibold tabular-nums text-blue-950">{{ $calendarSummary['total'] }}</p>
            <p class="text-[11px] text-blue-700">pada {{ $calendarSummary['active_days'] }} hari aktif</p>
        </div>
        <div class="rounded-lg border border-slate-200 px-3 py-2.5">
            <p class="text-xs font-medium text-slate-600">Sesi terpadat</p>
            <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $calendarSummary['busiest_session'] ? $calendarSessionNames[$calendarSummary['busiest_session']] : 'Belum ada data' }}</p>
            @if($calendarSummary['busiest_session'])<p class="text-[11px] text-slate-500">{{ $calendarSummary['busiest_session_total'] }} pemohon bulan ini</p>@endif
        </div>
    </div>

    <div class="mb-2 grid grid-cols-7 gap-1 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-500" aria-hidden="true">
        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
            <span class="py-1">{{ $weekday }}</span>
        @endforeach
    </div>
    <div class="grid grid-cols-7 gap-1" role="group" aria-label="Tanggal pada {{ $calendarMonthLabel }}">
        @foreach ($calendarCells as $cell)
            @if ($cell === null)
                <span class="min-h-12 rounded-lg" aria-hidden="true"></span>
            @else
                @php
                    $cellDate = \Illuminate\Support\Carbon::parse($calendarMonth.'-'.str_pad((string) $cell, 2, '0', STR_PAD_LEFT), config('app.timezone'));
                    $cellDateString = $cellDate->toDateString();
                    $cellData = $calendarDays[$cellDateString] ?? ['total' => 0];
                    $cellSelected = $date !== '' && $calendarSelectedDate === $cellDateString;
                    $cellToday = $calendarToday === $cellDateString;
                    $cellIntensity = $cellData['total'] === 0 ? 'bg-white' : ($cellData['total'] < 4 ? 'bg-blue-50' : ($cellData['total'] < 8 ? 'bg-blue-100' : 'bg-blue-200'));
                @endphp
                <button type="button" data-refinitiv-calendar-day="{{ $cellDateString }}" data-refinitiv-calendar-total="{{ $cellData['total'] }}" data-refinitiv-calendar-label="{{ $cellDate->locale('id')->isoFormat('dddd, D MMMM Y') }}" aria-pressed="{{ $cellSelected ? 'true' : 'false' }}" aria-label="{{ $cellDate->locale('id')->isoFormat('dddd, D MMMM Y') }}, {{ $cellData['total'] }} pemohon" class="refinitiv-calendar-day {{ $cellIntensity }} {{ $cellSelected ? 'is-selected' : '' }} {{ $cellToday ? 'is-today' : '' }} min-h-12 rounded-lg border p-1.5 text-left transition">
                    <span class="flex items-center justify-between gap-1">
                        <span class="text-xs font-semibold text-slate-800">{{ $cell }}</span>
                        @if ($cellToday)<span class="hidden text-[9px] font-semibold uppercase tracking-wide text-blue-700 2xl:inline">Hari ini</span>@endif
                    </span>
                    <span class="mt-1 block truncate text-[10px] font-medium text-slate-500" data-refinitiv-calendar-cell-count>{{ $cellData['total'] > 0 ? $cellData['total'].' org' : '—' }}</span>
                </button>
            @endif
        @endforeach
    </div>

    <div class="refinitiv-calendar-day-panel mt-4 border-t border-slate-100 pt-4" data-refinitiv-calendar-panel aria-live="polite" aria-atomic="false">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-900" data-refinitiv-selected-date-label>{{ \Illuminate\Support\Carbon::parse($calendarSelectedDate, config('app.timezone'))->locale('id')->isoFormat('dddd, D MMMM Y') }}</h3>
                <p class="mt-0.5 text-xs text-slate-500" data-refinitiv-day-filter-hint>{{ $date !== '' ? 'Daftar di samping difilter berdasarkan tanggal ini.' : 'Ringkasan tanggal ini. Pilih tanggal untuk memfilter daftar di samping.' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-xs font-semibold text-blue-800"><span data-refinitiv-day-total>{{ $calendarSelectedDay['total'] }}</span> pemohon</p>
                <button type="button" data-refinitiv-clear-date @if($date === '') hidden @endif class="inline-flex min-h-9 items-center justify-center rounded-lg border border-blue-200 px-2.5 text-xs font-semibold text-blue-800 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">Hapus filter tanggal</button>
            </div>
        </div>
        <div class="mb-3 flex flex-wrap gap-2" aria-label="Ringkasan status pada tanggal terpilih">
            @foreach ($calendarStatusLabels as $statusKey => $statusLabel)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-xs text-slate-700">{{ $statusLabel }} <strong class="tabular-nums" data-refinitiv-day-status="{{ $statusKey }}">{{ $calendarSelectedDay['statuses'][$statusKey] ?? 0 }}</strong></span>
            @endforeach
        </div>
        <div class="space-y-2" aria-label="Ringkasan pemohon menurut sesi">
            @foreach ($calendarSessionNames as $sessionKey => $sessionName)
                @php $sessionData = $calendarSelectedDay['sessions'][$sessionKey]; @endphp
                <div data-refinitiv-calendar-session="{{ $sessionKey }}" class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2 rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-700">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900">{{ $sessionName }}</p>
                        <p class="mt-0.5 tabular-nums text-slate-500" data-refinitiv-session-time="{{ $sessionKey }}">{{ $calendarSessionTimes[$sessionKey] }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 tabular-nums">
                        <span class="font-semibold text-slate-900"><span data-refinitiv-session-total>{{ $sessionData['total'] }}</span> total</span>
                        @foreach ($calendarStatusLabels as $statusKey => $statusLabel)
                            <span class="text-slate-500" aria-label="{{ $statusLabel }}"><span data-refinitiv-session-status="{{ $statusKey }}">{{ $sessionData['statuses'][$statusKey] ?? 0 }}</span> {{ $statusLabel }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-2 text-[11px] text-slate-500">Waktu sesi ke-3 mengikuti jadwal khusus hari Jumat.</p>
    </div>
    <script type="application/json" data-refinitiv-calendar-data>@json($calendarDays)</script>
</section>
