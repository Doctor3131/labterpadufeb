{{-- Schedule Table Partial - For AJAX filtering --}}
@php
    $typeColors = [
        'perkuliahan_tetap' => 'bg-yellow-100 text-yellow-800',
        'perkuliahan_tidak_tetap' => 'bg-indigo-100 text-indigo-800',
        'non_perkuliahan' => 'bg-emerald-100 text-emerald-800',
        'booking_recurring' => 'bg-yellow-100 text-yellow-800',
        'booking_onetime' => 'bg-gray-100 text-gray-800',
    ];
    $typeLabels = [
        'perkuliahan_tetap' => 'Perkuliahan tetap',
        'perkuliahan_tidak_tetap' => 'Perkuliahan tidak tetap',
        'non_perkuliahan' => 'Non-perkuliahan',
        'booking_recurring' => 'Tetap (lama)',
        'booking_onetime' => 'Sekali (lama)',
    ];
    $calendarService = app(\App\Services\ScheduleCalendarService::class);
@endphp

{{-- Desktop Table View --}}
<div class="hidden overflow-x-auto md:block" id="desktop-table">
    <table class="w-full min-w-[900px]">
        <caption class="sr-only">Daftar jadwal laboratorium</caption>
        <thead class="border-b border-gray-200 bg-gray-50">
            <tr>
                <th scope="col" class="schedule-table-heading">Jadwal</th>
                <th scope="col" class="schedule-table-heading min-w-[230px]">Kegiatan</th>
                <th scope="col" class="schedule-table-heading min-w-[170px]">Periode</th>
                <th scope="col" class="schedule-table-heading">Jenis</th>
                <th scope="col" class="schedule-table-heading">Peserta</th>
                <th scope="col" class="schedule-table-heading text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($schedules as $schedule)
                @php
                    $isRecurring = $calendarService->isRecurringSchedule($schedule);
                    $courseName = trim((string) $schedule->course) ?: 'Jadwal laboratorium';
                    $daysLabel = $schedule->recurrence_days ? implode(', ', $schedule->recurrence_days) : ($schedule->day ?: 'Hari belum ditentukan');
                    $timeLabel = \Carbon\Carbon::parse($schedule->start_time)->format('H:i').'–'.\Carbon\Carbon::parse($schedule->end_time)->format('H:i');
                    $startDateLabel = $schedule->start_date?->format('d M Y') ?? 'Tanpa tanggal mulai';
                    $endDateLabel = $schedule->end_date?->format('d M Y') ?? 'Tanpa batas akhir';
                    $participantLabel = $schedule->student_count ? number_format($schedule->student_count, 0, ',', '.') : '-';
                @endphp
                <tr class="group align-top transition-colors hover:bg-yellow-50/40">
                    <td class="px-4 py-4">
                        <div class="flex gap-3">
                            <span class="schedule-table-marker" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <div class="truncate font-bold text-gray-900" title="{{ $schedule->lab->name }}">{{ $schedule->lab->name }}</div>
                                <div class="mt-1 text-xs leading-5 text-gray-500">{{ $daysLabel }}</div>
                                <div class="text-sm font-semibold text-gray-700">{{ $timeLabel }} <span class="text-xs font-normal text-gray-400">WIB</span></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="font-semibold leading-5 text-gray-900">{{ $courseName }}</div>
                        @if($schedule->lecturer)
                            <div class="mt-1 text-xs text-gray-500">Dosen/PIC: <span class="text-gray-700">{{ $schedule->lecturer }}</span></div>
                        @endif
                        @if($schedule->komting)
                            <div class="text-xs text-gray-500">
                                {{ in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']) ? 'Komting' : 'Peminjam' }}:
                                <span class="text-gray-700">{{ $schedule->komting }}</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-semibold text-gray-800">{{ $startDateLabel }}</div>
                        <div class="mt-0.5 text-xs text-gray-500">s.d. {{ $endDateLabel }}</div>
                        <span class="mt-2 inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-[11px] font-bold text-gray-600">
                            {{ $isRecurring ? 'Berulang' : 'Sekali' }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex max-w-[145px] rounded-full px-2.5 py-1 text-xs font-bold leading-4 {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $typeLabels[$schedule->type] ?? $schedule->type }}
                        </span>
                        @if($schedule->booking)
                            <div class="mt-1 text-[11px] text-gray-400">Pengajuan #{{ $schedule->booking_id }}</div>
                        @else
                            <div class="mt-1 text-[11px] text-gray-400">Dibuat admin</div>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-bold text-gray-800">{{ $participantLabel }}</div>
                        <div class="text-xs text-gray-500">orang</div>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <div class="relative inline-block text-left">
                            <button type="button"
                                    class="schedule-table-menu-button"
                                    data-table-menu-button
                                    data-menu-target="schedule-menu-desktop-{{ $schedule->id }}"
                                    aria-haspopup="menu"
                                    aria-expanded="false"
                                    aria-label="Buka aksi untuk {{ $courseName }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.4"/><circle cx="12" cy="12" r="1.4"/><circle cx="19" cy="12" r="1.4"/></svg>
                            </button>
                            <div id="schedule-menu-desktop-{{ $schedule->id }}" class="schedule-table-menu hidden" data-table-menu role="menu">
                                <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="schedule-table-menu-item" role="menuitem">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L8 18l-4 1 1-4 11.5-11.5z"/></svg>
                                    <span>Edit detail</span>
                                </a>
                                <a href="{{ route('admin.schedules.print', $schedule->id) }}" target="_blank" rel="noopener" class="schedule-table-menu-item" role="menuitem">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v7H6z"/></svg>
                                    <span>Cetak jadwal</span>
                                </a>
                                <button type="button" class="schedule-table-menu-item schedule-table-menu-danger" role="menuitem"
                                        onclick="openDeleteModal({{ $schedule->id }}, @js($courseName), {{ $isRecurring ? 'true' : 'false' }}, @js($schedule->day), null)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>
                                    <span>Batalkan jadwal</span>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="font-semibold text-gray-700">Tidak ada jadwal yang sesuai</p>
                        <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci atau filter yang dipilih.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Mobile Card View --}}
<div class="divide-y divide-gray-100 md:hidden" id="mobile-cards">
    @forelse($schedules as $schedule)
        @php
            $isRecurring = $calendarService->isRecurringSchedule($schedule);
            $courseName = trim((string) $schedule->course) ?: 'Jadwal laboratorium';
            $daysLabel = $schedule->recurrence_days ? implode(', ', $schedule->recurrence_days) : ($schedule->day ?: 'Hari belum ditentukan');
            $timeLabel = \Carbon\Carbon::parse($schedule->start_time)->format('H:i').'–'.\Carbon\Carbon::parse($schedule->end_time)->format('H:i');
            $startDateLabel = $schedule->start_date?->format('d M Y') ?? 'Tanpa tanggal mulai';
            $endDateLabel = $schedule->end_date?->format('d M Y') ?? 'Tanpa batas akhir';
            $participantLabel = $schedule->student_count ? number_format($schedule->student_count, 0, ',', '.') : '-';
        @endphp
        <article class="p-4 transition-colors hover:bg-yellow-50/40">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="truncate font-bold text-gray-900">{{ $courseName }}</h3>
                    <p class="mt-1 text-sm font-semibold text-gray-700">{{ $schedule->lab->name }}</p>
                </div>
                <div class="relative shrink-0">
                    <button type="button"
                            class="schedule-table-menu-button"
                            data-table-menu-button
                            data-menu-target="schedule-menu-mobile-{{ $schedule->id }}"
                            aria-haspopup="menu"
                            aria-expanded="false"
                            aria-label="Buka aksi untuk {{ $courseName }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.4"/><circle cx="12" cy="12" r="1.4"/><circle cx="19" cy="12" r="1.4"/></svg>
                    </button>
                    <div id="schedule-menu-mobile-{{ $schedule->id }}" class="schedule-table-menu hidden" data-table-menu role="menu">
                        <a href="{{ route('admin.schedules.edit', $schedule->id) }}" class="schedule-table-menu-item" role="menuitem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L8 18l-4 1 1-4 11.5-11.5z"/></svg>
                            <span>Edit detail</span>
                        </a>
                        <a href="{{ route('admin.schedules.print', $schedule->id) }}" target="_blank" rel="noopener" class="schedule-table-menu-item" role="menuitem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v7H6z"/></svg>
                            <span>Cetak jadwal</span>
                        </a>
                        <button type="button" class="schedule-table-menu-item schedule-table-menu-danger" role="menuitem"
                                onclick="openDeleteModal({{ $schedule->id }}, @js($courseName), {{ $isRecurring ? 'true' : 'false' }}, @js($schedule->day), null)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/></svg>
                            <span>Batalkan jadwal</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 rounded-lg border border-gray-100 bg-gray-50/70 p-3">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Hari & waktu</div>
                    <div class="mt-1 text-sm font-semibold text-gray-800">{{ $daysLabel }}</div>
                    <div class="text-xs text-gray-500">{{ $timeLabel }} WIB</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Periode</div>
                    <div class="mt-1 text-sm font-semibold text-gray-800">{{ $isRecurring ? 'Berulang' : 'Sekali' }}</div>
                    <div class="text-xs text-gray-500">{{ $startDateLabel }}–{{ $endDateLabel }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Jenis</div>
                    <span class="mt-1 inline-flex rounded-full px-2 py-1 text-[11px] font-bold leading-4 {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-800' }}">{{ $typeLabels[$schedule->type] ?? $schedule->type }}</span>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Peserta</div>
                    <div class="mt-1 text-sm font-semibold text-gray-800">{{ $participantLabel }} orang</div>
                </div>
            </div>

            <div class="mt-3 space-y-1 text-xs text-gray-500">
                @if($schedule->lecturer)
                    <div>Dosen/PIC: <span class="font-semibold text-gray-700">{{ $schedule->lecturer }}</span></div>
                @endif
                @if($schedule->komting)
                    <div>{{ in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']) ? 'Komting' : 'Peminjam' }}: <span class="font-semibold text-gray-700">{{ $schedule->komting }}</span></div>
                @endif
            </div>
        </article>
    @empty
        <div class="p-10 text-center">
            <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="font-semibold text-gray-700">Tidak ada jadwal yang sesuai</p>
            <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci atau filter yang dipilih.</p>
        </div>
    @endforelse
</div>

{{-- Load More Button --}}
@if($schedules->hasMorePages())
    <div class="border-t border-gray-100 bg-white p-3 text-center" id="load-more-container">
        <button type="button"
                id="btn-load-more"
                data-next-url="{{ $schedules->nextPageUrl() }}"
                class="schedule-secondary-button mx-auto inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">
            <span>Tampilkan lebih banyak</span>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m19 9-7 7-7-7"/></svg>
        </button>
    </div>
@endif

{{-- Total Count --}}
<div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-4 py-2.5 text-xs text-gray-500" id="schedule-count">
    <span>Menampilkan <span class="font-bold text-gray-700">{{ $schedules->firstItem() ?? 0 }}</span>–<span class="font-bold text-gray-700">{{ $schedules->lastItem() ?? 0 }}</span> dari <span class="font-bold text-gray-700">{{ $schedules->total() }}</span> jadwal</span>
    <span>Halaman {{ $schedules->currentPage() }} dari {{ $schedules->lastPage() }}</span>
</div>
