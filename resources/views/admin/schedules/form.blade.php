@extends('layouts.admin')

@section('title', ($isEdit ? 'Edit' : 'Tambah').' Jadwal - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')

    <div class="admin-schedule-page mx-auto max-w-5xl px-0">
        @include('admin.schedules.partials.navigation', [
            'current' => $isEdit ? 'Edit Jadwal' : 'Tambah Jadwal',
            'backUrl' => route('admin.schedules.index'),
            'backLabel' => 'Kembali ke Manajemen Jadwal',
            'includeSchedule' => true,
        ])

        <!-- Page heading -->
        <div class="mb-6 flex flex-col gap-3 md:mb-8 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="mb-1 text-xs font-bold uppercase tracking-[0.14em] text-yellow-700">{{ $isEdit ? 'Perubahan jadwal' : 'Jadwal baru' }}</p>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 md:text-3xl">{{ $isEdit ? 'Edit' : 'Tambah' }} Jadwal</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">{{ $isEdit ? 'Halaman ini untuk mengubah detail jadwal. Untuk memindahkan satu pertemuan dengan cepat, gunakan kalender.' : 'Tentukan waktu, lokasi, dan informasi kegiatan untuk membuat jadwal laboratorium.' }}</p>
            </div>
            @if($isEdit)
                <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">Jadwal #{{ $schedule->id }}</span>
            @endif
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                <p class="font-bold">Periksa kembali data berikut:</p>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Booking Info (if editing booking schedule) -->
        @if($isEdit && $schedule->booking)
            <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 text-blue-900">
                <p class="font-bold">Berasal dari pengajuan peminjaman</p>
                <p class="mt-1 text-sm leading-5">Pengajuan #{{ $schedule->booking_id }} tetap disimpan sebagai data asli. Perubahan pada halaman ini dicatat sebagai revisi jadwal.</p>
            </div>
        @endif

        @if($isEdit)
            <section class="mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-4 md:p-5" aria-labelledby="schedule-summary-heading">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 id="schedule-summary-heading" class="text-base font-bold text-gray-900">Ringkasan jadwal saat ini</h2>
                        <p class="mt-1 text-sm text-gray-600">Gunakan ringkasan ini untuk memastikan perubahan diterapkan pada jadwal yang benar.</p>
                    </div>
                    <span class="w-fit rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-500">Data tersimpan</span>
                </div>
                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Laboratorium</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $schedule->lab?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Hari &amp; waktu</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $schedule->day }} · {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Periode</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $schedule->start_date?->format('d M Y') ?? 'Tanpa batas awal' }} – {{ $schedule->end_date?->format('d M Y') ?? 'Tanpa batas akhir' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kegiatan</dt>
                        <dd class="mt-1 font-semibold text-gray-900">{{ $schedule->course ?: '-' }}</dd>
                    </div>
                </dl>
            </section>
        @endif

        @php
            $isRecurringEdit = $isEdit && app(\App\Services\ScheduleCalendarService::class)->isRecurringSchedule($schedule);
            $currentScheduleFrequency = old(
                'schedule_frequency',
                $isEdit && $schedule->type === 'perkuliahan_tidak_tetap' && $isRecurringEdit ? 'multiple' : 'once'
            );
            $selectedRecurrenceDays = (array) old('recurrence_days', $schedule->recurrence_days ?? []);
            if ($isEdit && $schedule->type === 'perkuliahan_tidak_tetap' && $currentScheduleFrequency === 'multiple' && $selectedRecurrenceDays === []) {
                $selectedRecurrenceDays = [$schedule->day];
            }
        @endphp
        @if($isRecurringEdit)
            <!-- Change scope for recurring series -->
            <section class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50/50 p-4 md:p-5" aria-labelledby="change-scope-heading">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 id="change-scope-heading" class="text-base font-bold text-gray-900">Atur lingkup perubahan</h2>
                        <p class="mt-1 text-sm leading-5 text-gray-600">Pilih bagian rangkaian jadwal yang ingin diubah. Pertemuan yang sudah lewat tetap tersimpan sebagai histori.</p>
                    </div>
                    <span class="w-fit rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-yellow-800">Histori aman</span>
                </div>
                <fieldset class="mt-4 space-y-3">
                    <legend class="sr-only">Lingkup perubahan</legend>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 transition-colors hover:border-yellow-300 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                        <input type="radio" name="scope" value="all" {{ old('scope', request('scope', 'all')) === 'all' ? 'checked' : '' }} form="scheduleForm"
                               class="mt-1 h-4 w-4 accent-yellow-600 focus:ring-yellow-500">
                        <span class="text-sm text-gray-700"><span class="font-semibold text-gray-900">Semua pertemuan yang akan datang</span><span class="mt-0.5 block text-xs text-gray-500">Perubahan berlaku untuk jadwal setelah pertemuan ini.</span></span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 transition-colors hover:border-yellow-300 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                        <input type="radio" name="scope" value="single" {{ old('scope', request('scope')) === 'single' ? 'checked' : '' }} form="scheduleForm"
                               class="mt-1 h-4 w-4 accent-yellow-600 focus:ring-yellow-500">
                        <span class="text-sm text-gray-700"><span class="font-semibold text-gray-900">Pertemuan ini saja</span><span class="mt-0.5 block text-xs text-gray-500">Ubah lab atau waktu hanya pada satu tanggal.</span></span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 transition-colors hover:border-yellow-300 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                        <input type="radio" name="scope" value="future" {{ old('scope', request('scope')) === 'future' ? 'checked' : '' }} form="scheduleForm"
                               class="mt-1 h-4 w-4 accent-yellow-600 focus:ring-yellow-500">
                        <span class="text-sm text-gray-700"><span class="font-semibold text-gray-900">Pertemuan ini dan berikutnya</span><span class="mt-0.5 block text-xs text-gray-500">Perbarui rangkaian mulai tanggal ini sampai jadwal berakhir.</span></span>
                    </label>

                    <div id="occurrence-date-field" class="hidden pl-7">
                        <label for="occurrence_date-trigger" class="mb-1 block text-sm font-medium text-gray-600">Tanggal pertemuan yang diubah <span class="text-red-500">*</span></label>
                        <div class="schedule-date-picker w-full md:w-80" data-schedule-date-picker data-date-placeholder="Pilih tanggal pertemuan" data-date-label="Tanggal pertemuan yang diubah">
                            <input type="hidden" name="occurrence_date" id="occurrence_date" form="scheduleForm"
                                   value="{{ old('occurrence_date', request('occurrence_date', '')) }}">
                            <button type="button" id="occurrence_date-trigger" class="schedule-date-trigger" data-date-trigger aria-haspopup="dialog" aria-expanded="false" aria-controls="occurrence_date-calendar">
                                <span data-date-value>Belum dipilih</span>
                                <svg class="schedule-date-trigger-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14A1.5 1.5 0 0120.5 6v13A1.5 1.5 0 0119 20.5H5A1.5 1.5 0 013.5 19V6A1.5 1.5 0 015 4.5z"/></svg>
                            </button>
                            <div id="occurrence_date-calendar" class="schedule-date-popover hidden" data-date-popover role="dialog" aria-label="Pilih tanggal pertemuan">
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
                            <p class="schedule-date-error hidden" data-date-error role="alert"></p>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Pilih tanggal pertemuan yang menjadi titik mulai perubahan.</p>
                        <input type="hidden" name="target_date" value="{{ old('target_date', request('target_date', '')) }}" form="scheduleForm">
                    </div>
                </fieldset>
                <div class="mt-4">
                    <label for="change_reason" class="block text-sm font-semibold text-gray-700 mb-1">Alasan perubahan *</label>
                    <textarea id="change_reason" name="change_reason" form="scheduleForm" required rows="2" maxlength="1000"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                              placeholder="Contoh: perubahan ruang dari program studi">{{ old('change_reason') }}</textarea>
                </div>
            </section>
        @elseif($isEdit)
            <section class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50/50 p-4 md:p-5" aria-labelledby="change-reason-heading">
                <h2 id="change-reason-heading" class="text-base font-bold text-gray-900">Catatan perubahan</h2>
                <p class="mt-1 mb-4 text-sm text-gray-600">Tuliskan alasan perubahan agar riwayat jadwal tetap mudah ditelusuri.</p>
                <label for="change_reason" class="block text-sm font-semibold text-gray-700 mb-1">Alasan perubahan *</label>
                <textarea id="change_reason" name="change_reason" form="scheduleForm" required rows="2" maxlength="1000"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                          placeholder="Contoh: perubahan ruang dari program studi">{{ old('change_reason') }}</textarea>
            </section>
        @endif

        @if($isEdit && isset($changeLogs) && $changeLogs->isNotEmpty())
            <details class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-5">
                <summary class="cursor-pointer font-semibold text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">Riwayat perubahan ({{ $changeLogs->count() }})</summary>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500 border-b">
                            <tr><th class="py-2 pr-3">Waktu</th><th class="py-2 pr-3">Aksi</th><th class="py-2 pr-3">Tanggal efektif</th><th class="py-2 pr-3">Admin</th><th class="py-2">Alasan</th></tr>
                        </thead>
                        <tbody>
                            @foreach($changeLogs as $log)
                                <tr class="border-b border-gray-100 align-top">
                                    <td class="py-2 pr-3 whitespace-nowrap">{{ $log->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 pr-3">{{ str_replace('_', ' ', ucfirst($log->action)) }}</td>
                                    <td class="py-2 pr-3 whitespace-nowrap">{{ $log->effective_date?->format('d/m/Y') ?: '-' }}</td>
                                    <td class="py-2 pr-3">{{ $log->changedBy?->name ?: 'Sistem' }}</td>
                                    <td class="py-2">{{ $log->reason ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif

        <!-- Form -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6 lg:p-8">
            <form id="scheduleForm" action="{{ $isEdit ? route('admin.schedules.update', $schedule->id) : route('admin.schedules.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <section aria-labelledby="schedule-timing-heading">
                    <div class="mb-5 border-b border-gray-100 pb-4">
                        <h2 id="schedule-timing-heading" class="text-lg font-bold text-gray-900">Jadwal dan lokasi</h2>
                        <p class="mt-1 text-sm leading-5 text-gray-600">Pilih tipe jadwal, kemudian tentukan hari, waktu, periode, laboratorium, dan kapasitasnya.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-6">
                    <!-- Type drives the conditional detail fields below. -->
                    <div>
                        <label for="typeSelect" class="block text-sm font-semibold text-gray-700 mb-2">Tipe Jadwal *</label>
                        <select name="type" id="typeSelect" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $schedule->type ?? request('type', 'perkuliahan_tetap')) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Jenis jadwal menentukan rincian kegiatan yang perlu dilengkapi.</p>
                    </div>

                    <!-- Day -->
                    <div>
                        <label for="daySelect" class="block text-sm font-semibold text-gray-700 mb-2">Hari *</label>
                        <select name="day" id="daySelect" required class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-base">
                            <option value="">Pilih Hari</option>
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ old('day', $schedule->day ?? request('day', '')) == $day ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Time -->
                    <div>
                        <label for="start_hour" class="block text-sm font-semibold text-gray-700 mb-2">Jam Mulai *</label>
                        <input type="hidden" name="start_time" id="startTime" required
                               value="{{ old('start_time', request('start_time', $schedule ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '')) }}">
                        <div class="flex gap-2">
                            <select id="start_hour" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                <option value="" disabled selected>Jam</option>
                                @foreach(range(0, 23) as $h)
                                    <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                @endforeach
                            </select>
                            <span class="self-center font-bold text-gray-400">:</span>
                            <select id="start_minute" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                <option value="" disabled selected>Menit</option>
                                @foreach(range(0, 55, 5) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- End Time -->
                    <div>
                        <label for="end_hour" class="block text-sm font-semibold text-gray-700 mb-2">Jam Selesai *</label>
                        <input type="hidden" name="end_time" id="endTime" required
                               value="{{ old('end_time', request('end_time', $schedule ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '')) }}">
                        <div class="flex gap-2">
                            <select id="end_hour" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                <option value="" disabled selected>Jam</option>
                                @foreach(range(0, 23) as $h)
                                    <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                @endforeach
                            </select>
                            <span class="self-center font-bold text-gray-400">:</span>
                            <select id="end_minute" class="w-1/2 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                                <option value="" disabled selected>Menit</option>
                                @foreach(range(0, 55, 5) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <p id="time-error" class="text-xs text-red-500 mt-1 hidden"><strong>* Jam Selesai harus setelah Jam Mulai</strong></p>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="startDate-trigger" class="mb-2 block text-sm font-semibold text-gray-700">Tanggal Mulai <span id="start-date-required" class="hidden text-red-500">*</span></label>
                        <div class="schedule-date-picker" data-schedule-date-picker data-date-placeholder="Pilih tanggal mulai" data-date-label="Tanggal mulai">
                            <input type="hidden" name="start_date" id="startDate"
                                   value="{{ old('start_date', $schedule && $schedule->start_date ? $schedule->start_date->format('Y-m-d') : request('start_date', '')) }}">
                            <button type="button" id="startDate-trigger" class="schedule-date-trigger" data-date-trigger aria-haspopup="dialog" aria-expanded="false" aria-controls="startDate-calendar">
                                <span data-date-value>Belum dipilih</span>
                                <svg class="schedule-date-trigger-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14A1.5 1.5 0 0120.5 6v13A1.5 1.5 0 0119 20.5H5A1.5 1.5 0 013.5 19V6A1.5 1.5 0 015 4.5z"/></svg>
                            </button>
                            <div id="startDate-calendar" class="schedule-date-popover hidden" data-date-popover role="dialog" aria-label="Pilih tanggal mulai">
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
                            <p class="schedule-date-error hidden" data-date-error role="alert"></p>
                        </div>
                        <p id="start-date-help" class="mt-1 text-xs text-gray-500">Kosongkan jika jadwal berlaku tanpa tanggal mulai.</p>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="endDate-trigger" class="mb-2 block text-sm font-semibold text-gray-700">Tanggal Selesai <span id="end-date-required" class="text-red-500">*</span></label>
                        <div class="schedule-date-picker" data-schedule-date-picker data-date-placeholder="Pilih tanggal selesai" data-date-label="Tanggal selesai" data-min-input="startDate">
                            <input type="hidden" name="end_date" id="endDate"
                                   value="{{ old('end_date', $schedule && $schedule->end_date ? $schedule->end_date->format('Y-m-d') : request('end_date', '')) }}">
                            <button type="button" id="endDate-trigger" class="schedule-date-trigger" data-date-trigger aria-haspopup="dialog" aria-expanded="false" aria-controls="endDate-calendar">
                                <span data-date-value>Belum dipilih</span>
                                <svg class="schedule-date-trigger-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9.5h17M5 4.5h14A1.5 1.5 0 0120.5 6v13A1.5 1.5 0 0119 20.5H5A1.5 1.5 0 013.5 19V6A1.5 1.5 0 015 4.5z"/></svg>
                            </button>
                            <div id="endDate-calendar" class="schedule-date-popover hidden" data-date-popover role="dialog" aria-label="Pilih tanggal selesai">
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
                            <p class="schedule-date-error hidden" data-date-error role="alert"></p>
                        </div>
                        <p id="end-date-help" class="text-xs text-gray-500 mt-1">Wajib untuk perkuliahan tetap; maksimal 60 pertemuan.</p>
                    </div>

                    <!-- Lab (LAST - After time is selected, fetched via AJAX) -->
                    <div>
                        <label for="labSelect" class="block text-sm font-semibold text-gray-700 mb-2">Laboratorium *</label>
                        <select name="lab_id" id="labSelect" required class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-base" {{ $isEdit ? '' : 'disabled' }}>
                            @if($isEdit)
                                @foreach($labs as $lab)
                                    <option value="{{ $lab->id }}" data-capacity="{{ $lab->capacity }}" {{ old('lab_id', request('lab_id', $schedule->lab_id ?? '')) == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->name }} (Kap. {{ $lab->capacity }})
                                    </option>
                                @endforeach
                            @else
                                <option value="">Pilih waktu terlebih dahulu</option>
                            @endif
                        </select>
                    </div>

                    <!-- Student Count -->
                    <div>
                        <label for="student_count" class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Mahasiswa *</label>
                        <input type="number" name="student_count" id="student_count" min="1" required
                               value="{{ old('student_count', $schedule->student_count ?? '') }}"
                               placeholder="Wajib diisi"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    </div>
                    </div>

                <!-- Recurrence pattern -->
                <div id="nonfixed-schedule-pattern" class="mt-6 hidden rounded-xl border border-yellow-200 bg-yellow-50/50 p-4">
                    <fieldset>
                        <legend class="text-sm font-semibold text-gray-800">Pola pengulangan</legend>
                        <p class="mt-1 text-xs text-gray-600">Tentukan apakah jadwal hanya berlangsung sekali atau berulang setiap minggu.</p>

                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                                <input type="radio" name="schedule_frequency" value="once"
                                       class="mt-0.5 h-4 w-4 accent-yellow-600 focus:ring-yellow-500"
                                       {{ $currentScheduleFrequency === 'once' ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Sekali</span>
                                    <span class="mt-0.5 block text-xs text-gray-500">Gunakan satu tanggal dan waktu.</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50">
                                <input type="radio" name="schedule_frequency" value="multiple"
                                       class="mt-0.5 h-4 w-4 accent-yellow-600 focus:ring-yellow-500"
                                       {{ $currentScheduleFrequency === 'multiple' ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Berulang setiap minggu</span>
                                    <span class="mt-0.5 block text-xs text-gray-500">Pilih hari dan tanggal akhir rangkaian.</span>
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <div id="recurrence-days-panel" class="mt-4 hidden border-t border-yellow-200 pt-4">
                        <fieldset>
                            <legend class="text-sm font-semibold text-gray-800">Hari pertemuan</legend>
                            <p class="mt-1 text-xs text-gray-600">Tanggal mulai harus jatuh pada salah satu hari yang dipilih.</p>
                            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-6">
                                @foreach($days as $day)
                                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-2 py-2.5 text-sm has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-100">
                                        <input type="checkbox" name="recurrence_days[]" value="{{ $day }}"
                                               class="recurrence-day h-4 w-4 rounded accent-yellow-600 focus:ring-yellow-500"
                                               {{ in_array($day, $selectedRecurrenceDays, true) ? 'checked' : '' }}>
                                        <span class="font-medium text-gray-700">{{ $day }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <p id="recurrence-validation-error" class="mt-3 hidden rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert"></p>
                    </div>
                </div>
                </section>

                <!-- Conditional activity details -->
                <section class="mt-8 border-t border-gray-100 pt-6" aria-labelledby="activity-details-heading">
                    <div class="mb-5">
                        <h2 id="activity-details-heading" class="text-lg font-bold text-gray-900">Rincian kegiatan</h2>
                        <p class="mt-1 text-sm leading-5 text-gray-600">Lengkapi informasi yang sesuai dengan tipe jadwal yang dipilih.</p>
                    </div>

                    <!-- Perkuliahan Fields -->
                    <div id="perkuliahan-fields" class="hidden">
                    <h3 class="mb-4 text-base font-bold text-gray-800">Informasi perkuliahan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="course_name" class="block text-sm font-semibold text-gray-700 mb-2">Mata Kuliah *</label>
                            <input type="text" name="course_name" id="course_name"
                                   value="{{ old('course_name', $schedule->course ?? '') }}"
                                   placeholder="Contoh: Sistem Informasi Manajemen"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="lecturer_name" class="block text-sm font-semibold text-gray-700 mb-2">Dosen Pengampu *</label>
                            <input type="text" name="lecturer_name" id="lecturer_name"
                                   value="{{ old('lecturer_name', $schedule->lecturer ?? '') }}"
                                   placeholder="Contoh: Dr. Budi Santoso"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="komting" class="block text-sm font-semibold text-gray-700 mb-2">Koordinator / Komting</label>
                            <input type="text" name="komting" id="komting"
                                   value="{{ old('komting', $schedule->komting ?? '') }}"
                                   placeholder="Contoh: Ahmad Faizal"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="komting_phone" class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon Komting</label>
                            <input type="text" name="komting_phone" id="komting_phone"
                                   value="{{ old('komting_phone', $schedule->komting_phone ?? '') }}"
                                   placeholder="Contoh: 08123456789"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                    <!-- Non-Perkuliahan Fields -->
                    <div id="non-perkuliahan-fields" class="hidden">
                    <h3 class="mb-4 text-base font-bold text-gray-800">Informasi kegiatan non-perkuliahan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="activity_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Kegiatan *</label>
                            <input type="text" name="activity_name" id="activity_name"
                                   value="{{ old('activity_name', $isEdit ? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_name : ($schedule->type === 'non_perkuliahan' ? $schedule->course : '')) : '') }}"
                                   placeholder="Contoh: Workshop Data Analytics"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="activity_type" class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kegiatan *</label>
                            <select name="activity_type" id="activity_type"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                @php
                                    $currentActivityType = old('activity_type', $isEdit ? ($schedule->activity_type ?? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->activity_type : '')) : '');
                                @endphp
                                <option value="">-- Pilih Jenis Kegiatan --</option>
                                <option value="Seminar" {{ $currentActivityType == 'Seminar' ? 'selected' : '' }}>Seminar</option>
                                <option value="Workshop" {{ $currentActivityType == 'Workshop' ? 'selected' : '' }}>Workshop</option>
                                <option value="Pelatihan" {{ $currentActivityType == 'Pelatihan' ? 'selected' : '' }}>Pelatihan</option>
                                <option value="Rapat" {{ $currentActivityType == 'Rapat' ? 'selected' : '' }}>Rapat</option>
                                <option value="Ujian" {{ $currentActivityType == 'Ujian' ? 'selected' : '' }}>Ujian</option>
                                <option value="Lainnya" {{ $currentActivityType == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label for="position" class="block text-sm font-semibold text-gray-700 mb-2">Posisi Peminjam</label>
                            <input type="text" name="position" id="position"
                                   value="{{ old('position', $isEdit ? ($schedule->position ?? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->position : '')) : '') }}"
                                   placeholder="Contoh: Ketua Panitia"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="pic_name_non_perkuliahan" class="block text-sm font-semibold text-gray-700 mb-2">Nama Peminjam / PIC *</label>
                            <input type="text" name="pic_name_non_perkuliahan" id="pic_name_non_perkuliahan"
                                   value="{{ old('pic_name_non_perkuliahan', $schedule->lecturer ?? '') }}"
                                   placeholder="Contoh: Ahmad Rafi"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                        </div>
                        <div class="md:col-span-2">
                            <label for="equipment_needs" class="block text-sm font-semibold text-gray-700 mb-2">Kebutuhan Peralatan</label>
                            <textarea name="equipment_needs" id="equipment_needs" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">{{ old('equipment_needs', $isEdit ? ($schedule->equipment_needs ?? (($schedule->booking && $schedule->booking->booking_type === 'non_perkuliahan') ? $schedule->booking->equipment_needs : '')) : '') }}</textarea>
                        </div>
                    </div>
                    </div>
                </section>

                <!-- Document Fields (Collapsible) -->
                <div class="mt-6 border border-gray-200 rounded-lg overflow-hidden">
                    <button type="button" id="doc-toggle" onclick="toggleDocSection()" aria-expanded="false" aria-controls="doc-section" class="flex w-full items-center justify-between bg-gray-50 px-4 py-3 text-left transition-colors hover:bg-yellow-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-inset">
                        <div>
                            <span class="font-semibold text-gray-700">Data dokumen <span class="font-normal text-gray-500">(opsional)</span></span>
                            <span class="mt-0.5 block text-sm text-gray-500">Lengkapi hanya jika dokumen peminjaman perlu dibuat.</span>
                        </div>
                        <svg id="doc-chevron" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="doc-section" role="region" aria-labelledby="doc-toggle" class="hidden space-y-4 px-4 py-4">
                        @php
                            $doc = ($isEdit && $schedule->document) ? $schedule->document : null;
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Study Program (all types) -->
                            <div id="doc-study-program">
                                <label for="study_program" class="block text-sm font-semibold text-gray-700 mb-2">Strata/Jurusan</label>
                                <select name="study_program" id="study_program" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                    <option value="">Pilih Program Studi</option>
                                    @foreach(['S1- Ekonomi', 'S1- Manajemen', 'S1- Akuntansi', 'S1- Ekonomi Islam', 'S1- Bisnis Digital', 'S2- Ekonomi', 'S2- Manajemen', 'S2- Akuntansi', 'Sekolah Vokasi', 'S3- PDIE Ilmu Ekonomi', 'S3- PDIE Akuntansi', 'S3- PDIE Manajemen', 'Lainnya'] as $program)
                                        <option value="{{ $program }}" {{ old('study_program', $doc->study_program ?? '') == $program ? 'selected' : '' }}>{{ $program }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Phone Number: only for non_perkuliahan (perkuliahan uses komting_phone) --}}
                            <div id="doc-phone-number" class="hidden">
                                <label for="doc_phone_number" class="block text-sm font-semibold text-gray-700 mb-2">No. Telp. Koordinator</label>
                                <input type="text" name="doc_phone_number" id="doc_phone_number"
                                       value="{{ old('doc_phone_number', $doc->phone_number ?? '') }}"
                                       placeholder="08xxxxxxxxxx"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- Lecturer NIP (perkuliahan only) -->
                            <div id="doc-lecturer-nip">
                                <label for="lecturer_nip" class="block text-sm font-semibold text-gray-700 mb-2">NIP Dosen Pengampu</label>
                                <input type="text" name="lecturer_nip" id="lecturer_nip"
                                       value="{{ old('lecturer_nip', $doc->lecturer_nip ?? '') }}"
                                       placeholder="NIP Dosen"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- NIM (non-perkuliahan) -->
                            <div id="doc-nim">
                                <label for="nim" class="block text-sm font-semibold text-gray-700 mb-2">NIM</label>
                                <input type="text" name="nim" id="nim"
                                       value="{{ old('nim', $doc->nim ?? '') }}"
                                       placeholder="NIM Koordinator"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>

                            <!-- NIP (non-perkuliahan, alternative) -->
                            <div id="doc-nip">
                                <label for="nip" class="block text-sm font-semibold text-gray-700 mb-2">NIP (jika dosen/pegawai)</label>
                                <input type="text" name="nip" id="nip"
                                       value="{{ old('nip', $doc->nip ?? '') }}"
                                       placeholder="NIP jika bukan mahasiswa"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Software Needs -->
                        <div>
                            <label for="software_needs" class="block text-sm font-semibold text-gray-700 mb-2">Software yang Digunakan</label>
                            <textarea name="software_needs" id="software_needs" rows="2"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                placeholder="Contoh: Microsoft Excel, SPSS, EViews">{{ old('software_needs', $doc->software_needs ?? '') }}</textarea>
                        </div>

                        <!-- KTM Upload -->
                        <div>
                            <label for="ktm_file" class="block text-sm font-semibold text-gray-700 mb-2">Upload KTM (Opsional)</label>
                            <div class="flex items-center gap-3">
                                <input type="file" name="ktm_file" id="ktm_file" accept=".jpg,.jpeg,.png,.pdf"
                                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                                @if($doc && $doc->ktm_path)
                                    <a href="{{ route('admin.secure-file', ['path' => $doc->ktm_path]) }}" target="_blank" 
                                       class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium whitespace-nowrap">
                                        Lihat KTM
                                    </a>
                                    <button type="button" onclick="confirmDeleteKtm()" class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, PDF. Maks 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 md:mt-8 flex flex-col sm:flex-row gap-3 md:gap-4">
                    <button type="submit" class="schedule-primary-button flex-1 rounded-lg py-3.5 text-base font-bold shadow-sm transition-all hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2 md:py-3">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="schedule-secondary-button rounded-lg px-6 py-3.5 text-center text-base font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2 md:py-3">
                        Batal
                    </a>    
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Hidden Form for Deleting KTM -->
    @if($isEdit && $schedule->document && $schedule->document->ktm_path)
        <form id="delete-ktm-form" action="{{ route('admin.schedules.delete-ktm', $schedule->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <script>
        // Time Dropdown Logic
        function setupTimeDropdowns() {
            const timeInputs = [
                { id: 'start', inputId: 'startTime' },
                { id: 'end', inputId: 'endTime' }
            ];
            
            timeInputs.forEach(config => {
                const hourSelect = document.getElementById(config.id + '_hour');
                const minuteSelect = document.getElementById(config.id + '_minute');
                const hiddenInput = document.getElementById(config.inputId);
                
                if (!hourSelect || !minuteSelect || !hiddenInput) return;

                function updateHiddenInput() {
                    if (hourSelect.value && minuteSelect.value) {
                        hiddenInput.value = `${hourSelect.value}:${minuteSelect.value}`;
                        hiddenInput.dispatchEvent(new Event('change'));
                    } else {
                        hiddenInput.value = '';
                        hiddenInput.dispatchEvent(new Event('change'));
                    }
                }
                
                // Initialize
                if (hiddenInput.value) {
                    const [h, m] = hiddenInput.value.split(':');
                    if (h) hourSelect.value = h;
                    if (m) minuteSelect.value = m;
                }
                
                hourSelect.addEventListener('change', updateHiddenInput);
                minuteSelect.addEventListener('change', updateHiddenInput);
            });
        }
        
        document.addEventListener('DOMContentLoaded', setupTimeDropdowns);

        // Time Validation - Ensure end time > start time
        function validateTimeSelection() {
            const startTimeEl = document.getElementById('startTime');
            const endTimeEl = document.getElementById('endTime');
            const timeError = document.getElementById('time-error');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            if (!startTimeEl || !endTimeEl || !timeError) return true;
            
            const startTime = startTimeEl.value;
            const endTime = endTimeEl.value;
            
            // Skip validation if either time is not set
            if (!startTime || !endTime) {
                timeError.classList.add('hidden');
                return true;
            }
            
            // Compare times (format HH:MM)
            const [startH, startM] = startTime.split(':').map(Number);
            const [endH, endM] = endTime.split(':').map(Number);
            const startMinutes = startH * 60 + startM;
            const endMinutes = endH * 60 + endM;
            
            if (endMinutes <= startMinutes) {
                // Invalid - end time is not after start time
                timeError.classList.remove('hidden');
                if (submitBtn) submitBtn.disabled = true;
                return false;
            } else {
                // Valid
                timeError.classList.add('hidden');
                if (submitBtn) submitBtn.disabled = false;
                return true;
            }
        }
        
        // Add event listener for time changes
        document.addEventListener('DOMContentLoaded', function() {
            const startTimeEl = document.getElementById('startTime');
            const endTimeEl = document.getElementById('endTime');
            
            if (startTimeEl) {
                startTimeEl.addEventListener('change', validateTimeSelection);
            }
            if (endTimeEl) {
                endTimeEl.addEventListener('change', validateTimeSelection);
            }
            
            // Also validate on form submit
            const form = document.getElementById('scheduleForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateTimeSelection()) {
                        e.preventDefault();
                        document.getElementById('time-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        });

        // Time-First Flow Variables
        const daySelectEl = document.getElementById('daySelect');
        const startTimeEl = document.getElementById('startTime');
        const endTimeEl = document.getElementById('endTime');
        const startDateEl = document.getElementById('startDate');
        const endDateEl = document.getElementById('endDate');
        const labSelectEl = document.getElementById('labSelect');
        const isEditMode = {{ $isEdit ? 'true' : 'false' }};
        const excludeScheduleId = {{ $isEdit ? $schedule->id : 'null' }};
        const requestedLabId = Number(new URLSearchParams(window.location.search).get('lab_id')) || null;
        const currentLabId = requestedLabId || {{ $isEdit ? ($schedule->lab_id ?? 'null') : 'null' }};

        // Fetch available labs via AJAX
        function fetchAvailableLabs() {
            const day = daySelectEl.value;
            const startTime = startTimeEl.value;
            const endTime = endTimeEl.value;
            const startDate = startDateEl.value;
            const endDate = endDateEl.value;

            // Require day, start_time, and end_time
            if (!day || !startTime || !endTime) {
                if (!isEditMode) {
                    labSelectEl.disabled = true;
                    labSelectEl.innerHTML = '<option value="">Pilih waktu terlebih dahulu</option>';
                }
                return;
            }

            // Show loading state
            labSelectEl.innerHTML = '<option value="">Memuat lab tersedia...</option>';
            labSelectEl.disabled = true;

            // Make AJAX request
            fetch('{{ route("admin.schedules.available-labs") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    day: day,
                    start_time: startTime,
                    end_time: endTime,
                    start_date: startDate || null,
                    end_date: endDate || null,
                    recurrence_days: getSelectedRecurrenceDays(),
                    exclude_schedule_id: excludeScheduleId
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(labs => {
                labSelectEl.innerHTML = '';
                
                if (labs.length === 0) {
                    labSelectEl.innerHTML = '<option value="">Tidak ada lab tersedia pada waktu ini</option>';
                    labSelectEl.disabled = true;
                } else {
                    labSelectEl.innerHTML = '<option value="">-- Pilih Laboratorium --</option>';
                    labs.forEach(lab => {
                        const option = document.createElement('option');
                        option.value = lab.id;
                        option.dataset.capacity = lab.capacity;
                        option.textContent = `${lab.name} (Kap. ${lab.capacity})`;
                        
                        // Pre-select current lab in edit mode
                        if ((isEditMode && lab.id === currentLabId) || (!isEditMode && lab.id === requestedLabId)) {
                            option.selected = true;
                        }
                        
                        labSelectEl.appendChild(option);
                    });
                    labSelectEl.disabled = false;
                    
                    // Trigger validation after loading labs
                    validateStudentCount();
                }
            })
            .catch(error => {
                console.error('Error fetching labs:', error);
                labSelectEl.innerHTML = '<option value="">Gagal memuat data lab</option>';
                labSelectEl.disabled = true;
            });
        }

        // Event listeners for time fields - fetch labs when any changes
        [daySelectEl, startTimeEl, endTimeEl, startDateEl, endDateEl].forEach(el => {
            el.addEventListener('change', fetchAvailableLabs);
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (!isEditMode) {
                // In create mode, fetch if time fields already have values
                if (daySelectEl.value && startTimeEl.value && endTimeEl.value) {
                    fetchAvailableLabs();
                }
            }
        });

        // Conditional Fields Logic
        const typeSelect = document.getElementById('typeSelect');
        const perkuliahanFields = document.getElementById('perkuliahan-fields');
        const nonPerkuliahanFields = document.getElementById('non-perkuliahan-fields');
        const nonfixedSchedulePattern = document.getElementById('nonfixed-schedule-pattern');
        const recurrenceDaysPanel = document.getElementById('recurrence-days-panel');
        const recurrenceValidationError = document.getElementById('recurrence-validation-error');
        const recurrenceDayInputs = Array.from(document.querySelectorAll('.recurrence-day'));

        // Track previous type for smart field transfer
        let previousType = typeSelect.value;

        // Function to show/hide fields based on type
        function updateFieldsVisibility() {
            const selectedType = typeSelect.value;
            const isNonfixedMultiple = selectedType === 'perkuliahan_tidak_tetap'
                && getScheduleFrequency() === 'multiple';
            const isNonfixed = selectedType === 'perkuliahan_tidak_tetap';
            const requiresEndDate = selectedType === 'perkuliahan_tetap' || isNonfixedMultiple;

            startDateEl.required = isNonfixed;
            endDateEl.required = requiresEndDate;
            document.getElementById('start-date-required')?.classList.toggle('hidden', !isNonfixed);
            document.getElementById('end-date-required')?.classList.toggle('hidden', !requiresEndDate);
            const startDateHelp = document.getElementById('start-date-help');
            const endDateHelp = document.getElementById('end-date-help');
            if (startDateHelp) {
                startDateHelp.textContent = isNonfixed
                    ? 'Wajib untuk menentukan tanggal pelaksanaan.'
                    : 'Kosongkan jika jadwal berlaku tanpa tanggal mulai.';
            }
            if (endDateHelp) {
                endDateHelp.textContent = isNonfixedMultiple
                    ? 'Pilih tanggal akhir rangkaian; maksimal 60 pertemuan.'
                    : selectedType === 'perkuliahan_tetap'
                        ? 'Wajib untuk membatasi periode jadwal perkuliahan tetap.'
                        : 'Untuk jadwal sekali, tanggal selesai akan disamakan dengan tanggal mulai.';
            }
            
            // Smart field transfer when switching types
            transferFieldsBetweenTypes(previousType, selectedType);
            previousType = selectedType;
            
            // Hide all conditional sections first
            perkuliahanFields.classList.add('hidden');
            nonPerkuliahanFields.classList.add('hidden');
            
            // Disable all conditional fields
            setFieldsRequired('perkuliahan-fields', false);
            setFieldsRequired('non-perkuliahan-fields', false);
            
            // Show and enable appropriate section
            if (selectedType === 'perkuliahan_tetap' || selectedType === 'perkuliahan_tidak_tetap') {
                perkuliahanFields.classList.remove('hidden');
                setFieldsRequired('perkuliahan-fields', true);
            } else if (selectedType === 'non_perkuliahan') {
                nonPerkuliahanFields.classList.remove('hidden');
                setFieldsRequired('non-perkuliahan-fields', true);
            }

            // Doc phone number: only for non_perkuliahan (perkuliahan uses komting_phone)
            const docPhoneEl = document.getElementById('doc-phone-number');
            if (docPhoneEl) {
                if (selectedType === 'non_perkuliahan') {
                    docPhoneEl.classList.remove('hidden');
                } else {
                    docPhoneEl.classList.add('hidden');
                }
            }

            updateRecurrenceVisibility();
        }

        function getScheduleFrequency() {
            return document.querySelector('input[name="schedule_frequency"]:checked')?.value || 'once';
        }

        function getSelectedRecurrenceDays() {
            return recurrenceDayInputs.filter(input => input.checked && !input.disabled).map(input => input.value);
        }

        function updateRecurrenceVisibility() {
            const isNonfixed = typeSelect.value === 'perkuliahan_tidak_tetap';
            const isMultiple = isNonfixed && getScheduleFrequency() === 'multiple';

            nonfixedSchedulePattern?.classList.toggle('hidden', !isNonfixed);
            recurrenceDaysPanel?.classList.toggle('hidden', !isMultiple);
            recurrenceDayInputs.forEach(input => {
                input.disabled = !isMultiple;
            });

            if (isNonfixed && !isMultiple && startDateEl.value) {
                endDateEl.value = startDateEl.value;
            }

            endDateEl.required = typeSelect.value === 'perkuliahan_tetap' || isMultiple;
            document.getElementById('end-date-required')?.classList.toggle('hidden', !endDateEl.required);
            validateRecurrencePattern();
        }

        function validateRecurrencePattern() {
            if (typeSelect.value !== 'perkuliahan_tidak_tetap' || getScheduleFrequency() !== 'multiple') {
                recurrenceValidationError?.classList.add('hidden');
                return true;
            }

            const selectedDays = getSelectedRecurrenceDays();
            if (selectedDays.length === 0) {
                if (recurrenceValidationError) {
                    recurrenceValidationError.textContent = 'Pilih minimal satu hari untuk jadwal berulang.';
                    recurrenceValidationError.classList.remove('hidden');
                }
                return false;
            }

            if (!startDateEl.value || !endDateEl.value) {
                if (recurrenceValidationError) {
                    recurrenceValidationError.textContent = 'Isi tanggal mulai dan tanggal selesai untuk jadwal berulang.';
                    recurrenceValidationError.classList.remove('hidden');
                }
                return false;
            }

            const startDay = dayNames[new Date(`${startDateEl.value}T00:00:00`).getDay()];
            if (!selectedDays.includes(startDay)) {
                if (recurrenceValidationError) {
                    recurrenceValidationError.textContent = `Tanggal mulai harus jatuh pada salah satu hari yang dipilih (${selectedDays.join(', ')}).`;
                    recurrenceValidationError.classList.remove('hidden');
                }
                return false;
            }

            recurrenceValidationError?.classList.add('hidden');
            return true;
        }

        /**
         * Transfer coordinator/phone data when switching between types
         * Koordinator = Komting (same role, different label)
         */
        function transferFieldsBetweenTypes(fromType, toType) {
            if (fromType === toType) return;

            const isFromPerkuliahan = (fromType === 'perkuliahan_tetap' || fromType === 'perkuliahan_tidak_tetap');
            const isToPerkuliahan = (toType === 'perkuliahan_tetap' || toType === 'perkuliahan_tidak_tetap');
            const isFromNonPerkuliahan = (fromType === 'non_perkuliahan');
            const isToNonPerkuliahan = (toType === 'non_perkuliahan');

            // Get field references
            const komtingInput = document.querySelector('input[name="komting"]');
            const komtingPhoneInput = document.querySelector('input[name="komting_phone"]');
            const picNameInput = document.querySelector('input[name="pic_name_non_perkuliahan"]');
            const docPhoneInput = document.querySelector('input[name="doc_phone_number"]');

            // Non-perkuliahan → Perkuliahan: PIC → Komting, Telp Koordinator → Telp Komting
            if (isFromNonPerkuliahan && isToPerkuliahan) {
                if (komtingInput && picNameInput && picNameInput.value) {
                    komtingInput.value = picNameInput.value;
                }
                if (komtingPhoneInput && docPhoneInput && docPhoneInput.value) {
                    komtingPhoneInput.value = docPhoneInput.value;
                }
            }

            // Perkuliahan → Non-perkuliahan: Komting → PIC, Telp Komting → Telp Koordinator
            if (isFromPerkuliahan && isToNonPerkuliahan) {
                if (picNameInput && komtingInput && komtingInput.value) {
                    picNameInput.value = komtingInput.value;
                }
                if (docPhoneInput && komtingPhoneInput && komtingPhoneInput.value) {
                    docPhoneInput.value = komtingPhoneInput.value;
                }
            }
        }

        // Function to set required attribute on fields
        function setFieldsRequired(containerId, required) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const inputs = container.querySelectorAll('input, select, textarea');
            const optionalFields = ['equipment_needs', 'lecturer', 'komting', 'komting_phone', 'position'];
            
            inputs.forEach(input => {
                const fieldName = input.name || input.id;
                
                // Skip optional fields
                if (optionalFields.includes(fieldName)) {
                    input.removeAttribute('required');
                    if (!required) {
                        input.setAttribute('disabled', 'disabled');
                    } else {
                        input.removeAttribute('disabled');
                    }
                    return;
                }
                
                // Handle required/disabled based on visibility
                if (required) {
                    input.setAttribute('required', 'required');
                    input.removeAttribute('disabled');
                } else {
                    input.removeAttribute('required');
                    input.setAttribute('disabled', 'disabled');
                }
            });
        }

        // Event listeners
        typeSelect.addEventListener('change', function() {
            updateFieldsVisibility();
            updateDocFieldsVisibility();
            fetchAvailableLabs();
        });

        document.querySelectorAll('input[name="schedule_frequency"]').forEach(input => {
            input.addEventListener('change', function() {
                updateRecurrenceVisibility();
                validateDayInDateRange();
                fetchAvailableLabs();
            });
        });

        recurrenceDayInputs.forEach(input => {
            input.addEventListener('change', function() {
                validateRecurrencePattern();
                validateDayInDateRange();
                fetchAvailableLabs();
            });
        });

        // Document section toggle
        function toggleDocSection() {
            const section = document.getElementById('doc-section');
            const chevron = document.getElementById('doc-chevron');
            const trigger = document.getElementById('doc-toggle');
            const isOpening = section.classList.contains('hidden');
            section.classList.toggle('hidden', !isOpening);
            chevron.classList.toggle('rotate-180');
            trigger?.setAttribute('aria-expanded', String(isOpening));
        }

        // Confirm Delete KTM
        function confirmDeleteKtm() {
            if (confirm('Hapus file KTM?')) {
                document.getElementById('delete-ktm-form').submit();
            }
        }

        // Document fields visibility based on type
        function updateDocFieldsVisibility() {
            const selectedType = typeSelect.value;
            const lecturerNip = document.getElementById('doc-lecturer-nip');
            const nim = document.getElementById('doc-nim');
            const nip = document.getElementById('doc-nip');

            if (!lecturerNip || !nim || !nip) return;

            // Reset: hide all type-specific doc fields
            lecturerNip.classList.add('hidden');
            nim.classList.add('hidden');
            nip.classList.add('hidden');

            if (selectedType === 'perkuliahan_tetap' || selectedType === 'perkuliahan_tidak_tetap') {
                lecturerNip.classList.remove('hidden');
            } else if (selectedType === 'non_perkuliahan') {
                nim.classList.remove('hidden');
                nip.classList.remove('hidden');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateFieldsVisibility();
            updateDocFieldsVisibility();

            // Auto-expand doc section if editing and has document data
            @if($isEdit && isset($schedule) && $schedule->document)
                toggleDocSection();
            @endif
        });

        // Real-time validation for day in date range
        // Re-use existing variables: daySelectEl, startDateEl, endDateEl from above
        const submitButton = document.querySelector('button[type="submit"]');
        
        // Create error message container
        const errorContainer = document.createElement('div');
        errorContainer.className = 'hidden mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700';
        errorContainer.id = 'day-validation-error';
        endDateEl.parentElement.appendChild(errorContainer);

        // Indonesian day names mapping
        const dayNames = {
            0: 'Minggu',
            1: 'Senin',
            2: 'Selasa',
            3: 'Rabu',
            4: 'Kamis',
            5: 'Jumat',
            6: 'Sabtu'
        };

        function validateDayInDateRange() {
            const selectedDay = daySelectEl.value;
            const startDate = startDateEl.value;
            const endDate = endDateEl.value;
            const recurrenceDays = getSelectedRecurrenceDays();
            const isMultipleNonfixed = typeSelect.value === 'perkuliahan_tidak_tetap'
                && getScheduleFrequency() === 'multiple';
            const allowedDays = isMultipleNonfixed ? recurrenceDays : [selectedDay];

            // If no day selected or no dates, clear error
            if (!selectedDay || (!startDate && !endDate)) {
                errorContainer.classList.add('hidden');
                return validateRecurrencePattern();
            }

            if (isMultipleNonfixed && recurrenceDays.length === 0) {
                errorContainer.classList.add('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                return false;
            }

            // Parse dates
            const start = new Date(`${startDate || endDate}T00:00:00`);
            const end = new Date(`${endDate || startDate}T00:00:00`);

            if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
                errorContainer.textContent = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
                errorContainer.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                return false;
            }

            const startDayOfWeek = start.getDay();
            const startDayName = dayNames[startDayOfWeek];
            
            if (!allowedDays.includes(startDayName)) {
                const formattedStart = start.toLocaleDateString('id-ID');
                errorContainer.innerHTML = `
                    <p class="font-bold">Validasi tanggal mulai</p>
                    <p class="text-sm mt-1">Tanggal mulai (<strong>${formattedStart}</strong>) adalah hari <strong>${startDayName}</strong>.</p>
                    <p class="text-sm mt-1">Pilih tanggal yang jatuh pada salah satu hari: <strong>${allowedDays.join(', ')}</strong>.</p>
                `;
                errorContainer.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Check if selected day exists in range
            let dayFound = false;
            const currentDate = new Date(start);

            while (currentDate <= end) {
                const dayOfWeek = currentDate.getDay();
                if (allowedDays.includes(dayNames[dayOfWeek])) {
                    dayFound = true;
                    break;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }

            if (!dayFound) {
                // Format dates for display
                const formattedStart = start.toLocaleDateString('id-ID');
                const formattedEnd = end.toLocaleDateString('id-ID');
                
                errorContainer.innerHTML = `
                    <p class="font-bold">Validasi rentang tanggal</p>
                    <p class="text-sm mt-1">Tidak ada hari yang dipilih dalam rentang tanggal <strong>${formattedStart} - ${formattedEnd}</strong>.</p>
                    <p class="text-sm mt-1">Pilih rentang yang memuat salah satu hari: <strong>${allowedDays.join(', ')}</strong>.</p>
                `;
                errorContainer.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                errorContainer.classList.add('hidden');
                // Check if student count validation also passes before enabling
                const studentCountValid = validateStudentCount();
                if (studentCountValid) {
                    const recurrenceValid = validateRecurrencePattern();
                    submitButton.disabled = !recurrenceValid;
                    submitButton.classList.toggle('opacity-50', !recurrenceValid);
                    submitButton.classList.toggle('cursor-not-allowed', !recurrenceValid);
                }
            }

            return !submitButton.disabled;
        }

        // Add event listeners
        daySelectEl.addEventListener('change', validateDayInDateRange);
        startDateEl.addEventListener('change', validateDayInDateRange);
        endDateEl.addEventListener('change', validateDayInDateRange);

        // Run validation on page load (for edit form with existing data)
        document.addEventListener('DOMContentLoaded', validateDayInDateRange);

        // Real-time validation for student count vs lab capacity
        // Use labSelectEl (already defined above for AJAX)
        const studentCountInput = document.querySelector('input[name="student_count"]');
        
        // Create warning message container for student count (changed to warning style)
        const studentCountErrorContainer = document.createElement('div');
        studentCountErrorContainer.className = 'hidden mt-2 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-r-lg text-sm';
        studentCountErrorContainer.id = 'student-count-error';
        studentCountInput.parentElement.appendChild(studentCountErrorContainer);

        function validateStudentCount() {
            const selectedLabId = labSelectEl.value;
            const studentCount = parseInt(studentCountInput.value);

            // Clear error if no lab selected or no student count
            if (!selectedLabId || !studentCount || studentCount <= 0) {
                studentCountErrorContainer.classList.add('hidden');
                return true;
            }

            // Get capacity from selected option's data attribute (supports dynamic AJAX options)
            const selectedOption = labSelectEl.options[labSelectEl.selectedIndex];
            const labCapacity = selectedOption ? parseInt(selectedOption.dataset.capacity) : 0;
            
            if (!labCapacity || studentCount > labCapacity) {
                const labName = labSelectEl.options[labSelectEl.selectedIndex].text;
                studentCountErrorContainer.innerHTML = `
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-bold">Kapasitas tidak memadai</p>
                            <p class="mt-1">Jumlah mahasiswa (${studentCount}) melebihi kapasitas ${labName} (${labCapacity} orang).</p>
                            <p class="mt-1 italic font-semibold">Konsekuensi: Fasilitas mungkin tidak mencukupi untuk setiap peserta dan ketidaknyamanan ditanggung sendiri.</p>
                        </div>
                    </div>
                `;
                studentCountErrorContainer.classList.remove('hidden');
                
                // Allow submit even with warning
                const dayErrorVisible = !errorContainer.classList.contains('hidden');
                if (!dayErrorVisible) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                return true; 
            } else {
                studentCountErrorContainer.classList.add('hidden');
                // Check if day validation also passes before enabling
                const dayErrorVisible = !errorContainer.classList.contains('hidden');
                if (!dayErrorVisible) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                return true;
            }
        }

        // Add event listeners for student count validation
        labSelectEl.addEventListener('change', validateStudentCount);
        studentCountInput.addEventListener('input', validateStudentCount);

        // Run student count validation on page load
        document.addEventListener('DOMContentLoaded', validateStudentCount);

        // Custom Dropdown Implementation for Mobile Friendliness
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

                // Observe disabled attribute changes AND dynamic options changes on original select
                this.observer = new MutationObserver((mutations) => {
                    let shouldUpdateTrigger = false;
                    let shouldReinitOptions = false;
                    
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                            shouldUpdateTrigger = true;
                        }
                        if (mutation.type === 'childList') {
                            // Options were added/removed (e.g., from AJAX)
                            shouldReinitOptions = true;
                            shouldUpdateTrigger = true;
                        }
                    });
                    
                    if (shouldReinitOptions) this.initOptions();
                    if (shouldUpdateTrigger) this.updateTrigger();
                });
                this.observer.observe(this.originalSelect, { 
                    attributes: true, 
                    childList: true, 
                    subtree: true 
                });

                // Listen for changes on original select (to update UI if changed programmatically)
                this.originalSelect.addEventListener('change', () => {
                   this.updateTrigger();
                   this.initOptions(); // Re-render to update checkmarks
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
                        check.innerHTML = `<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>`;
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
                
                // Handle disabled state
                if (this.originalSelect.disabled) {
                    this.trigger.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-60');
                    this.trigger.setAttribute('disabled', 'disabled');
                    this.trigger.setAttribute('aria-disabled', 'true');
                } else {
                    this.trigger.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-60');
                    this.trigger.removeAttribute('disabled');
                    this.trigger.removeAttribute('aria-disabled');
                }
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

        // Initialize Custom Selects
        document.addEventListener('DOMContentLoaded', function() {
            // Use one custom surface for every select in the schedule form.
            document.querySelectorAll('.admin-schedule-page select').forEach(select => new CustomSelect(select));
        });

        // Lingkup Perubahan (recurring edit) - toggle occurrence date field
        document.addEventListener('DOMContentLoaded', function() {
            const scopeInputs = document.querySelectorAll('input[name="scope"]');
            const occurrenceField = document.getElementById('occurrence-date-field');
            const occurrenceInput = document.getElementById('occurrence_date');
            const scheduleDay = @json($schedule->day ?? null);

            if (!scopeInputs.length || !occurrenceField) return;

            function updateScopeUI() {
                const selected = document.querySelector('input[name="scope"]:checked');
                const isScoped = selected && (selected.value === 'single' || selected.value === 'future');
                occurrenceField.classList.toggle('hidden', !isScoped);

                if (selected && selected.value === 'single' && scheduleDay) {
                    const d = document.getElementById('daySelect');
                    if (d) d.value = scheduleDay;
                }
            }

            // Default the occurrence date to the schedule's next upcoming weekday
            if (occurrenceInput && scheduleDay) {
                const dayMap = {'Senin':1,'Selasa':2,'Rabu':3,'Kamis':4,'Jumat':5,'Sabtu':6};
                const target = dayMap[scheduleDay];
                const now = new Date();
                const daysAhead = (target - now.getDay() + 7) % 7 || 7;
                const next = new Date(now.getFullYear(), now.getMonth(), now.getDate() + daysAhead);
                const pad = n => String(n).padStart(2, '0');
                occurrenceInput.value = `${next.getFullYear()}-${pad(next.getMonth()+1)}-${pad(next.getDate())}`;
            }

            scopeInputs.forEach(input => input.addEventListener('change', updateScopeUI));
            updateScopeUI();
        });
    </script>
@endsection
