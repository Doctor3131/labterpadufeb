@extends('layouts.admin')

@section('title', 'Kelola Reservasi Bloomberg - Admin')

@push('styles')
    <style>
        .bloomberg-admin-page .custom-select-trigger:hover { border-color: #a5b4fc; }
        .bloomberg-admin-page .custom-select-trigger:focus-visible,
        .bloomberg-admin-page .custom-select-trigger[aria-expanded='true'] {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
        }
        .bloomberg-admin-page .custom-select-option:hover,
        .bloomberg-admin-page .custom-select-option.is-active,
        .bloomberg-admin-page .custom-select-option[aria-selected='true'] {
            background: #eef2ff;
            color: #3730a3;
        }
        .bloomberg-admin-page .custom-select-option[aria-selected='true'] { font-weight: 700; }
        .bloomberg-admin-page .custom-select-options { z-index: 70; }
        .bloomberg-admin-page [hidden] { display: none !important; }
        .bloomberg-results-region { transition: opacity 180ms ease; }
        .bloomberg-results-region.is-loading { opacity: 0.55; pointer-events: none; }
        .bloomberg-results-region.is-entering { animation: bloombergResultsIn 220ms ease-out; }
        @keyframes bloombergResultsIn { from { opacity: 0.55; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) {
            .bloomberg-results-region, .bloomberg-results-region.is-entering { animation: none; transition: none; }
        }
    </style>
@endpush

@section('content')
    @php
        $filterQuery = array_filter([
            'q' => $search !== '' ? $search : null,
            'applicant_type' => $applicantType !== 'all' ? $applicantType : null,
            'purpose' => $purpose !== 'all' ? $purpose : null,
            'period' => $period !== 'all' ? $period : null,
        ], static fn ($value) => $value !== null);
        $tabUrl = fn (string $tabType) => route('admin.bloomberg.index', ['type' => $tabType] + $filterQuery);
        $resetUrl = route('admin.bloomberg.index', ['type' => $type]);
        $filterControls = [
            [
                'name' => 'applicant_type',
                'id' => 'bloomberg-applicant-filter',
                'label' => 'Jenis pemohon',
                'value' => $applicantType,
                'options' => ['all' => 'Semua pemohon'] + \App\Models\BloombergRequest::APPLICANT_TYPES,
            ],
            [
                'name' => 'purpose',
                'id' => 'bloomberg-purpose-filter',
                'label' => 'Keperluan',
                'value' => $purpose,
                'options' => ['all' => 'Semua keperluan'] + \App\Models\BloombergRequest::PURPOSES,
            ],
            [
                'name' => 'period',
                'id' => 'bloomberg-period-filter',
                'label' => 'Periode jadwal',
                'value' => $period,
                'options' => [
                    'all' => 'Semua jadwal',
                    'upcoming' => 'Hari ini & akan datang',
                    'today' => 'Hari ini',
                    'past' => 'Sudah lewat',
                ],
            ],
        ];
    @endphp
    <div class="bloomberg-admin-page" data-bloomberg-admin>
    <div class="mb-5">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-md text-sm font-medium text-slate-600 transition hover:text-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-1 text-sm font-semibold uppercase tracking-wide text-indigo-700">Layanan data</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reservasi Bloomberg</h1>
            <p class="mt-1 text-sm text-slate-600">Kelola reservasi dan kunjungan Bloomberg Terminal.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.bloomberg.blocked-dates') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                <svg class="h-4 w-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Tanggal blokir
            </a>
            <a href="{{ route('admin.bloomberg.settings') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                <svg class="h-4 w-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-2.756-2.37 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-2.37 2.37-2.37z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pengaturan
            </a>
        </div>
    </header>

    <!-- Tabs + Request List -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200">
            <nav id="bloomberg-type-tabs" class="flex overflow-x-auto" aria-label="Jenis permohonan Bloomberg">
                    <a href="{{ $tabUrl('reservasi') }}" data-bloomberg-type="reservasi"
                   data-active-classes="border-indigo-500 text-indigo-700 bg-indigo-50"
                   data-inactive-classes="border-transparent text-gray-500 hover:text-indigo-600"
                   @if ($type === 'reservasi') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-3 py-3 text-sm font-semibold transition {{ $type === 'reservasi' ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-indigo-600' }}">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span>Reservasi</span>
                    <span class="px-2 py-0.5 bg-indigo-500 text-white rounded-full text-xs font-bold">{{ $counts['reservasi'] }}</span>
                </a>
                    <a href="{{ $tabUrl('walk_in') }}" data-bloomberg-type="walk_in"
                   data-active-classes="border-orange-500 text-orange-700 bg-orange-50"
                   data-inactive-classes="border-transparent text-gray-500 hover:text-orange-600"
                   @if ($type === 'walk_in') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-3 py-3 text-sm font-semibold transition {{ $type === 'walk_in' ? 'border-orange-500 text-orange-700 bg-orange-50' : 'border-transparent text-gray-500 hover:text-orange-600' }}">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span>Kunjungan Langsung</span>
                    <span class="px-2 py-0.5 bg-orange-500 text-white rounded-full text-xs font-bold">{{ $counts['walk_in'] }}</span>
                </a>
                    <a href="{{ $tabUrl('all') }}" data-bloomberg-type="all"
                   data-active-classes="border-gray-500 text-gray-700 bg-gray-50"
                   data-inactive-classes="border-transparent text-gray-500 hover:text-gray-600"
                   @if ($type === 'all') aria-current="page" @endif
                   class="inline-flex min-w-fit flex-1 items-center justify-center gap-2 border-b-2 px-3 py-3 text-sm font-semibold transition {{ $type === 'all' ? 'border-gray-500 text-gray-700 bg-gray-50' : 'border-transparent text-gray-500 hover:text-gray-600' }}">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <span>Semua</span>
                    <span class="px-2 py-0.5 bg-gray-500 text-white rounded-full text-xs font-bold">{{ $counts['all'] }}</span>
                </a>
            </nav>
        </div>

        <div class="border-b border-slate-200 bg-slate-50/60 p-4 md:p-5">
            <form id="bloomberg-filters" action="{{ route('admin.bloomberg.index') }}" method="GET" class="space-y-3">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1.5fr)_minmax(11rem,1fr)_minmax(11rem,1fr)_minmax(10rem,0.9fr)]">
                    <div>
                        <label for="bloomberg-search" class="mb-1.5 block text-sm font-semibold text-slate-700">Cari pemohon</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="m20 20-4-4"/>
                            </svg>
                            <input id="bloomberg-search" type="search" name="q" value="{{ $search }}" maxlength="100" autocomplete="off"
                                   placeholder="Nama, NIM/NIP, HP, variabel, atau ID"
                                   class="min-h-[44px] w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        </div>
                    </div>

                    @foreach ($filterControls as $filter)
                        <div>
                            <label id="{{ $filter['id'] }}-label" for="{{ $filter['id'] }}" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $filter['label'] }}</label>
                            <div class="custom-select-wrapper relative" data-bloomberg-custom-select>
                                <select id="{{ $filter['id'] }}" name="{{ $filter['name'] }}" data-bloomberg-filter-select
                                        class="min-h-[44px] w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                    @foreach ($filter['options'] as $value => $label)
                                        <option value="{{ $value }}" @selected($filter['value'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="custom-select-trigger hidden" data-bloomberg-select-trigger aria-haspopup="listbox" aria-expanded="false"
                                        aria-labelledby="{{ $filter['id'] }}-label {{ $filter['id'] }}-value" aria-controls="{{ $filter['id'] }}-options">
                                    <span id="{{ $filter['id'] }}-value" class="block truncate"></span>
                                    <svg class="custom-select-chevron h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div id="{{ $filter['id'] }}-options" class="custom-select-options hidden" role="listbox" aria-labelledby="{{ $filter['id'] }}-label">
                                    @foreach ($filter['options'] as $value => $label)
                                        <button type="button" class="custom-select-option w-full text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-700"
                                                role="option" data-value="{{ $value }}" aria-selected="{{ $filter['value'] === $value ? 'true' : 'false' }}" tabindex="-1">
                                            <span>{{ $label }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">Filter otomatis</span>
                    <a id="bloomberg-reset-filters" href="{{ $resetUrl }}" data-bloomberg-reset @if (!$hasActiveFilters) hidden @endif
                       class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                        Reset filter
                    </a>
                </div>
                <p id="bloomberg-filter-status" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></p>
                <noscript>
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Perbarui hasil</button>
                </noscript>
            </form>
        </div>

        <p id="bloomberg-filter-error" hidden role="alert" class="mx-4 mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-800 md:mx-6"></p>
        <div id="bloomberg-results-region" class="bloomberg-results-region p-4 md:p-6" aria-busy="false">
            @include('admin.bloomberg.partials.results')
        </div>
    </div>
    </div>
@endsection
