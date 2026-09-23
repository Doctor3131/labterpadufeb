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

    <div class="refinitiv-page" data-refinitiv-admin>
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
                <span id="refinitiv-total-count">{{ $requests->total() }}</span> permohonan
            </div>
        </header>

        <section class="relative z-10 mb-5 rounded-xl border border-slate-200 bg-white shadow-sm" aria-label="Filter permohonan Refinitiv">
            <nav id="refinitiv-status-tabs" class="flex overflow-x-auto border-b border-slate-200" aria-label="Filter status kehadiran">
                <a href="{{ $tabLink('all') }}" data-refinitiv-status="all" data-active-classes="border-blue-600 bg-blue-50 text-blue-900" data-inactive-classes="border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900" @if($status === 'all') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500 {{ $status === 'all' ? 'border-blue-600 bg-blue-50 text-blue-900' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Semua <span data-refinitiv-tab-count data-active-classes="bg-blue-100 text-blue-800" data-inactive-classes="bg-slate-100 text-slate-600" class="rounded-full px-2 py-0.5 text-xs {{ $status === 'all' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ $tabLink('pending') }}" data-refinitiv-status="pending" data-active-classes="border-amber-500 bg-amber-50 text-amber-900" data-inactive-classes="border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900" @if($status === 'pending') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500 {{ $status === 'pending' ? 'border-amber-500 bg-amber-50 text-amber-900' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Menunggu <span data-refinitiv-tab-count data-active-classes="bg-amber-100 text-amber-900" data-inactive-classes="bg-slate-100 text-slate-600" class="rounded-full px-2 py-0.5 text-xs {{ $status === 'pending' ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-600' }}">{{ $counts['pending'] }}</span>
                </a>
                <a href="{{ $tabLink('hadir') }}" data-refinitiv-status="hadir" data-active-classes="border-green-600 bg-green-50 text-green-800" data-inactive-classes="border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900" @if($status === 'hadir') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-green-600 {{ $status === 'hadir' ? 'border-green-600 bg-green-50 text-green-800' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Hadir <span data-refinitiv-tab-count data-active-classes="bg-green-100 text-green-800" data-inactive-classes="bg-slate-100 text-slate-600" class="rounded-full px-2 py-0.5 text-xs {{ $status === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['hadir'] }}</span>
                </a>
                <a href="{{ $tabLink('tidak_hadir') }}" data-refinitiv-status="tidak_hadir" data-active-classes="border-red-600 bg-red-50 text-red-800" data-inactive-classes="border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900" @if($status === 'tidak_hadir') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition first:rounded-tl-xl last:rounded-tr-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-red-500 {{ $status === 'tidak_hadir' ? 'border-red-600 bg-red-50 text-red-800' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    Tidak Hadir <span data-refinitiv-tab-count data-active-classes="bg-red-100 text-red-800" data-inactive-classes="bg-slate-100 text-slate-600" class="rounded-full px-2 py-0.5 text-xs {{ $status === 'tidak_hadir' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600' }}">{{ $counts['tidak_hadir'] }}</span>
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
                               maxlength="100" autocomplete="off" aria-describedby="refinitiv-search-help">
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
                    <a id="refinitiv-reset" data-refinitiv-reset-link href="{{ $clearFiltersUrl }}" @if($search === '' && $sort === 'schedule_asc') hidden @endif
                       class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                        Reset
                    </a>
                </div>
                <p id="refinitiv-search-help" class="sr-only">Hasil pencarian diperbarui otomatis setelah Anda berhenti mengetik.</p>
                <p id="refinitiv-filter-status" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></p>
                <noscript>
                    <div class="text-sm text-slate-600">
                        <p>JavaScript tidak aktif. Perbarui hasil setelah mengubah pencarian atau urutan.</p>
                        <button type="submit" class="mt-2 inline-flex min-h-[40px] items-center justify-center rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white">Perbarui filter</button>
                    </div>
                </noscript>
            </form>
        </section>

        <div id="refinitiv-results-region" class="refinitiv-results-region" aria-busy="false">
            @include('admin.refinitiv.partials.results')
        </div>
    </div>
@endsection
