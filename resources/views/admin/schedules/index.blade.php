@extends('layouts.admin')

@section('title', 'Kelola Jadwal - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')

    @if($errors->any())
        <script>
            window.showToast?.(@json($errors->first()), 'error');
        </script>
    @endif

    <div class="admin-schedule-page py-0">
        @include('admin.schedules.partials.navigation', [
            'current' => 'Manajemen Jadwal',
            'backUrl' => route('admin.dashboard'),
            'backLabel' => 'Kembali ke Dashboard',
        ])

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 md:mb-6 gap-3 md:gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">Manajemen Jadwal</h1>
                <p class="text-sm md:text-base text-gray-600">Kelola semua jadwal laboratorium</p>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                {{-- View Toggle --}}
                <div class="flex bg-gray-100 rounded-lg p-1 shadow-inner" role="group" aria-label="Tampilan jadwal">
                    <button type="button" id="btn-view-list" aria-pressed="false" class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-gray-500 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span class="hidden sm:inline">Daftar</span>
                    </button>
                    <button type="button" id="btn-view-timetable" aria-pressed="false" class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-gray-500 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        <span class="hidden sm:inline">Tabel waktu</span>
                    </button>
                    <button type="button" id="btn-view-calendar" aria-pressed="false" class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-gray-500 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="hidden sm:inline">Kalender</span>
                    </button>
                </div>
                <a href="{{ route('admin.schedules.create') }}" 
                   class="schedule-primary-button flex flex-1 items-center justify-center rounded-lg px-4 py-3 text-sm font-semibold shadow-sm transition-all hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2 md:flex-none md:py-2 md:text-base">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Jadwal
                </a>
            </div>
        </div>

        {{-- ==================== LIST VIEW ==================== --}}
        <div id="list-view-section">
            <!-- Filters -->
            <section class="schedule-filter-panel mb-4 md:mb-6" aria-labelledby="schedule-filter-heading">
                <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-4 md:flex-row md:items-center md:justify-between md:px-5">
                    <div class="flex items-start gap-3">
                        <span class="schedule-filter-icon" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h18M6 12h12m-8 7h4"/></svg>
                        </span>
                        <div>
                            <h2 id="schedule-filter-heading" class="text-sm font-bold text-gray-900">Cari dan saring jadwal</h2>
                            <p class="mt-0.5 text-xs text-gray-500">Mulai dengan pencarian atau filter utama. Filter tanggal tersedia di bagian lanjutan.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="advanced-filter-count" class="hidden rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-bold text-yellow-800"></span>
                        <button type="button" id="btn-toggle-filters" aria-expanded="false" aria-controls="advanced-filters" class="schedule-secondary-button inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10m-7 6h4"/></svg>
                            <span>Filter lanjutan</span>
                            <svg data-filter-chevron class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <button type="button" id="btn-reset" class="schedule-secondary-button rounded-lg px-3 py-2 text-xs font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">Reset</button>
                    </div>
                </div>

                <div class="px-4 py-4 md:px-5">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">
                        <div class="md:col-span-6">
                            <label for="filter-search" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Pencarian</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.3-4.3m1.8-5.2a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" id="filter-search" value="{{ request('search') }}" placeholder="Cari kegiatan, mata kuliah, dosen, atau PIC" class="w-full rounded-lg border border-gray-300 py-2.5 pl-9 pr-3 text-sm text-gray-800 transition focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="filter-period" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Tampilkan</label>
                            <select id="filter-period" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200">
                                <option value="upcoming" {{ request('period', 'upcoming') === 'upcoming' ? 'selected' : '' }}>Mendatang & aktif</option>
                                <option value="history" {{ request('period') === 'history' ? 'selected' : '' }}>Histori</option>
                                <option value="all" {{ request('period') === 'all' ? 'selected' : '' }}>Semua jadwal</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="filter-lab" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Laboratorium</label>
                            <select id="filter-lab" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200">
                                <option value="">Semua laboratorium</option>
                                @foreach($labs as $lab)
                                    <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="filter-type" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Jenis jadwal</label>
                            <select id="filter-type" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200">
                                <option value="">Semua jenis</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div id="advanced-filters" class="hidden border-t border-gray-100 bg-gray-50/70 px-4 py-4 md:px-5" aria-hidden="true">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Penyaringan berdasarkan waktu</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Tanggal tertentu akan diprioritaskan dibandingkan bulan.</p>
                        </div>
                        <span class="text-xs font-medium text-gray-400">Opsional</span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label for="filter-month-trigger" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Bulan</label>
                            <div class="schedule-month-picker" data-schedule-month-picker data-month-placeholder="Pilih bulan" data-month-label="Bulan jadwal">
                                <input type="hidden" id="filter-month" data-month-input value="{{ request('month') }}">
                                <button type="button" id="filter-month-trigger" class="schedule-month-trigger" data-month-trigger aria-haspopup="dialog" aria-expanded="false" aria-controls="filter-month-calendar">
                                    <span class="block truncate" data-month-value>Belum dipilih</span>
                                    <svg class="schedule-month-trigger-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14A1.5 1.5 0 0120.5 6v13A1.5 1.5 0 0119 20.5H5A1.5 1.5 0 013.5 19V6A1.5 1.5 0 015 4.5z"/></svg>
                                </button>
                                <div id="filter-month-calendar" class="schedule-date-popover schedule-month-popover hidden" data-month-popover role="dialog" aria-label="Pilih bulan jadwal">
                                    <div class="schedule-date-header">
                                        <button type="button" class="schedule-date-nav" data-month-prev aria-label="Tahun sebelumnya">‹</button>
                                        <p class="schedule-date-month" data-month-year></p>
                                        <button type="button" class="schedule-date-nav" data-month-next aria-label="Tahun berikutnya">›</button>
                                    </div>
                                    <div class="schedule-month-grid" data-month-grid role="listbox" aria-label="Daftar bulan"></div>
                                    <div class="schedule-date-footer">
                                        <button type="button" class="schedule-date-link" data-month-current>Bulan ini</button>
                                        <button type="button" class="schedule-date-link schedule-date-clear" data-month-clear>Kosongkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="filter-date-trigger" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Tanggal tertentu</label>
                            <div class="schedule-date-picker" data-schedule-date-picker data-date-placeholder="Pilih tanggal" data-date-label="Tanggal jadwal">
                                <input type="hidden" id="filter-date" data-date-input value="{{ request('date') }}">
                                <button type="button" id="filter-date-trigger" class="schedule-date-trigger" data-date-trigger aria-haspopup="dialog" aria-expanded="false" aria-controls="filter-date-calendar">
                                    <span class="block truncate" data-date-value>Belum dipilih</span>
                                    <svg class="schedule-date-trigger-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14A1.5 1.5 0 0120.5 6v13A1.5 1.5 0 0119 20.5H5A1.5 1.5 0 013.5 19V6A1.5 1.5 0 015 4.5z"/></svg>
                                </button>
                                <div id="filter-date-calendar" class="schedule-date-popover hidden" data-date-popover role="dialog" aria-label="Pilih tanggal jadwal">
                                    <div class="schedule-date-header">
                                        <button type="button" class="schedule-date-nav" data-date-prev aria-label="Bulan sebelumnya">‹</button>
                                        <p class="schedule-date-month" data-date-month></p>
                                        <button type="button" class="schedule-date-nav" data-date-next aria-label="Bulan berikutnya">›</button>
                                    </div>
                                    <div class="schedule-date-weekdays" data-date-weekdays></div>
                                    <div class="schedule-date-grid" data-date-grid role="grid" aria-label="Kalender"></div>
                                    <div class="schedule-date-footer">
                                        <button type="button" class="schedule-date-link" data-date-today>Hari ini</button>
                                        <button type="button" class="schedule-date-link schedule-date-clear" data-date-clear>Kosongkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="filter-day" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Hari</label>
                            <select id="filter-day" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200">
                                <option value="">Semua hari</option>
                                <option value="Senin" {{ request('day') == 'Senin' ? 'selected' : '' }}>Senin</option>
                                <option value="Selasa" {{ request('day') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                                <option value="Rabu" {{ request('day') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                                <option value="Kamis" {{ request('day') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                                <option value="Jumat" {{ request('day') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                                <option value="Sabtu" {{ request('day') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Schedule Table/Cards -->
            <div id="schedule-container" class="bg-white rounded-xl shadow-md overflow-hidden">
                @include('admin.schedules.partials.table', ['schedules' => $schedules])
            </div>
        </div>

        {{-- ==================== TIMETABLE VIEW ==================== --}}
        <div id="timetable-view-section" class="hidden">
            {{-- Week Navigation and Context --}}
            <section class="schedule-timetable-toolbar mb-4 md:mb-6" aria-labelledby="timetable-heading">
                <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-4 md:flex-row md:items-center md:justify-between md:px-5">
                    <div class="flex items-start gap-3">
                        <span class="schedule-filter-icon" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14A1.5 1.5 0 0120.5 6v13A1.5 1.5 0 0119 20.5H5A1.5 1.5 0 013.5 19V6A1.5 1.5 0 015 4.5z"/></svg>
                        </span>
                        <div>
                            <h2 id="timetable-heading" class="text-sm font-bold text-gray-900">Tabel waktu mingguan</h2>
                            <p class="mt-0.5 text-xs text-gray-500">Pilih hari untuk melihat kepadatan jadwal setiap laboratorium.</p>
                        </div>
                    </div>
                    <div class="tt-timetable-actions">
                        <span id="tt-view-summary" class="tt-view-summary">Memuat jadwal...</span>
                        <button type="button" id="tt-today-btn" class="schedule-primary-button inline-flex items-center justify-center rounded-lg px-3 py-2 text-xs font-bold shadow-sm transition-all hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">Hari ini</button>
                    </div>
                </div>

                <div class="schedule-timetable-controls px-4 py-4 md:px-5">
                    <button type="button" id="tt-prev-week" class="schedule-icon-button rounded-lg p-2 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2" aria-label="Pekan sebelumnya">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                    </button>
                    <div class="min-w-0 flex-1 text-center">
                        <h3 id="tt-week-label" class="truncate text-sm font-bold text-gray-900 md:text-base">Memuat...</h3>
                        <p class="mt-0.5 text-xs text-gray-500">Gunakan panah untuk berpindah pekan</p>
                    </div>
                    <div class="tt-date-control w-full sm:w-52">
                        <label for="tt-date-picker-trigger" class="mb-1.5 block text-left text-[11px] font-bold uppercase tracking-wide text-gray-500">Pekan yang memuat tanggal</label>
                        <div class="schedule-date-picker" data-schedule-date-picker data-date-placeholder="Pilih tanggal" data-date-label="Pekan">
                            <input type="hidden" id="tt-date-picker" data-date-input>
                            <button type="button" id="tt-date-picker-trigger" class="schedule-date-trigger" data-date-trigger aria-haspopup="dialog" aria-expanded="false" aria-controls="tt-date-picker-calendar">
                                <span class="block truncate" data-date-value>Belum dipilih</span>
                                <svg class="schedule-date-trigger-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14A1.5 1.5 0 0120.5 6v13A1.5 1.5 0 0119 20.5H5A1.5 1.5 0 013.5 19V6A1.5 1.5 0 015 4.5z"/></svg>
                            </button>
                            <div id="tt-date-picker-calendar" class="schedule-date-popover hidden" data-date-popover role="dialog" aria-label="Pilih tanggal pekan">
                                <div class="schedule-date-header">
                                    <button type="button" class="schedule-date-nav" data-date-prev aria-label="Bulan sebelumnya">‹</button>
                                    <p class="schedule-date-month" data-date-month></p>
                                    <button type="button" class="schedule-date-nav" data-date-next aria-label="Bulan berikutnya">›</button>
                                </div>
                                <div class="schedule-date-weekdays" data-date-weekdays></div>
                                <div class="schedule-date-grid" data-date-grid role="grid" aria-label="Kalender"></div>
                                <div class="schedule-date-footer">
                                    <button type="button" class="schedule-date-link" data-date-today>Hari ini</button>
                                    <button type="button" class="schedule-date-link schedule-date-clear" data-date-clear>Kosongkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="tt-next-week" class="schedule-icon-button rounded-lg p-2 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2" aria-label="Pekan berikutnya">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                    </button>
                </div>

                <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/60 px-4 py-3 md:flex-row md:items-end md:justify-between md:px-5">
                    <div class="w-full md:max-w-xs">
                        <label for="tt-lab-filter" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-gray-500">Laboratorium</label>
                        <select id="tt-lab-filter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-200">
                            <option value="">Semua laboratorium</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-600" aria-label="Legenda jenis jadwal">
                        <span class="inline-flex items-center gap-2"><span class="timetable-legend-dot timetable-legend-fixed"></span>Perkuliahan tetap</span>
                        <span class="inline-flex items-center gap-2"><span class="timetable-legend-dot timetable-legend-flexible"></span>Perkuliahan tidak tetap</span>
                        <span class="inline-flex items-center gap-2"><span class="timetable-legend-dot timetable-legend-other"></span>Non-perkuliahan</span>
                    </div>
                </div>

                {{-- Day Tabs --}}
                <div id="tt-day-tabs" class="tt-day-tabs" role="tablist" aria-label="Pilih hari"></div>
            </section>

            {{-- Timetable Grid --}}
            <div id="tt-grid-container" class="schedule-timetable-grid overflow-x-auto"></div>
        </div>

        {{-- ==================== INTERACTIVE CALENDAR VIEW ==================== --}}
        <div id="calendar-view-section" class="hidden">
            <div class="bg-white rounded-xl shadow-md p-4 mb-4">
                <div class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="w-full md:w-72">
                        <label for="calendar-lab-filter" class="block text-sm font-semibold text-gray-700 mb-1">Filter laboratorium</label>
                        <select id="calendar-lab-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                            <option value="">Semua laboratorium</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-sm text-gray-600">Gunakan tampilan Pekan atau Hari untuk memilih waktu dan melakukan drag/resize. Tampilan Bulan digunakan untuk ringkasan.</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-3 md:p-5 overflow-x-auto">
                <div data-admin-schedule-calendar
                     data-events-url="{{ route('admin.schedules.calendar.events') }}"
                     data-change-url="{{ route('admin.schedules.calendar.change', '__ID__') }}"
                     data-create-url="{{ route('admin.schedules.create') }}"
                     data-edit-url="{{ route('admin.schedules.edit', '__ID__') }}"
                     class="min-w-[760px]"></div>
            </div>
            <p id="calendar-live-region" class="sr-only" aria-live="polite"></p>
        </div>
    </div>

    <dialog id="calendar-change-dialog" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl p-0 shadow-2xl backdrop:bg-black/50">
        <form id="calendar-change-form" method="dialog" class="bg-white">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">Konfirmasi perubahan jadwal</h2>
                <p id="calendar-change-summary" class="text-sm text-gray-600 mt-1"></p>
            </div>
            <div class="p-6 space-y-4">
                <div id="calendar-change-scope-group">
                    <span class="block text-sm font-semibold text-gray-700 mb-2">Lingkup perubahan</span>
                    <label class="flex items-center gap-2 mb-2">
                        <input type="radio" name="calendar_change_scope" value="single" checked>
                        <span class="text-sm"><span class="font-medium">Hanya pertemuan ini</span><span class="block text-xs text-gray-500">Histori dan pertemuan lain tidak berubah.</span></span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="calendar_change_scope" value="future">
                        <span class="text-sm"><span class="font-medium">Pertemuan ini dan seterusnya</span><span class="block text-xs text-gray-500">Membuat perubahan berlaku mulai occurrence ini.</span></span>
                    </label>
                </div>
                <div>
                    <label for="calendar-change-lab" class="block text-sm font-semibold text-gray-700 mb-1">Laboratorium</label>
                    <select id="calendar-change-lab" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="calendar-change-reason" class="block text-sm font-semibold text-gray-700 mb-1">Alasan perubahan *</label>
                    <textarea id="calendar-change-reason" required maxlength="1000" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                              placeholder="Contoh: penyesuaian ruang dari program studi"></textarea>
                </div>
                <p id="calendar-change-error" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3" role="alert"></p>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                <button type="button" id="calendar-change-cancel" class="schedule-secondary-button rounded-lg px-4 py-2 font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">Batalkan</button>
                <button type="submit" class="schedule-primary-button rounded-lg px-4 py-2 font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">Simpan perubahan</button>
            </div>
        </form>
    </dialog>

    {{-- ==================== DELETE MODAL ==================== --}}
    <div id="delete-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 bg-red-50 border-b border-red-100">
                <h3 class="text-lg font-bold text-red-800">Batalkan Jadwal</h3>
                <p id="delete-course" class="text-sm text-red-700"></p>
            </div>
            <div class="p-6 space-y-4">
                <div id="delete-scope-all" class="flex items-start gap-3 cursor-pointer">
                    <input type="radio" name="delete_scope" value="all" checked class="mt-1 w-4 h-4 text-red-500 focus:ring-red-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Batalkan seluruh jadwal mendatang</p>
                        <p class="text-xs text-gray-500">Pertemuan yang sudah terlaksana tetap utuh; jadwal mendatang dicatat sebagai dibatalkan.</p>
                    </div>
                </div>
                <div id="delete-scope-single" class="hidden items-start gap-3 cursor-pointer">
                    <input type="radio" name="delete_scope" value="single" class="mt-1 w-4 h-4 text-red-500 focus:ring-red-500">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800">Batalkan di hari itu saja</p>
                        <p class="text-xs text-gray-500 mb-2">Membatalkan hanya satu pertemuan pada tanggal tertentu.</p>
                        <input type="date" id="delete-single-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500">
                    </div>
                </div>
                <div id="delete-scope-future" class="hidden items-start gap-3 cursor-pointer">
                    <input type="radio" name="delete_scope" value="future" class="mt-1 w-4 h-4 text-red-500 focus:ring-red-500">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800">Kelas ini &amp; selanjutnya</p>
                        <p class="text-xs text-gray-500 mb-2">Membatalkan mulai tanggal terpilih hingga akhir rangkaian.</p>
                        <input type="date" id="delete-future-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label for="delete-reason" class="block text-sm font-semibold text-gray-700 mb-1">Alasan pembatalan *</label>
                    <textarea id="delete-reason" rows="2" maxlength="1000"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500"
                              placeholder="Contoh: kegiatan dibatalkan oleh program studi"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" id="delete-cancel" class="schedule-secondary-button flex-1 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">
                        Batal
                    </button>
                    <button type="button" id="delete-confirm" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg text-sm">
                        Batalkan Jadwal
                    </button>
                </div>
            </div>
        </div>
    </div>
    <form id="delete-form" method="POST" action="" class="hidden">
        @csrf
        @method('DELETE')
        <input type="hidden" name="scope" id="delete-form-scope" value="all">
        <input type="hidden" name="occurrence_date" id="delete-form-date">
        <input type="hidden" name="change_reason" id="delete-form-reason">
    </form>
    <script>
        // Custom Dropdown Implementation (Reused from form.blade.php)
        class CustomSelect {
            constructor(originalSelect) {
                this.originalSelect = originalSelect;
                if (this.originalSelect.dataset.customSelectReady) return;
                this.originalSelect.dataset.customSelectReady = 'true';
                this.originalSelect.classList.add('custom-select-native');
                this.originalSelect.setAttribute('aria-hidden', 'true');
                this.originalSelect.tabIndex = -1;
                
                // Create wrapper
                this.wrapper = document.createElement('div');
                this.wrapper.className = 'custom-select-wrapper w-full';
                this.originalSelect.parentNode.insertBefore(this.wrapper, this.originalSelect);
                this.wrapper.appendChild(this.originalSelect); // Move original inside
                
                // Create Trigger Element
                this.trigger = document.createElement('button');
                this.trigger.type = 'button';
                this.trigger.className = 'custom-select-trigger';
                this.trigger.setAttribute('aria-haspopup', 'listbox');
                this.trigger.setAttribute('aria-expanded', 'false');
                const label = document.querySelector(`label[for="${CSS.escape(this.originalSelect.id)}"]`);
                if (label) {
                    label.id ||= `${this.originalSelect.id}-label`;
                    this.trigger.setAttribute('aria-labelledby', label.id);
                } else {
                    this.trigger.setAttribute('aria-label', this.originalSelect.getAttribute('aria-label') || this.originalSelect.name || 'Pilih opsi');
                }
                
                // Content span
                this.triggerLabel = document.createElement('span');
                this.triggerLabel.className = 'block truncate';
                
                // Chevron icon
                const chevron = document.createElement('div');
                chevron.innerHTML = `<svg class="custom-select-chevron h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
                this.chevronIcon = chevron.firstElementChild;

                this.trigger.appendChild(this.triggerLabel);
                this.trigger.appendChild(chevron);
                this.wrapper.appendChild(this.trigger);

                // Create Options Container
                this.optionsContainer = document.createElement('div');
                const selectId = this.originalSelect.id || `custom-select-${Math.random().toString(36).slice(2)}`;
                this.optionsContainer.id = `${selectId}-options`;
                this.optionsContainer.setAttribute('role', 'listbox');
                this.trigger.setAttribute('aria-controls', this.optionsContainer.id);
                this.optionsContainer.className = 'custom-select-options hidden scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100';
                this.wrapper.appendChild(this.optionsContainer);

                // Initialize
                this.initOptions();
                this.updateTrigger();

                // Event Listeners
                this.trigger.addEventListener('click', (e) => {
                    if (this.trigger.hasAttribute('disabled')) return;
                    e.stopPropagation();
                    this.toggleDropdown();
                });

                // Close when clicking outside
                document.addEventListener('click', (e) => {
                    if (!this.wrapper.contains(e.target)) {
                        this.closeDropdown();
                    }
                });

                // Listen for changes
                this.originalSelect.addEventListener('change', () => {
                   this.updateTrigger();
                   this.initOptions();
                });
            }

            initOptions() {
                this.optionsContainer.innerHTML = '';
                Array.from(this.originalSelect.options).forEach(option => {
                     const optionDiv = document.createElement('div');
                     optionDiv.setAttribute('role', 'option');
                     optionDiv.setAttribute('aria-selected', option.selected ? 'true' : 'false');
                     optionDiv.tabIndex = option.disabled ? -1 : 0;
                     optionDiv.className = 'custom-select-option';
                    optionDiv.textContent = option.text;

                    if (option.disabled) {
                        optionDiv.classList.add('is-disabled');
                        optionDiv.setAttribute('aria-disabled', 'true');
                    }
                    
                    if (option.selected) {
                        optionDiv.classList.add('is-active');
                        const check = document.createElement('span');
                        check.className = 'ml-auto inline-flex items-center pl-4 text-yellow-700';
                        check.innerHTML = `<svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`;
                        optionDiv.appendChild(check);
                    }

                     optionDiv.addEventListener('click', (e) => {
                        if (option.disabled) return;
                        e.stopPropagation();
                        this.originalSelect.value = option.value;
                        this.originalSelect.dispatchEvent(new Event('change', { bubbles: true }));
                         this.closeDropdown();
                     });

                     optionDiv.addEventListener('keydown', (e) => {
                         const options = Array.from(this.optionsContainer.querySelectorAll('[role="option"]'));
                         const currentIndex = options.indexOf(optionDiv);
                         if (e.key === 'Enter' || e.key === ' ') {
                             e.preventDefault();
                             optionDiv.click();
                         } else if (e.key === 'ArrowDown' && options[currentIndex + 1]) {
                             e.preventDefault();
                             options[currentIndex + 1].focus();
                         } else if (e.key === 'ArrowUp' && options[currentIndex - 1]) {
                             e.preventDefault();
                             options[currentIndex - 1].focus();
                         } else if (e.key === 'Escape') {
                             e.preventDefault();
                             this.closeDropdown();
                             this.trigger.focus();
                         }
                     });

                    this.optionsContainer.appendChild(optionDiv);
                });
            }

            updateTrigger() {
                const selectedOption = this.originalSelect.options[this.originalSelect.selectedIndex];
                this.triggerLabel.textContent = selectedOption ? selectedOption.text : 'Pilih...';
            }

            toggleDropdown() {
                const isHidden = this.optionsContainer.classList.contains('hidden');
                // Close others
                document.querySelectorAll('.custom-select-wrapper .custom-select-options').forEach(el => {
                    if (!el.classList.contains('hidden') && el !== this.optionsContainer) {
                        el.classList.add('hidden');
                        const otherTrigger = el.parentElement.querySelector('.custom-select-trigger');
                        if (otherTrigger) otherTrigger.setAttribute('aria-expanded', 'false');
                        const otherChevron = el.parentElement.querySelector('.custom-select-chevron');
                        if (otherChevron) otherChevron.classList.remove('rotate-180');
                    }
                });

                if (isHidden) {
                    this.optionsContainer.classList.remove('hidden');
                    this.chevronIcon.classList.add('rotate-180');
                    this.trigger.setAttribute('aria-expanded', 'true');
                    this.optionsContainer.querySelector('[aria-selected="true"]')?.focus();
                } else {
                    this.closeDropdown();
                }
            }

            closeDropdown() {
                this.optionsContainer.classList.add('hidden');
                this.chevronIcon.classList.remove('rotate-180');
                this.trigger.setAttribute('aria-expanded', 'false');
            }
        }

        // AJAX Filter Implementation
        let searchTimeout = null;
        let suppressFilterRequests = false;
        const DEBOUNCE_DELAY = 400; // milliseconds
        const FILTER_STORAGE_KEY = 'admin_schedules_filters';

        // Save filters to localStorage
        function saveFiltersToLocalStorage() {
            try {
                const filters = {
                    month: document.getElementById('filter-month').value,
                    date: document.getElementById('filter-date').value,
                    lab: document.getElementById('filter-lab').value,
                    day: document.getElementById('filter-day').value,
                    period: document.getElementById('filter-period').value,
                    type: document.getElementById('filter-type').value,
                    search: document.getElementById('filter-search').value
                };
                localStorage.setItem(FILTER_STORAGE_KEY, JSON.stringify(filters));
            } catch (e) {
                console.warn('Failed to save filters to localStorage:', e);
            }
        }

        // Load filters from localStorage (silent mode - no AJAX trigger)
        function loadFiltersFromLocalStorage() {
            const savedFilters = localStorage.getItem(FILTER_STORAGE_KEY);
            if (!savedFilters) return false;

            try {
                const filters = JSON.parse(savedFilters);
                
                // Set values silently (before event listeners are attached)
                document.getElementById('filter-month').value = filters.month || '';
                document.getElementById('filter-month').closest('[data-schedule-month-picker]')?.__scheduleMonthPicker?.updateTrigger();
                document.getElementById('filter-date').value = filters.date || '';
                document.getElementById('filter-lab').value = filters.lab || '';
                document.getElementById('filter-day').value = filters.day || '';
                document.getElementById('filter-period').value = filters.period || 'upcoming';
                document.getElementById('filter-type').value = filters.type || '';
                document.getElementById('filter-search').value = filters.search || '';
                
                return true; // Filters were loaded
            } catch (e) {
                console.error('Error loading filters from localStorage:', e);
                return false;
            }
        }

        // Clear filters from localStorage
        function clearFiltersFromLocalStorage() {
            localStorage.removeItem(FILTER_STORAGE_KEY);
        }

        function updateAdvancedFilterState() {
            const activeCount = [
                document.getElementById('filter-month').value,
                document.getElementById('filter-date').value,
                document.getElementById('filter-day').value,
            ].filter(Boolean).length;
            const countBadge = document.getElementById('advanced-filter-count');
            if (!countBadge) return;

            countBadge.textContent = activeCount ? `${activeCount} aktif` : '';
            countBadge.classList.toggle('hidden', activeCount === 0);
        }

        function setAdvancedFiltersOpen(isOpen) {
            const panel = document.getElementById('advanced-filters');
            const toggle = document.getElementById('btn-toggle-filters');
            if (!panel || !toggle) return;

            panel.classList.toggle('hidden', !isOpen);
            panel.setAttribute('aria-hidden', String(!isOpen));
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.querySelector('[data-filter-chevron]')?.classList.toggle('rotate-180', isOpen);
        }

        function closeScheduleMenus(returnFocus = false) {
            document.querySelectorAll('[data-table-menu]:not(.hidden)').forEach(menu => {
                menu.classList.add('hidden');
                menu.style.removeProperty('left');
                menu.style.removeProperty('top');
                menu.style.removeProperty('visibility');
                const button = document.querySelector(`[data-menu-target="${menu.id}"]`);
                button?.setAttribute('aria-expanded', 'false');
                if (returnFocus && button) button.focus();
            });
        }

        function positionScheduleMenu(menu, button) {
            const buttonRect = button.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            const gap = 6;
            const viewportPadding = 8;
            const canOpenBelow = buttonRect.bottom + gap + menuRect.height <= window.innerHeight - viewportPadding;
            const top = canOpenBelow
                ? buttonRect.bottom + gap
                : Math.max(viewportPadding, buttonRect.top - menuRect.height - gap);
            const left = Math.min(
                Math.max(viewportPadding, buttonRect.right - menuRect.width),
                Math.max(viewportPadding, window.innerWidth - menuRect.width - viewportPadding),
            );

            menu.style.left = `${left}px`;
            menu.style.top = `${top}px`;
        }

        function loadSchedules(url = null, append = false) {
            const container = document.getElementById('schedule-container');
            
            // If not append (filtering), show loading state
            if (!append) {
                container.innerHTML = `
                    <div class="p-8 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-500 mx-auto"></div>
                        <p class="text-gray-500 mt-2 text-sm">Memuat jadwal...</p>
                    </div>
                `;
            } else {
                // Show loading on button
                const btn = document.getElementById('btn-load-more');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = `<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-yellow-700 mr-2"></div> Memuat...`;
                }
            }

            // Build URL
            let fetchUrl;
            if (url) {
                fetchUrl = url;
            } else {
                // Save current filters to localStorage only when filtering (not loading more)
                saveFiltersToLocalStorage();
                
                const filterMonth = document.getElementById('filter-month').value;
                const filterDate = document.getElementById('filter-date').value;
                const filterLab = document.getElementById('filter-lab').value;
                const filterDay = document.getElementById('filter-day').value;
                const filterPeriod = document.getElementById('filter-period').value;
                const filterType = document.getElementById('filter-type').value;
                const filterSearch = document.getElementById('filter-search').value;

                // Build query string
                const params = new URLSearchParams();
                if (filterMonth) params.append('month', filterMonth);
                if (filterDate) params.append('date', filterDate);
                if (filterLab) params.append('lab_id', filterLab);
                if (filterDay) params.append('day', filterDay);
                if (filterPeriod && filterPeriod !== 'upcoming') params.append('period', filterPeriod);
                if (filterType) params.append('type', filterType);
                if (filterSearch) params.append('search', filterSearch);
                
                fetchUrl = `{{ route('admin.schedules.index') }}?${params.toString()}`;
            }

            // Fetch with AJAX
            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                if (append) {
                    // APPEND MODE: Parse HTML and append rows/cards
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Append Desktop Rows
                    const newRows = doc.querySelectorAll('#desktop-table tbody tr');
                    const currentTbody = document.querySelector('#desktop-table tbody');
                    if (currentTbody && newRows.length) {
                        newRows.forEach(row => currentTbody.appendChild(row));
                    }

                    // Append Mobile Cards
                    const newCards = doc.querySelectorAll('#mobile-cards > div'); // direct children divs
                    const currentCards = document.getElementById('mobile-cards');
                    if (currentCards && newCards.length) {
                        newCards.forEach(card => currentCards.appendChild(card));
                    }

                    // Update Load More Button (replace container)
                    const newLoadMore = doc.getElementById('load-more-container');
                    const currentLoadMore = document.getElementById('load-more-container');
                    if (newLoadMore) {
                        if (currentLoadMore) {
                            currentLoadMore.replaceWith(newLoadMore);
                        } else {
                             // Insert after mobile cards (or schedule container end)
                             container.appendChild(newLoadMore); 
                        }
                    } else if (currentLoadMore) {
                        currentLoadMore.remove(); // No more pages
                    }

                    // Update Total Count
                    const newCount = doc.getElementById('schedule-count');
                    const currentCount = document.getElementById('schedule-count');
                    if (newCount && currentCount) {
                        currentCount.replaceWith(newCount);
                    }

                } else {
                    // REPLACE MODE: Just replace innerHTML
                    container.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error loading schedules:', error);
                // Error handling...
                const errorHtml = `
                    <div class="p-8 text-center text-red-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Gagal memuat jadwal. Silakan coba lagi.
                    </div>
                `;

                if (!append) {
                    container.innerHTML = errorHtml;
                    window.showToast?.('Gagal memuat jadwal. Silakan coba lagi.', 'error');
                } else {
                    // Revert button state if append failed
                     const btn = document.getElementById('btn-load-more');
                     if(btn) {
                        btn.disabled = false;
                        btn.innerHTML = `<span>Coba Lagi</span>`;
                     }
                     window.showToast?.('Gagal memuat halaman berikutnya.', 'error');
                }
            });
        }

        function resetFilters() {
            suppressFilterRequests = true;
            document.getElementById('filter-month').value = '';
            document.getElementById('filter-month').closest('[data-schedule-month-picker]')?.__scheduleMonthPicker?.updateTrigger();
            document.getElementById('filter-date').value = '';
            document.getElementById('filter-date').closest('[data-schedule-date-picker]')?.__scheduleDatePicker?.updateTrigger();
            document.getElementById('filter-lab').value = '';
            document.getElementById('filter-day').value = '';
            document.getElementById('filter-period').value = 'upcoming';
            document.getElementById('filter-type').value = '';
            document.getElementById('filter-search').value = '';
            setAdvancedFiltersOpen(false);
            updateAdvancedFilterState();
            
            // Trigger change events to update custom selects
            document.getElementById('filter-lab').dispatchEvent(new Event('change'));
            document.getElementById('filter-day').dispatchEvent(new Event('change'));
            document.getElementById('filter-period').dispatchEvent(new Event('change'));
            document.getElementById('filter-type').dispatchEvent(new Event('change'));
            suppressFilterRequests = false;
            
            // Clear from localStorage
            clearFiltersFromLocalStorage();
            
            loadSchedules();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved filters from localStorage
            const filtersLoaded = loadFiltersFromLocalStorage();

            // Use the same custom select surface for filters and calendar dialogs.
            document.querySelectorAll('select').forEach(select => new CustomSelect(select));

            // Add event listeners for auto-filter
            const triggerLoad = () => {
                if (!suppressFilterRequests) loadSchedules();
            }; // Default: replace mode

            document.getElementById('filter-month').addEventListener('change', function() {
                // Mutual exclusion: Clear date if month is selected
                if (this.value) {
                    const dateInput = document.getElementById('filter-date');
                    dateInput.value = '';
                    dateInput.closest('[data-schedule-date-picker]')?.__scheduleDatePicker?.updateTrigger();
                }
                updateAdvancedFilterState();
                triggerLoad();
            });

            document.getElementById('filter-date').addEventListener('change', function() {
                // Mutual exclusion: Clear month if date is selected
                if (this.value) {
                    document.getElementById('filter-month').value = '';
                }
                updateAdvancedFilterState();
                triggerLoad();
            });
            document.getElementById('filter-lab').addEventListener('change', triggerLoad);
            document.getElementById('filter-day').addEventListener('change', function() {
                updateAdvancedFilterState();
                triggerLoad();
            });
            document.getElementById('filter-period').addEventListener('change', triggerLoad);
            document.getElementById('filter-type').addEventListener('change', triggerLoad);
            
            document.getElementById('filter-search').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(triggerLoad, DEBOUNCE_DELAY);
            });
            
            document.getElementById('btn-reset').addEventListener('click', resetFilters);

            const advancedValues = [
                document.getElementById('filter-month').value,
                document.getElementById('filter-date').value,
                document.getElementById('filter-day').value,
            ];
            setAdvancedFiltersOpen(advancedValues.some(Boolean));
            updateAdvancedFilterState();

            document.getElementById('btn-toggle-filters').addEventListener('click', function() {
                const isOpen = this.getAttribute('aria-expanded') === 'true';
                setAdvancedFiltersOpen(!isOpen);
            });

            // Event Delegation for Load More Button
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('#btn-load-more');
                if (btn) {
                    const nextUrl = btn.getAttribute('data-next-url');
                    if (nextUrl) {
                        loadSchedules(nextUrl, true); // Append mode
                    }
                }
            });

            // Table actions use one delegated menu so they keep working after AJAX replacement.
            document.addEventListener('click', function(e) {
                const menuButton = e.target.closest('[data-table-menu-button]');
                if (menuButton) {
                    e.stopPropagation();
                    const menu = document.getElementById(menuButton.dataset.menuTarget);
                    if (!menu) return;

                    const isOpening = menu.classList.contains('hidden');
                    closeScheduleMenus();
                    if (isOpening) {
                        menu.classList.remove('hidden');
                        menu.style.visibility = 'hidden';
                        positionScheduleMenu(menu, menuButton);
                        menu.style.visibility = '';
                        menuButton.setAttribute('aria-expanded', 'true');
                        menu.querySelector('[role="menuitem"]')?.focus();
                    }
                    return;
                }

                if (!e.target.closest('[data-table-menu]')) closeScheduleMenus();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeScheduleMenus(true);
            });

            // Repositioning is unnecessary while scrolling and can leave a popover
            // detached from its trigger, so close it when the viewport changes.
            window.addEventListener('scroll', () => closeScheduleMenus(), true);
            window.addEventListener('resize', () => closeScheduleMenus());

            // Initial load if filters exist in localStorage
            if (filtersLoaded) {
                loadSchedules();
            }

            // ==================== VIEW TOGGLE ====================
            initViewToggle();
        });

        // ==================== TIMETABLE VIEW ====================
        const TT_DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const TT_TIME_START = 5;
        const TT_TIME_END = 23;
        const TT_SLOT_MINUTES = 10;
        const TT_ROW_HEIGHT = 10;
        const TT_TOTAL_SLOTS = (TT_TIME_END - TT_TIME_START) * 60 / TT_SLOT_MINUTES;
        const TT_TYPE_COLORS = {
            'perkuliahan_tetap':       { bg: 'bg-yellow-100', accent: 'bg-yellow-500', border: 'border-yellow-300', shadow: 'shadow-yellow-100', text: 'text-yellow-900' },
            'perkuliahan_tidak_tetap': { bg: 'bg-indigo-50', accent: 'bg-indigo-500', border: 'border-indigo-200', shadow: 'shadow-indigo-100', text: 'text-indigo-900' },
            'non_perkuliahan':         { bg: 'bg-emerald-50', accent: 'bg-emerald-500', border: 'border-emerald-200', shadow: 'shadow-emerald-100', text: 'text-emerald-900' }
        };
        const TT_BASE_URL = `{{ url('/admin/schedules') }}`;

        let ttWeekData = null;
        let ttSelectedDay = null;
        let ttSelectedDate = null;
        let ttSelectedLabId = '';
        let ttAllLabs = [];
        let ttWeekOffset = 0;
        let ttDeleteInProgress = false;

        // Helpers
        function ttEscHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        function ttFormatTime(t) {
            if (!t || typeof t !== 'string') return '';
            const match = t.match(/(\d{1,2}):(\d{2})/);
            if (match) return match[1].padStart(2, '0') + ':' + match[2];
            return '';
        }
        function ttTimeToMinutes(t) {
            const str = ttFormatTime(t);
            if (!str) return 0;
            const [h, m] = str.split(':').map(Number);
            return h * 60 + m;
        }
        function ttDateStr(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
        }

        function ttSetDatePickerValue(value) {
            const input = document.getElementById('tt-date-picker');
            if (!input) return;

            input.value = value || '';
            const picker = input.closest('[data-schedule-date-picker]')?.__scheduleDatePicker;
            picker?.updateTrigger();
            picker?.render();
        }

        function ttUpdateSummary(scheduleCount = null) {
            const summary = document.getElementById('tt-view-summary');
            if (!summary) return;

            const labLabel = ttSelectedLabId
                ? document.querySelector(`#tt-lab-filter option[value="${CSS.escape(String(ttSelectedLabId))}"]`)?.textContent
                : 'Semua laboratorium';
            const count = scheduleCount ?? (ttWeekData?.schedules || []).filter(s => s.date === ttSelectedDate).length;
            summary.textContent = `${count} jadwal · ${labLabel || 'Laboratorium'}`;
            summary.classList.remove('hidden');
        }

        function ttExtractId(idStr) {
            // 'sched_123' -> '123'
            return idStr ? idStr.replace('sched_', '') : '';
        }

        // ==================== VIEW TOGGLE ====================
        function initViewToggle() {
            const btnList = document.getElementById('btn-view-list');
            const btnTimetable = document.getElementById('btn-view-timetable');
            const btnCalendar = document.getElementById('btn-view-calendar');
            const viewPreferenceKey = 'admin_schedule_view_v2';

            btnList.addEventListener('click', () => switchView('list'));
            btnTimetable.addEventListener('click', () => switchView('timetable'));
            btnCalendar.addEventListener('click', () => switchView('calendar'));

            // Calendar is the primary operational view. Keep a user's explicit
            // choice for later visits, while using a new key so the former
            // list default does not override this migration.
            const savedView = localStorage.getItem(viewPreferenceKey) || 'calendar';
            switchView(savedView);

            window.scheduleViewPreferenceKey = viewPreferenceKey;
        }

        function switchView(mode) {
            const listSection = document.getElementById('list-view-section');
            const ttSection = document.getElementById('timetable-view-section');
            const calendarSection = document.getElementById('calendar-view-section');
            const btnList = document.getElementById('btn-view-list');
            const btnTimetable = document.getElementById('btn-view-timetable');
            const btnCalendar = document.getElementById('btn-view-calendar');

            [listSection, ttSection, calendarSection].forEach(section => section.classList.add('hidden'));
            [btnList, btnTimetable, btnCalendar].forEach(button => {
                button.classList.remove('bg-white', 'shadow-sm', 'text-yellow-700');
                button.classList.add('text-gray-500');
                button.setAttribute('aria-pressed', 'false');
            });

            if (mode === 'timetable') {
                ttSection.classList.remove('hidden');
                btnTimetable.classList.add('bg-white', 'shadow-sm', 'text-yellow-700');
                btnTimetable.classList.remove('text-gray-500');
                btnTimetable.setAttribute('aria-pressed', 'true');

                // Load timetable data on first switch
                if (!ttWeekData) {
                    ttLoadWeek();
                }
            } else if (mode === 'calendar') {
                calendarSection.classList.remove('hidden');
                btnCalendar.classList.add('bg-white', 'shadow-sm', 'text-yellow-700');
                btnCalendar.classList.remove('text-gray-500');
                btnCalendar.setAttribute('aria-pressed', 'true');
                window.dispatchEvent(new CustomEvent('schedule-calendar-visible'));
            } else {
                listSection.classList.remove('hidden');
                btnList.classList.add('bg-white', 'shadow-sm', 'text-yellow-700');
                btnList.classList.remove('text-gray-500');
                btnList.setAttribute('aria-pressed', 'true');
            }

            localStorage.setItem(window.scheduleViewPreferenceKey || 'admin_schedule_view_v2', mode);
        }

        // ==================== TIMETABLE API ====================
        function ttLoadWeek(targetDate) {
            let url = `{{ route('schedules.week') }}`;
            if (targetDate) {
                url += `?date=${targetDate}`;
            } else if (ttWeekOffset !== 0) {
                url += `?week_offset=${ttWeekOffset}`;
            }

            // Show loading
            document.getElementById('tt-grid-container').innerHTML = `
                <div class="timetable-feedback">
                    <div class="animate-spin h-8 w-8 rounded-full border-2 border-yellow-200 border-t-yellow-600"></div>
                    <p class="mt-3 text-sm font-semibold text-gray-600">Memuat tabel waktu...</p>
                </div>`;

            fetch(url)
                .then(r => {
                    if (!r.ok) throw new Error(`Timetable request failed with status ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    ttWeekData = data;
                    ttAllLabs = data.labs || [];

                    // Update week label and the custom date picker without triggering a second request.
                    document.getElementById('tt-week-label').textContent = data.week_label || '';
                    ttSetDatePickerValue(data.week_start || '');

                    // Build day tabs
                    ttRenderDayTabs(data);

                    // Select appropriate day
                    if (targetDate) {
                        const target = new Date(targetDate + 'T00:00:00');
                        const dow = target.getDay();
                        const idx = dow === 0 ? 5 : dow - 1;
                        if (idx >= 0 && idx < 6) {
                            ttSelectDay(TT_DAYS[idx], targetDate);
                        } else {
                            ttSelectDay(TT_DAYS[0], data.week_start);
                        }
                    } else {
                        const today = new Date();
                        const todayStr = ttDateStr(today);
                        if (todayStr >= data.week_start && todayStr <= data.week_end) {
                            const dow = today.getDay();
                            const idx = dow === 0 ? -1 : dow - 1;
                            if (idx >= 0 && idx < 6) {
                                ttSelectDay(TT_DAYS[idx], todayStr);
                            } else {
                                ttSelectDay(TT_DAYS[0], data.week_start);
                            }
                        } else {
                            ttSelectDay(TT_DAYS[0], data.week_start);
                        }
                    }
                })
                .catch(err => {
                    console.error('Error loading timetable:', err);
                    window.showToast?.('Gagal memuat jadwal pekanan. Silakan coba lagi.', 'error');
                    document.getElementById('tt-grid-container').innerHTML = `
                        <div class="timetable-feedback">
                            <svg class="h-10 w-10 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-3 font-semibold text-red-700">Tabel waktu belum dapat dimuat</p>
                            <p class="mt-1 text-sm text-gray-500">Periksa koneksi lalu coba muat ulang data pekan ini.</p>
                            <button type="button" id="tt-retry" class="schedule-secondary-button mt-4 rounded-lg px-3 py-2 text-sm font-semibold">Coba lagi</button>
                        </div>`;
                });
        }

        // ==================== DAY TABS ====================
        function ttRenderDayTabs(data) {
            const container = document.getElementById('tt-day-tabs');
            const ws = new Date(data.week_start + 'T00:00:00');
            let html = '';
            TT_DAYS.forEach((day, idx) => {
                const d = new Date(ws);
                d.setDate(d.getDate() + idx);
                const dayDate = ttDateStr(d);
                const dd = d.getDate();
                const mm = d.getMonth() + 1;
                html += `
                    <button type="button" data-day="${day}" data-date="${dayDate}" role="tab" aria-selected="false"
                        class="tt-day-tab">
                        <span class="tt-day-name">${day}</span>
                        <span class="tt-day-date">${String(dd).padStart(2,'0')}/${String(mm).padStart(2,'0')}</span>
                        <span class="tt-day-count" data-day-count="${dayDate}">0 jadwal</span>
                    </button>`;
            });
            container.innerHTML = html;

            ttUpdateDayCounts(data.schedules || []);

            container.querySelectorAll('.tt-day-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    ttSelectDay(this.dataset.day, this.dataset.date);
                });
            });
        }

        function ttUpdateDayCounts(schedules = ttWeekData?.schedules || []) {
            document.querySelectorAll('.tt-day-tab').forEach(btn => {
                const count = schedules.filter(schedule => schedule.date === btn.dataset.date
                    && (!ttSelectedLabId || String(schedule.lab_id) === String(ttSelectedLabId))).length;
                btn.querySelector('[data-day-count]').textContent = `${count} jadwal`;
            });
        }

        function ttSelectDay(day, date) {
            ttSelectedDay = day;
            ttSelectedDate = date;

            // Update tab styles
            document.querySelectorAll('.tt-day-tab').forEach(tab => {
                if (tab.dataset.day === day) {
                    tab.classList.add('is-active');
                    tab.setAttribute('aria-selected', 'true');
                } else {
                    tab.classList.remove('is-active');
                    tab.setAttribute('aria-selected', 'false');
                }
            });

            // Filter schedules for selected day
            const daySchedules = (ttWeekData.schedules || []).filter(s => s.date === date);
            const visibleDaySchedules = ttSelectedLabId
                ? daySchedules.filter(schedule => String(schedule.lab_id) === String(ttSelectedLabId))
                : daySchedules;

            ttUpdateSummary(visibleDaySchedules.length);
            ttRenderGrid(daySchedules);
        }

        // ==================== GRID RENDERING ====================
        function ttRenderGrid(schedules) {
            const container = document.getElementById('tt-grid-container');
            if (ttAllLabs.length === 0) {
                container.innerHTML = '<div class="timetable-feedback"><p class="font-semibold text-gray-700">Belum ada laboratorium yang tersedia</p><p class="mt-1 text-sm text-gray-500">Tambahkan data laboratorium untuk melihat tabel waktu.</p></div>';
                return;
            }

            const visibleLabs = ttSelectedLabId
                ? ttAllLabs.filter(lab => String(lab.id) === String(ttSelectedLabId))
                : ttAllLabs;
            if (visibleLabs.length === 0) {
                container.innerHTML = '<div class="timetable-feedback"><p class="font-semibold text-gray-700">Laboratorium tidak ditemukan</p><p class="mt-1 text-sm text-gray-500">Pilih laboratorium lain atau tampilkan semua laboratorium.</p></div>';
                return;
            }

            const visibleScheduleCount = schedules.filter(schedule => !ttSelectedLabId || String(schedule.lab_id) === String(ttSelectedLabId)).length;

            const totalHeight = TT_TOTAL_SLOTS * TT_ROW_HEIGHT;
            let html = '';

            // Header row
            html += `<div class="tt-grid-header">`;
            html += '<div class="tt-grid-corner">Waktu</div>';
            visibleLabs.forEach(lab => {
                html += `<div class="tt-grid-lab-header">${ttEscHtml(lab.name)}</div>`;
            });
            html += '</div>';

            // Grid body
            html += `<div class="tt-grid-body" style="height:${totalHeight}px;">`;

            // Time labels
            html += '<div class="tt-time-column">';
            for (let h = TT_TIME_START; h < TT_TIME_END; h++) {
                const topHour = ((h - TT_TIME_START) * 60 / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                html += `<div class="tt-time-label tt-time-label-hour" style="top:${topHour}px;">${String(h).padStart(2,'0')}:00</div>`;
                const topHalf = topHour + (30 / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                html += `<div class="tt-time-label tt-time-label-half" style="top:${topHalf}px;">${String(h).padStart(2,'0')}:30</div>`;
            }
            html += '</div>';

            // Lab columns
            visibleLabs.forEach(lab => {
                html += `<div class="tt-lab-column" data-lab-id="${lab.id}">`;

                // Gridlines
                for (let slot = 0; slot <= TT_TOTAL_SLOTS; slot++) {
                    const mins = slot * TT_SLOT_MINUTES;
                    const topPx = slot * TT_ROW_HEIGHT;
                    if (mins % 60 === 0) {
                        html += `<div class="tt-grid-line tt-grid-line-hour" style="top:${topPx}px;"></div>`;
                    } else if (mins % 30 === 0) {
                        html += `<div class="tt-grid-line tt-grid-line-half" style="top:${topPx}px;"></div>`;
                    } else {
                        html += `<div class="tt-grid-line tt-grid-line-slot" style="top:${topPx}px;"></div>`;
                    }
                }

                // Schedule blocks
                const labSchedules = schedules.filter(s => String(s.lab_id) === String(lab.id));
                labSchedules.forEach(s => {
                    const startMin = ttTimeToMinutes(s.start_time);
                    const endMin = ttTimeToMinutes(s.end_time);
                    const startOffset = startMin - (TT_TIME_START * 60);
                    const duration = endMin - startMin;
                    if (startOffset < 0 || duration <= 0) return;

                    const topPx = (startOffset / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                    const heightPx = (duration / TT_SLOT_MINUTES) * TT_ROW_HEIGHT;
                    const colors = TT_TYPE_COLORS[s.booking_type] || TT_TYPE_COLORS['perkuliahan_tetap'];
                    const startTimeStr = ttFormatTime(s.start_time);
                    const endTimeStr = ttFormatTime(s.end_time);
                    const isKuliah = s.booking_type === 'perkuliahan_tetap' || s.booking_type === 'perkuliahan_tidak_tetap';
                    const lecturerDisplay = (isKuliah && s.lecturer) ? s.lecturer : '';
                    const scheduleId = ttExtractId(s.id);

                    // Icons
                    const iconClock = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                    const iconUser = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>`;

                    // Tooltip (escaped for HTML attribute safety)
                    const tooltipParts = [s.course || '-'];
                    if (lecturerDisplay) tooltipParts.push(lecturerDisplay);
                    tooltipParts.push(startTimeStr + ' - ' + endTimeStr);
                    const safeTooltip = ttEscHtml(tooltipParts.join('\n'));
                    const scheduleLabel = ttEscHtml(`${s.course || 'Jadwal'} · ${startTimeStr}–${endTimeStr}`);
                    const occurrenceEditQuery = s.is_recurring && s.occurrence_date
                        ? `?scope=single&occurrence_date=${encodeURIComponent(s.occurrence_date)}&target_date=${encodeURIComponent(s.date)}`
                        : '';
                    const occurrenceEditUrl = `${TT_BASE_URL}/${scheduleId}/edit${occurrenceEditQuery}`;

                    html += `
                        <div class="tt-schedule-block ${colors.bg} ${colors.border} group"
                                style="top:${topPx}px; height:${heightPx}px; z-index:5;"
                                title="${safeTooltip}"
                                role="button"
                                tabindex="0"
                                aria-label="Edit ${scheduleLabel}"
                                onclick="ttOpenSchedule(${scheduleId}, '${s.date}')"
                                onkeydown="if(event.key === 'Enter' || event.key === ' '){event.preventDefault();ttOpenSchedule(${scheduleId}, '${s.date}')}">
                            <div class="tt-schedule-content">
                                <div class="flex min-w-0 items-center gap-1.5">
                                    <span class="tt-schedule-dot ${colors.accent}" aria-hidden="true"></span>
                                    <div class="truncate text-xs font-bold ${colors.text} leading-tight">${ttEscHtml(s.course || '-')}</div>
                                </div>

                                ${heightPx > 32 ? `
                                <div class="mt-1 flex items-center gap-1.5 text-[10px] font-medium ${colors.text} leading-none opacity-90">
                                    ${iconClock}
                                    <span class="truncate">${startTimeStr}–${endTimeStr}</span>
                                </div>` : ''}

                                ${heightPx > 52 && lecturerDisplay ? `
                                <div class="mt-1 flex items-center gap-1.5 text-[10px] ${colors.text} leading-none opacity-80">
                                    ${iconUser}
                                    <span class="truncate">${ttEscHtml(lecturerDisplay)}</span>
                                </div>` : ''}
                            </div>

                            <!-- Admin Action Overlay -->
                            <div class="tt-schedule-actions" aria-label="Aksi jadwal">
                                <a href="${occurrenceEditUrl}" onclick="event.stopPropagation()" class="schedule-primary-button rounded-md p-1.5 transition-colors" title="Edit detail jadwal" aria-label="Edit detail jadwal">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <a href="${TT_BASE_URL}/${scheduleId}/print" target="_blank" rel="noopener" onclick="event.stopPropagation()" class="schedule-secondary-button rounded-md p-1.5 transition-colors" title="Cetak jadwal" aria-label="Cetak jadwal">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 002 2v4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <button type="button" onclick="ttDeleteSchedule(${scheduleId}, this, event)" class="schedule-danger-action rounded-md p-1.5 transition-colors" title="Batalkan jadwal" aria-label="Batalkan jadwal">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>`;
                });

                html += '</div>';
            });

            if (visibleScheduleCount === 0) {
                html += '<div class="tt-grid-empty">Tidak ada jadwal pada hari ini</div>';
            }

            html += '</div>';

            container.innerHTML = html;
        }

        function ttOpenSchedule(scheduleId, occurrenceDate = null) {
            if (!scheduleId) return;

            const schedule = (ttWeekData?.schedules || []).find(item =>
                Number(item.schedule_id) === Number(scheduleId) && item.date === occurrenceDate
            );
            const url = new URL(`${TT_BASE_URL}/${scheduleId}/edit`, window.location.origin);

            if (schedule?.is_recurring && schedule.occurrence_date) {
                url.searchParams.set('scope', 'single');
                url.searchParams.set('occurrence_date', schedule.occurrence_date);
                url.searchParams.set('target_date', schedule.date);
            }

            window.location.href = url.toString();
        }

        // ==================== DELETE HANDLER ====================
        function openDeleteModal(scheduleId, courseName, isRecurring, day, defaultDate) {
            const modal = document.getElementById('delete-modal');
            const scopeAll = document.getElementById('delete-scope-all');
            const scopeSingle = document.getElementById('delete-scope-single');
            const scopeFuture = document.getElementById('delete-scope-future');
            const singleDate = document.getElementById('delete-single-date');
            const futureDate = document.getElementById('delete-future-date');

            document.getElementById('delete-course').textContent = 'Mata Kuliah: ' + (courseName || '-');
            document.getElementById('delete-form').action = `${TT_BASE_URL}/${scheduleId}`;

            // Reset state
            document.querySelector('input[name="delete_scope"][value="all"]').checked = true;
            document.getElementById('delete-reason').value = '';
            scopeSingle.classList.add('hidden');
            scopeFuture.classList.add('hidden');

            if (isRecurring) {
                scopeSingle.classList.remove('hidden');
                scopeSingle.classList.add('flex');
                scopeFuture.classList.remove('hidden');
                scopeFuture.classList.add('flex');

                const dayMap = {'Senin':1,'Selasa':2,'Rabu':3,'Kamis':4,'Jumat':5,'Sabtu':6};
                const target = dayMap[day] || 1;
                const now = new Date();
                const daysAhead = (target - now.getDay() + 7) % 7 || 7;
                const next = new Date(now.getFullYear(), now.getMonth(), now.getDate() + daysAhead);
                const pad = n => String(n).padStart(2, '0');
                const fallback = `${next.getFullYear()}-${pad(next.getMonth()+1)}-${pad(next.getDate())}`;
                const value = defaultDate || fallback;
                singleDate.value = value;
                futureDate.value = value;
            }

            modal.classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }

        function submitDeleteModal() {
            const scope = document.querySelector('input[name="delete_scope"]:checked').value;
            const form = document.getElementById('delete-form');
            document.getElementById('delete-form-scope').value = scope;

            let date = null;
            if (scope === 'single') date = document.getElementById('delete-single-date').value;
            if (scope === 'future') date = document.getElementById('delete-future-date').value;

            if ((scope === 'single' || scope === 'future') && !date) {
                window.showToast?.('Silakan pilih tanggal kemunculan terlebih dahulu.', 'error');
                return;
            }

            const reason = document.getElementById('delete-reason').value.trim();
            if (!reason) {
                window.showToast?.('Alasan pembatalan wajib diisi.', 'error');
                return;
            }

            document.getElementById('delete-form-date').value = date || '';
            document.getElementById('delete-form-reason').value = reason;
            form.submit();
        }

        function ttDeleteSchedule(scheduleId, btnEl, event) {
            event.stopPropagation();
            if (ttDeleteInProgress) return;

            // Get course name from the block's title content (safe, no injection)
            const block = btnEl.closest('.group');
            const courseName = block ? block.querySelector('.text-xs.font-bold')?.textContent || '-' : '-';

            openDeleteModal(scheduleId, courseName, true, ttSelectedDay, ttSelectedDate);
        }

        // ==================== WEEK NAVIGATION ====================
        document.getElementById('tt-prev-week').addEventListener('click', () => {
            if (ttWeekData && ttWeekData.week_start) {
                const ws = new Date(ttWeekData.week_start + 'T00:00:00');
                ws.setDate(ws.getDate() - 7);
                ttLoadWeek(ttDateStr(ws));
            }
        });

        document.getElementById('tt-next-week').addEventListener('click', () => {
            if (ttWeekData && ttWeekData.week_start) {
                const ws = new Date(ttWeekData.week_start + 'T00:00:00');
                ws.setDate(ws.getDate() + 7);
                ttLoadWeek(ttDateStr(ws));
            }
        });

        document.getElementById('tt-today-btn').addEventListener('click', () => {
            ttLoadWeek(ttDateStr(new Date()));
        });

        document.getElementById('tt-date-picker').addEventListener('change', function() {
            if (this.value) {
                ttLoadWeek(this.value);
            }
        });

        document.getElementById('tt-lab-filter').addEventListener('change', function() {
            ttSelectedLabId = this.value || '';
            ttUpdateDayCounts();
            if (!ttWeekData || !ttSelectedDate) return;

            const selectedDaySchedules = (ttWeekData.schedules || []).filter(schedule => schedule.date === ttSelectedDate);
            const visibleDaySchedules = ttSelectedLabId
                ? selectedDaySchedules.filter(schedule => String(schedule.lab_id) === String(ttSelectedLabId))
                : selectedDaySchedules;
            ttUpdateSummary(visibleDaySchedules.length);
            ttRenderGrid(selectedDaySchedules);
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('#tt-retry')) return;
            ttLoadWeek(ttSelectedDate || undefined);
        });

        // ==================== DELETE MODAL WIRING ====================
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('delete-confirm').addEventListener('click', submitDeleteModal);
            document.getElementById('delete-cancel').addEventListener('click', closeDeleteModal);
            document.getElementById('delete-modal').addEventListener('click', function(e) {
                if (e.target === this) closeDeleteModal();
            });
        });
    </script>
@endsection
