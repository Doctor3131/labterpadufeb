@extends('layouts.admin')

@section('title', 'Kelola Permintaan Refinitiv - Admin')

@section('content')
    @php
        $search = $search ?? (string) request()->query('q', '');
        $sort = $sort ?? (string) request()->query('sort', 'schedule_asc');
        $counts['all'] = $counts['all'] ?? (($counts['pending'] ?? 0) + ($counts['hadir'] ?? 0) + ($counts['tidak_hadir'] ?? 0));
    @endphp

    <div class="mb-5">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 rounded-md">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-1 text-sm font-semibold uppercase tracking-wide text-amber-700">Layanan data</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Permintaan Refinitiv</h1>
            <p class="mt-1 text-sm text-slate-600">Cari permohonan dan kelola pencatatan kehadiran.</p>
        </div>
        <div class="inline-flex w-fit items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
            <svg class="h-4 w-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $requests->total() }} permohonan
        </div>
    </header>

    @php
        $tabLink = fn (string $tabStatus) => route('admin.refinitiv.index', [
            'status' => $tabStatus,
            'q' => $search !== '' ? $search : null,
            'sort' => $sort,
        ]);
        $clearFiltersUrl = route('admin.refinitiv.index', ['status' => $status]);
    @endphp

    <section class="relative z-10 mb-5 rounded-xl border border-slate-200 bg-white shadow-sm" aria-label="Filter permohonan Refinitiv">
        <nav class="flex overflow-x-auto border-b border-slate-200" aria-label="Filter status kehadiran">
            <a href="{{ $tabLink('all') }}" @if($status === 'all') aria-current="page" @endif
               class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500 {{ $status === 'all' ? 'border-amber-500 bg-amber-50 text-amber-900' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                Semua <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'all' ? 'bg-amber-200 text-amber-900' : 'bg-slate-100 text-slate-600' }}">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ $tabLink('pending') }}" @if($status === 'pending') aria-current="page" @endif
               class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500 {{ $status === 'pending' ? 'border-amber-500 bg-amber-50 text-amber-900' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                Menunggu <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'pending' ? 'bg-amber-200 text-amber-900' : 'bg-slate-100 text-slate-600' }}">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ $tabLink('hadir') }}" @if($status === 'hadir') aria-current="page" @endif
               class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500 {{ $status === 'hadir' ? 'border-green-600 bg-green-50 text-green-800' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                Hadir <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['hadir'] }}</span>
            </a>
            <a href="{{ $tabLink('tidak_hadir') }}" @if($status === 'tidak_hadir') aria-current="page" @endif
               class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500 {{ $status === 'tidak_hadir' ? 'border-red-600 bg-red-50 text-red-800' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                Tidak Hadir <span class="rounded-full px-2 py-0.5 text-xs {{ $status === 'tidak_hadir' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['tidak_hadir'] }}</span>
            </a>
        </nav>

        <form action="{{ route('admin.refinitiv.index') }}" method="GET" class="grid grid-cols-1 gap-3 p-4 md:grid-cols-[minmax(14rem,1fr)_minmax(13rem,0.7fr)_auto] md:items-end">
            <input type="hidden" name="status" value="{{ $status }}">
            <div>
                <label for="refinitiv-search" class="mb-1.5 block text-sm font-semibold text-slate-700">Cari pemohon</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="m20 20-4-4"/>
                    </svg>
                    <input id="refinitiv-search" type="search" name="q" value="{{ $search }}" placeholder="Nama, NIM/NIP, WhatsApp, atau ID"
                           class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
                           autocomplete="off" aria-describedby="refinitiv-search-help">
                </div>
                <p id="refinitiv-search-help" class="mt-1 text-xs text-slate-500">Pencarian mengikuti tab status yang dipilih.</p>
            </div>

            <div>
                <label id="refinitiv-sort-label" for="refinitiv-sort" class="mb-1.5 block text-sm font-semibold text-slate-700">Urutkan</label>
                <div class="custom-select-wrapper relative">
                    <select id="refinitiv-sort" name="sort" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
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
                            <button type="button" class="custom-select-option w-full text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-amber-700" role="option" data-value="{{ $value }}" aria-selected="{{ $sort === $value ? 'true' : 'false' }}" tabindex="-1">
                                <span>{{ $label }}</span>
                                @if ($sort === $value)
                                    <svg class="h-4 w-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-amber-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-600 focus-visible:ring-offset-2">
                    Terapkan
                </button>
                @if($search !== '' || $sort !== 'schedule_asc')
                    <a href="{{ $clearFiltersUrl }}" class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600" aria-live="polite">
        <p>
            Menampilkan <span class="font-semibold text-slate-900">{{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }}</span>
            dari <span class="font-semibold text-slate-900">{{ $requests->total() }}</span> permohonan
            @if($search !== '') <span>untuk “{{ $search }}”</span> @endif
        </p>
    </div>

    <section class="space-y-3" aria-label="Daftar permohonan Refinitiv">
        @forelse($requests as $req)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-amber-300 hover:shadow-md sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $req->isLecturer() ? 'Dosen' : 'Mahasiswa' }}</span>
                            <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-900">{{ $req->affiliation_label }}</span>
                            @if($req->attendance_status === 'pending')
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">Menunggu</span>
                            @elseif($req->attendance_status === 'hadir')
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Hadir</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">Tidak Hadir</span>
                            @endif
                            <span class="text-xs text-slate-500">Diajukan {{ $req->created_at->diffForHumans() }}</span>
                        </div>
                        <h2 class="truncate text-lg font-bold text-slate-900">{{ $req->name }}</h2>
                        <div class="mt-2 grid grid-cols-1 gap-x-5 gap-y-1.5 text-sm text-slate-600 sm:grid-cols-2 lg:grid-cols-3">
                            <p><span class="text-slate-500">{{ $req->isLecturer() ? 'NIP' : 'NIM' }}:</span> <span class="font-medium text-slate-800">{{ $req->nim_nip }}</span></p>
                            @if($req->study_program)<p><span class="text-slate-500">Prodi:</span> <span class="font-medium text-slate-800">{{ $req->study_program }}</span></p>@endif
                            <p><span class="text-slate-500">Keperluan:</span> <span class="font-medium text-slate-800">{{ $req->purpose_label }}</span></p>
                            <p><span class="text-slate-500">WhatsApp:</span> <span class="font-medium text-slate-800">{{ $req->whatsapp }}</span></p>
                        </div>
                    </div>
                    <div class="shrink-0 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 sm:min-w-40 sm:text-right">
                        <p class="text-sm font-bold text-slate-900">{{ $req->usage_date->locale('id')->isoFormat('D MMM Y') }}</p>
                        <p class="mt-0.5 text-xs font-medium text-amber-900">{{ \App\Models\RefinitivRequest::SESSIONS[$req->session] ?? $req->session }}</p>
                    </div>
                </div>

                <div class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-sm leading-6 text-slate-700">
                    <span class="font-semibold text-slate-600">Variabel:</span> {{ Str::limit($req->variables, 120) }}
                </div>

                <div class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-3 sm:flex-row">
                    <a href="{{ route('admin.refinitiv.show', ['request' => $req, 'status' => $status, 'q' => $search !== '' ? $search : null, 'sort' => $sort]) }}"
                       class="inline-flex min-h-[42px] items-center justify-center gap-2 rounded-lg border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 sm:flex-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Lihat detail
                    </a>

                    @if($req->attendance_status === 'pending')
                        <form action="{{ route('admin.refinitiv.hadir', $req) }}" method="POST" class="sm:flex-1" onsubmit="return window.confirm('Tandai pemohon ini hadir?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="inline-flex min-h-[42px] w-full items-center justify-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-600 focus-visible:ring-offset-2">Tandai hadir</button>
                        </form>
                        <form action="{{ route('admin.refinitiv.tidak-hadir', $req) }}" method="POST" class="sm:flex-1" onsubmit="return window.confirm('Tandai pemohon ini tidak hadir?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="inline-flex min-h-[42px] w-full items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">Tidak hadir</button>
                        </form>
                    @else
                        <form action="{{ route('admin.refinitiv.reset', $req) }}" method="POST" class="sm:flex-1" onsubmit="return window.confirm('Reset status kehadiran pemohon ini ke Menunggu?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="inline-flex min-h-[42px] w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Reset status</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h8m-8 4h8m-8 4h5m-8 5h14a2 2 0 002-2V7l-5-5H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                @if($search !== '')
                    <h2 class="text-base font-semibold text-slate-900">Tidak ada hasil yang cocok</h2>
                    <p class="mt-1 text-sm text-slate-600">Coba periksa ejaan atau gunakan nama, NIM/NIP, nomor WhatsApp, atau ID permohonan.</p>
                    <a href="{{ $clearFiltersUrl }}" class="mt-4 inline-flex min-h-[40px] items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-amber-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-600 focus-visible:ring-offset-2">Hapus pencarian</a>
                @else
                    <h2 class="text-base font-semibold text-slate-900">
                        @if($status === 'pending') Belum ada permohonan menunggu
                        @elseif($status === 'hadir') Belum ada pemohon tercatat hadir
                        @elseif($status === 'tidak_hadir') Belum ada pemohon tercatat tidak hadir
                        @else Belum ada permohonan Refinitiv @endif
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">Permohonan dengan status ini akan tampil di sini.</p>
                @endif
            </div>
        @endforelse
    </section>

    @if($requests->hasPages())
        <div class="mt-5">
            {{ $requests->appends(['status' => $status, 'q' => $search, 'sort' => $sort])->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (() => {
            const select = document.getElementById('refinitiv-sort');
            const trigger = document.querySelector('[data-refinitiv-sort-trigger]');
            const options = document.getElementById('refinitiv-sort-options');
            const form = select?.closest('form');
            const wrapper = select?.closest('.custom-select-wrapper');

            if (!select || !trigger || !options || !form || !wrapper) return;

            const optionItems = [...options.querySelectorAll('[role="option"]')];
            const sortLabel = document.getElementById('refinitiv-sort-value');

            const syncSelection = () => {
                const selected = select.selectedOptions[0];
                sortLabel.textContent = selected?.textContent.trim() ?? '';

                optionItems.forEach((option) => {
                    const isSelected = option.dataset.value === select.value;
                    option.setAttribute('aria-selected', String(isSelected));
                    option.querySelector('svg')?.remove();

                    if (isSelected) {
                        const checkmark = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                        checkmark.setAttribute('class', 'h-4 w-4 text-amber-700');
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
                select.value = option.dataset.value;
                syncSelection();
                closeOptions({ returnFocus: true });
                form.requestSubmit();
            };

            select.classList.add('custom-select-native');
            select.setAttribute('aria-hidden', 'true');
            select.tabIndex = -1;
            trigger.classList.remove('hidden');
            syncSelection();

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
