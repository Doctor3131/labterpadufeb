@php
    $navigationCurrent = $current ?? 'Manajemen Jadwal';
    $navigationBackUrl = $backUrl ?? route('admin.dashboard');
    $navigationBackLabel = $backLabel ?? 'Kembali ke Dashboard';
    $navigationIncludeSchedule = $includeSchedule ?? false;
@endphp

<div class="mb-4 md:mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <nav aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5 text-sm">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-1.5 rounded-md px-1 py-1 text-gray-500 transition-colors hover:bg-yellow-50 hover:text-yellow-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 10 9-7 9 7v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 21v-6h6v6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="text-gray-300" aria-hidden="true">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                </svg>
            </li>
            @if($navigationIncludeSchedule)
                <li>
                    <a href="{{ route('admin.schedules.index') }}"
                       class="rounded-md px-1 py-1 text-gray-500 transition-colors hover:bg-yellow-50 hover:text-yellow-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">
                        Manajemen Jadwal
                    </a>
                </li>
                <li class="text-gray-300" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                    </svg>
                </li>
            @endif
            <li class="font-semibold text-gray-800" aria-current="page">{{ $navigationCurrent }}</li>
        </ol>
    </nav>

    <a href="{{ $navigationBackUrl }}"
       class="inline-flex w-fit items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-600 shadow-sm transition-colors hover:border-yellow-300 hover:bg-yellow-50 hover:text-yellow-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        <span>{{ $navigationBackLabel }}</span>
    </a>
</div>
