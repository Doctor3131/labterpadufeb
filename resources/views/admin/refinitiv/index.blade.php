@extends('layouts.admin')

@section('title', 'Kelola Permintaan Refinitiv - Admin')

@push('styles')
    <style>
        @@view-transition { navigation: auto; }
    </style>
@endpush

@section('content')
    @php
        $search = $search ?? (string) request()->query('q', '');
        $sort = $sort ?? (string) request()->query('sort', 'schedule_asc');
        $counts['all'] = $counts['all'] ?? (($counts['pending'] ?? 0) + ($counts['hadir'] ?? 0) + ($counts['tidak_hadir'] ?? 0));
        $tabLink = fn (string $tabStatus) => route('admin.refinitiv.index', [
            'status' => $tabStatus,
            'q' => $search !== '' ? $search : null,
            'sort' => $sort,
        ]);
        $clearFiltersUrl = route('admin.refinitiv.index', ['status' => $status]);
    @endphp

    <div class="refinitiv-page">
        <div class="mb-5">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-md text-sm font-medium text-slate-600 transition hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-1 text-sm font-semibold uppercase tracking-wide text-blue-700">Layanan data</p>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Permintaan Refinitiv</h1>
                <p class="mt-1 text-sm text-slate-600">Cari permohonan, tinjau jadwal, dan catat kehadiran.</p>
            </div>
            <div class="inline-flex w-fit items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-900">
                <svg class="h-4 w-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4v-4M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $requests->total() }} permohonan
            </div>
        </header>

        <section class="relative z-10 mb-5 rounded-xl border border-slate-200 bg-white shadow-sm" aria-label="Filter permohonan Refinitiv">
            <nav class="flex overflow-x-auto border-b border-slate-200" aria-label="Filter status kehadiran">
                <a href="{{ $tabLink('all') }}" @if($status === 'all') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500 {{ $status === 'all' ? 'border-blue-600 bg-blue-50 text-blue-900' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Semua <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'all' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ $tabLink('pending') }}" @if($status === 'pending') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500 {{ $status === 'pending' ? 'border-amber-500 bg-amber-50 text-amber-900' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Menunggu <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'pending' ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-600' }}">{{ $counts['pending'] }}</span>
                </a>
                <a href="{{ $tabLink('hadir') }}" @if($status === 'hadir') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-green-600 {{ $status === 'hadir' ? 'border-green-600 bg-green-50 text-green-800' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Hadir <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['hadir'] }}</span>
                </a>
                <a href="{{ $tabLink('tidak_hadir') }}" @if($status === 'tidak_hadir') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-red-500 {{ $status === 'tidak_hadir' ? 'border-red-600 bg-red-50 text-red-800' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Tidak Hadir <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'tidak_hadir' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['tidak_hadir'] }}</span>
                </a>
            </nav>

            <form id="refinitiv-filters" action="{{ route('admin.refinitiv.index') }}" method="GET" class="grid grid-cols-1 gap-4 p-4 md:grid-cols-[minmax(0,1fr)_minmax(15rem,0.42fr)_auto] md:items-end">
                <input type="hidden" name="status" value="{{ $status }}">
                <div>
                    <label for="refinitiv-search" class="mb-1.5 block text-sm font-semibold text-slate-700">Cari pemohon</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="m20 20-4-4"/>
                        </svg>
                        <input id="refinitiv-search" type="search" name="q" value="{{ $search }}" placeholder="Nama, NIM/NIP, WhatsApp, atau ID"
                               class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                               autocomplete="off" aria-describedby="refinitiv-search-help">
                    </div>
                </div>

                <div>
                    <label id="refinitiv-sort-label" for="refinitiv-sort" class="mb-1.5 block text-sm font-semibold text-slate-700">Urutkan</label>
                    <div class="custom-select-wrapper relative">
                        <select id="refinitiv-sort" name="sort" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="schedule_asc" @selected($sort === 'schedule_asc')>Jadwal terdekat</option>
                            <option value="schedule_desc" @selected($sort === 'schedule_desc')>Jadwal paling baru</option>
                            <option value="recent" @selected($sort === 'recent')>Permohonan terbaru</option>
                            <option value="name_asc" @selected($sort === 'name_asc')>Nama A–Z</option>
                        </select>
                        <button type="button" class="custom-select-trigger hidden" data-refinitiv-sort-trigger aria-haspopup="listbox" aria-expanded="false" aria-labelledby="refinitiv-sort-label refinitiv-sort-value" aria-controls="refinitiv-sort-options">
                            <span id="refinitiv-sort-value" class="block truncate"></span>
                            <svg class="custom-select-chevron h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="refinitiv-sort-options" class="custom-select-options hidden" role="listbox" aria-labelledby="refinitiv-sort-label">
                            @foreach ([
                                'schedule_asc' => 'Jadwal terdekat',
                                'schedule_desc' => 'Jadwal paling baru',
                                'recent' => 'Permohonan terbaru',
                                'name_asc' => 'Nama A–Z',
                            ] as $value => $label)
                                <button type="button" class="custom-select-option w-full text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-700" role="option" data-value="{{ $value }}" aria-selected="{{ $sort === $value ? 'true' : 'false' }}" tabindex="-1">
                                    <span>{{ $label }}</span>
                                    @if ($sort === $value)
                                        <svg class="h-4 w-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/>
                                        </svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 md:pb-0.5">
                    <span class="text-xs text-slate-500" aria-hidden="true">Filter otomatis</span>
                    @if($search !== '' || $sort !== 'schedule_asc')
                        <a href="{{ $clearFiltersUrl }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                            Reset
                        </a>
                    @endif
                </div>
                <p id="refinitiv-search-help" class="sr-only">Hasil pencarian diperbarui otomatis setelah Anda berhenti mengetik.</p>
                <noscript>
                    <div class="text-sm text-slate-600">
                        <p>JavaScript tidak aktif. Perbarui hasil setelah mengubah pencarian atau urutan.</p>
                        <button type="submit" class="mt-2 inline-flex min-h-[40px] items-center justify-center rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white">Perbarui filter</button>
                    </div>
                </noscript>
            </form>
        </section>

        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600" aria-live="polite" aria-atomic="true">
            <p>
                Menampilkan <span class="font-semibold text-slate-900">{{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-slate-900">{{ $requests->total() }}</span> permohonan
                @if($search !== '') <span>untuk “{{ $search }}”</span> @endif
            </p>
            @if($requests->total() > 0)
                <p class="text-xs text-slate-500">Pilih detail untuk melihat dokumen dan informasi lengkap.</p>
            @endif
        </div>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-label="Daftar permohonan Refinitiv">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left xl:min-w-[1060px]">
                    <caption class="sr-only">Daftar permohonan data Refinitiv</caption>
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th scope="col" class="schedule-table-heading w-[270px]">Pemohon</th>
                            <th scope="col" class="schedule-table-heading w-[190px]">Jadwal</th>
                            <th scope="col" class="schedule-table-heading hidden w-[270px] xl:table-cell">Keperluan</th>
                            <th scope="col" class="schedule-table-heading w-[125px]">Kehadiran</th>
                            <th scope="col" class="schedule-table-heading w-[205px] text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($requests as $req)
                            @php
                                $attendanceStyles = [
                                    'pending' => 'bg-amber-100 text-amber-900',
                                    'hadir' => 'bg-green-100 text-green-800',
                                    'tidak_hadir' => 'bg-red-100 text-red-800',
                                ];
                                $detailsUrl = route('admin.refinitiv.show', [
                                    'request' => $req,
                                    'status' => $status,
                                    'q' => $search !== '' ? $search : null,
                                    'sort' => $sort,
                                ]);
                            @endphp
                            <tr class="align-top transition-colors hover:bg-blue-50/40">
                                <td class="px-4 py-4">
                                    <div class="font-semibold leading-5 text-slate-900">{{ $req->name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $req->isLecturer() ? 'Dosen' : 'Mahasiswa' }}
                                        <span aria-hidden="true">·</span>
                                        <span class="inline-block max-w-[190px] truncate align-bottom" title="{{ $req->affiliation_label }}">{{ $req->affiliation_label }}</span>
                                    </div>
                                    <div class="mt-2 space-y-0.5 text-xs text-slate-600">
                                        <p>{{ $req->isLecturer() ? 'NIP' : 'NIM' }}: <span class="font-medium text-slate-800">{{ $req->nim_nip }}</span></p>
                                        @if($req->study_program)
                                            <p class="truncate" title="{{ $req->study_program }}">Prodi: <span class="font-medium text-slate-800">{{ $req->study_program }}</span></p>
                                        @endif
                                        <p>WhatsApp: <span class="font-medium text-slate-800">{{ $req->whatsapp }}</span></p>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">{{ $req->usage_date->locale('id')->isoFormat('D MMM Y') }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-600">{{ \App\Models\RefinitivRequest::SESSIONS[$req->session] ?? $req->session }}</p>
                                    <p class="mt-2 text-[11px] text-slate-400" title="Diajukan {{ $req->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB">Diajukan {{ $req->created_at->locale('id')->diffForHumans() }}</p>
                                </td>
                                <td class="hidden px-4 py-4 xl:table-cell">
                                    <p class="font-medium text-slate-800">{{ $req->purpose_label }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-600" title="{{ $req->variables }}">{{ Str::limit($req->variables, 105) }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendanceStyles[$req->attendance_status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $req->attendance_status_label }}
                                    </span>
                                    @if($req->attendance_marked_at)
                                        <p class="mt-1.5 text-[11px] text-slate-500" title="Dicatat {{ $req->attendance_marked_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB">{{ $req->attendance_marked_at->locale('id')->isoFormat('D MMM Y') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <a href="{{ $detailsUrl }}" class="inline-flex min-h-[36px] items-center justify-center rounded-lg border border-blue-200 bg-white px-2.5 text-xs font-semibold text-blue-800 transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1">Detail</a>
                                        @if($req->attendance_status === 'pending')
                                            <form action="{{ route('admin.refinitiv.hadir', $req) }}" method="POST" onsubmit="return window.confirm('Tandai pemohon ini hadir?')">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="inline-flex min-h-[36px] items-center justify-center rounded-lg bg-blue-700 px-2.5 text-xs font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-1">Hadir</button>
                                            </form>
                                            <form action="{{ route('admin.refinitiv.tidak-hadir', $req) }}" method="POST" onsubmit="return window.confirm('Tandai pemohon ini tidak hadir?')">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="inline-flex min-h-[36px] items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-semibold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-1">Absen</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.refinitiv.reset', $req) }}" method="POST" onsubmit="return window.confirm('Reset status kehadiran pemohon ini ke Menunggu?')">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="inline-flex min-h-[36px] items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1">Reset</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-700">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h8m-8 4h8m-8 4h5m-8 5h14a2 2 0 002-2V7l-5-5H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                    @if($search !== '')
                                        <h2 class="text-base font-semibold text-slate-900">Tidak ada hasil yang cocok</h2>
                                        <p class="mt-1 text-sm text-slate-600">Coba periksa ejaan atau cari dengan nama, NIM/NIP, nomor WhatsApp, atau ID.</p>
                                        <a href="{{ $clearFiltersUrl }}" class="mt-4 inline-flex min-h-[40px] items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">Hapus pencarian</a>
                                    @else
                                        <h2 class="text-base font-semibold text-slate-900">
                                            @if($status === 'pending') Belum ada permohonan menunggu
                                            @elseif($status === 'hadir') Belum ada pemohon tercatat hadir
                                            @elseif($status === 'tidak_hadir') Belum ada pemohon tercatat tidak hadir
                                            @else Belum ada permohonan Refinitiv @endif
                                        </h2>
                                        <p class="mt-1 text-sm text-slate-600">Permohonan dengan status ini akan tampil di sini.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="border-t border-slate-100 px-4 py-2 text-xs text-slate-500 xl:hidden">Geser tabel ke samping untuk melihat kolom lainnya.</p>
        </section>

        @if($requests->hasPages())
            <div class="mt-5">
                {{ $requests->appends(['status' => $status, 'q' => $search, 'sort' => $sort])->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('refinitiv-filters');
            const search = document.getElementById('refinitiv-search');
            const select = document.getElementById('refinitiv-sort');
            const trigger = document.querySelector('[data-refinitiv-sort-trigger]');
            const options = document.getElementById('refinitiv-sort-options');
            const wrapper = select?.closest('.custom-select-wrapper');

            if (!form || !search || !select || !trigger || !options || !wrapper) return;

            const optionItems = [...options.querySelectorAll('[role="option"]')];
            const sortLabel = document.getElementById('refinitiv-sort-value');
            let searchTimer;

            const syncSelection = () => {
                const selected = select.selectedOptions[0];
                sortLabel.textContent = selected?.textContent.trim() ?? '';

                optionItems.forEach((option) => {
                    const isSelected = option.dataset.value === select.value;
                    option.setAttribute('aria-selected', String(isSelected));
                    option.querySelector('svg')?.remove();

                    if (isSelected) {
                        const checkmark = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                        checkmark.setAttribute('class', 'h-4 w-4 text-blue-700');
                        checkmark.setAttribute('viewBox', '0 0 24 24');
                        checkmark.setAttribute('fill', 'none');
                        checkmark.setAttribute('stroke', 'currentColor');
                        checkmark.setAttribute('aria-hidden', 'true');
                        checkmark.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"></path>';
                        option.appendChild(checkmark);
                    }
                });
            };

            const closeOptions = ({ returnFocus = false } = {}) => {
                options.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
                if (returnFocus) trigger.focus();
            };

            const openOptions = (focusSelected = false) => {
                options.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');

                if (focusSelected) {
                    (optionItems.find((option) => option.dataset.value === select.value) ?? optionItems[0])?.focus();
                }
            };

            const chooseOption = (option) => {
                if (!option) return;
                const changed = select.value !== option.dataset.value;
                select.value = option.dataset.value;
                syncSelection();
                closeOptions({ returnFocus: true });
                if (changed) form.requestSubmit();
            };

            select.classList.add('custom-select-native');
            select.setAttribute('aria-hidden', 'true');
            select.tabIndex = -1;
            trigger.classList.remove('hidden');
            syncSelection();

            search.addEventListener('input', () => {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(() => form.requestSubmit(), 450);
            });

            search.addEventListener('search', () => {
                window.clearTimeout(searchTimer);
                form.requestSubmit();
            });

            trigger.addEventListener('click', () => {
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';
                if (isOpen) closeOptions();
                else openOptions(true);
            });

            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    openOptions(true);
                } else if (event.key === 'Escape' && trigger.getAttribute('aria-expanded') === 'true') {
                    event.preventDefault();
                    closeOptions();
                }
            });

            optionItems.forEach((option, index) => {
                option.addEventListener('click', () => chooseOption(option));
                option.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        closeOptions({ returnFocus: true });
                    } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        const direction = event.key === 'ArrowDown' ? 1 : -1;
                        optionItems[(index + direction + optionItems.length) % optionItems.length]?.focus();
                    } else if (event.key === 'Home' || event.key === 'End') {
                        event.preventDefault();
                        optionItems[event.key === 'Home' ? 0 : optionItems.length - 1]?.focus();
                    } else if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        chooseOption(option);
                    }
                });
            });

            wrapper.addEventListener('focusout', (event) => {
                if (!wrapper.contains(event.relatedTarget)) closeOptions();
            });

            document.addEventListener('click', (event) => {
                if (!wrapper.contains(event.target)) closeOptions();
            });
        })();
    </script>
@endpush
