@extends('layouts.admin')

@section('title', 'Detail Permintaan Refinitiv - Admin')

@push('styles')
    <style>
        @@view-transition { navigation: auto; }
    </style>
@endpush

@section('content')
    @php
        $statusStyles = [
            'pending' => 'bg-amber-100 text-amber-900',
            'hadir' => 'bg-green-100 text-green-800',
            'tidak_hadir' => 'bg-red-100 text-red-800',
        ];
        $backUrl = route('admin.refinitiv.index', [
            'status' => request()->query('status', 'pending'),
            'q' => request()->query('q'),
            'sort' => request()->query('sort', 'schedule_asc'),
        ]);
        $documents = [];

        if ($request->ktm_file || $request->isStudent()) {
            $documents[] = [
                'title' => 'KTM (Kartu Tanda Mahasiswa)',
                'path' => $request->ktm_file,
            ];
        }

        $documents[] = [
            'title' => 'Surat Pernyataan Kesanggupan',
            'path' => $request->statement_file,
        ];
    @endphp

    <div class="refinitiv-page refinitiv-detail-page">
        <div class="mb-5">
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 rounded-md text-sm font-medium text-slate-600 transition hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke permintaan Refinitiv
            </a>
        </div>

        <header class="mb-5 flex flex-col gap-4 rounded-xl border border-blue-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Layanan data · Refinitiv</p>
                <div class="mt-1 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Detail permohonan</h1>
                    <span class="text-sm font-medium text-slate-500">#{{ $request->id }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-600">{{ $request->name }} <span aria-hidden="true">·</span> diajukan {{ $request->created_at->locale('id')->diffForHumans() }}</p>
            </div>

            <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusStyles[$request->attendance_status] ?? 'bg-slate-100 text-slate-700' }}">
                <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                {{ $request->attendance_status_label }}
            </span>
        </header>

        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1fr)_18rem] xl:grid-cols-[minmax(0,1fr)_20rem]">
            <main class="space-y-4">
                <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="refinitiv-applicant-heading">
                    <div class="mb-4 flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <div>
                            <h2 id="refinitiv-applicant-heading" class="text-base font-semibold text-slate-900">Data pemohon</h2>
                            <p class="text-xs text-slate-500">Identitas dan kontak pemohon</p>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Nama</dt>
                            <dd class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $request->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">{{ $request->isLecturer() ? 'NIP' : 'NIM' }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $request->nim_nip }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Jenis pemohon</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $request->isLecturer() ? 'Dosen' : 'Mahasiswa' }}</dd>
                        </div>
                        @if($request->study_program)
                            <div>
                                <dt class="text-xs font-medium text-slate-500">Program studi</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $request->study_program }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Afiliasi</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $request->affiliation_label }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">WhatsApp</dt>
                            <dd class="mt-1 text-sm font-semibold">
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $request->whatsapp) }}" target="_blank" rel="noopener noreferrer" class="rounded text-blue-700 underline decoration-blue-200 underline-offset-2 transition hover:text-blue-900 hover:decoration-blue-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" aria-label="Hubungi {{ $request->name }} melalui WhatsApp">
                                    {{ $request->whatsapp }}
                                </a>
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="refinitiv-schedule-heading">
                    <div class="mb-4 flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <div>
                            <h2 id="refinitiv-schedule-heading" class="text-base font-semibold text-slate-900">Keperluan dan jadwal</h2>
                            <p class="text-xs text-slate-500">Informasi penggunaan layanan</p>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Keperluan</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $request->purpose_label }}</dd>
                        </div>
                        @if($request->lecturer_name)
                            <div>
                                <dt class="text-xs font-medium text-slate-500">Nama dosen</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $request->lecturer_name }}</dd>
                            </div>
                        @endif
                        <div class="rounded-lg border border-blue-100 bg-blue-50/70 p-3 sm:col-span-2">
                            <dt class="text-xs font-semibold text-blue-800">Tanggal pemakaian</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-900">{{ $request->usage_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</dd>
                            <dd class="mt-1 text-sm text-slate-700">{{ $request->session_label }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Variabel yang dibutuhkan</h3>
                        <p class="mt-2 whitespace-pre-wrap break-words rounded-lg bg-slate-50 p-3 text-sm leading-6 text-slate-700">{{ $request->variables }}</p>
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="refinitiv-documents-heading">
                    <div class="mb-4 flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9v10a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div>
                            <h2 id="refinitiv-documents-heading" class="text-base font-semibold text-slate-900">Dokumen pemohon</h2>
                            <p class="text-xs text-slate-500">Pratinjau tersedia untuk gambar dan PDF</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                        @foreach($documents as $document)
                            @php
                                $documentPath = $document['path'];
                                $documentExtension = $documentPath ? strtolower(pathinfo($documentPath, PATHINFO_EXTENSION)) : '';
                                $isImageDocument = in_array($documentExtension, ['jpg', 'jpeg', 'png']);
                                $isPdfDocument = $documentExtension === 'pdf';
                                $documentUrl = $documentPath ? route('admin.secure-file', ['path' => $documentPath]) : null;
                            @endphp

                            <article class="rounded-lg border border-slate-200 p-3 sm:p-4">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $document['title'] }}</h3>
                                        @if($documentPath)
                                            <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $documentExtension ?: 'File' }}</p>
                                        @endif
                                    </div>
                                    @if($documentPath)
                                        <span class="shrink-0 rounded-md bg-blue-50 px-2 py-1 text-[11px] font-semibold uppercase text-blue-800">{{ $documentExtension ?: 'File' }}</span>
                                    @endif
                                </div>

                                @if($documentPath)
                                    @if($isImageDocument)
                                        <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" data-file-preview data-preview-type="image" data-preview-title="{{ $document['title'] }}" class="group relative block overflow-hidden rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" aria-label="Pratinjau {{ $document['title'] }}">
                                            <img src="{{ $documentUrl }}" alt="Pratinjau {{ $document['title'] }}" loading="lazy" class="h-36 w-full object-contain transition duration-200 group-hover:scale-[1.02] sm:h-40">
                                            <span class="absolute inset-x-0 bottom-0 bg-slate-950/65 px-3 py-2 text-center text-xs font-medium text-white">Pilih gambar untuk memperbesar</span>
                                        </a>
                                    @elseif($isPdfDocument)
                                        <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" data-file-preview data-preview-type="pdf" data-preview-title="{{ $document['title'] }}" class="flex min-h-36 items-center gap-3 rounded-lg border border-blue-100 bg-blue-50/60 p-4 text-left transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:min-h-40">
                                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-blue-700 shadow-sm" aria-hidden="true">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9v10a2 2 0 01-2 2z"/></svg>
                                            </span>
                                            <span>
                                                <span class="block text-sm font-semibold text-slate-900">Pratinjau PDF</span>
                                                <span class="mt-1 block text-xs text-slate-600">Buka dokumen tanpa meninggalkan halaman</span>
                                            </span>
                                        </a>
                                    @else
                                        <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="flex min-h-36 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:min-h-40">Buka dokumen</a>
                                    @endif

                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <span class="text-xs text-slate-500">Dokumen terlampir</span>
                                        <a href="{{ $documentUrl }}" download class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Unduh
                                        </a>
                                    </div>
                                @else
                                    <div class="flex min-h-36 items-center gap-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 sm:min-h-40">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-slate-400" aria-hidden="true">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9v10a2 2 0 01-2 2z"/></svg>
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium text-slate-700">Belum diunggah</p>
                                            <p class="mt-0.5 text-xs text-slate-500">Dokumen ini tidak disertakan pada permohonan.</p>
                                        </div>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            </main>

            <aside class="order-first space-y-4 lg:sticky lg:top-24 lg:order-none" aria-label="Tindakan dan riwayat permohonan">
                <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="refinitiv-attendance-heading">
                    <div class="mb-4">
                        <h2 id="refinitiv-attendance-heading" class="text-base font-semibold text-slate-900">Kehadiran</h2>
                        <p class="mt-1 text-sm text-slate-600">Catat hasil sesi Refinitiv ini.</p>
                    </div>

                    @if($request->attendance_status === 'pending')
                        <div class="mb-4 flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
                            <span class="h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
                            Menunggu pencatatan
                        </div>
                        <div class="space-y-2.5">
                            <form action="{{ route('admin.refinitiv.hadir', $request) }}" method="POST" onsubmit="return window.confirm('Tandai pemohon ini hadir?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 4 4L19 6"/></svg>
                                    Tandai hadir
                                </button>
                            </form>
                            <form action="{{ route('admin.refinitiv.tidak-hadir', $request) }}" method="POST" onsubmit="return window.confirm('Tandai pemohon ini tidak hadir?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:-translate-y-0.5 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg>
                                    Tandai tidak hadir
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-medium text-slate-500">Status kehadiran</p>
                            <p class="mt-1 text-sm font-semibold {{ $request->attendance_status === 'hadir' ? 'text-green-700' : 'text-red-700' }}">{{ $request->attendance_status_label }}</p>
                            @if($request->handler || $request->attendance_marked_at)
                                <p class="mt-2 border-t border-slate-200 pt-2 text-xs leading-5 text-slate-600">
                                    @if($request->handler)
                                        Dicatat oleh <span class="font-medium text-slate-800">{{ $request->handler->name }}</span>
                                    @endif
                                    @if($request->attendance_marked_at)
                                        <span class="block">{{ $request->attendance_marked_at->locale('id')->isoFormat('D MMM Y, HH:mm') }} WIB</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                        <form action="{{ route('admin.refinitiv.reset', $request) }}" method="POST" onsubmit="return window.confirm('Reset status kehadiran pemohon ini ke Menunggu?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Kembalikan ke menunggu
                            </button>
                        </form>
                    @endif
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="refinitiv-history-heading">
                    <h2 id="refinitiv-history-heading" class="text-sm font-semibold text-slate-900">Riwayat permohonan</h2>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Diajukan</dt>
                            <dd class="mt-0.5 text-slate-800" title="{{ $request->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB">{{ $request->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }} WIB</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Terakhir diperbarui</dt>
                            <dd class="mt-0.5 text-slate-800" title="{{ $request->updated_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB">{{ $request->updated_at->locale('id')->isoFormat('D MMM Y, HH:mm') }} WIB</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>

        <div id="refinitiv-file-modal" class="refinitiv-file-modal fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/80 p-3 sm:p-6" aria-hidden="true">
            <section class="refinitiv-preview-panel flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="refinitiv-preview-title" tabindex="-1">
                <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-5">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-blue-700">Pratinjau dokumen</p>
                        <h2 id="refinitiv-preview-title" class="truncate text-sm font-semibold text-slate-900 sm:text-base"></h2>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <a id="refinitiv-preview-new-tab" href="#" target="_blank" rel="noopener noreferrer" class="hidden rounded-lg px-2 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:inline-flex">Buka tab baru</a>
                        <button id="refinitiv-preview-close" type="button" aria-label="Tutup pratinjau dokumen" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </header>
                <div class="flex min-h-0 flex-1 items-center justify-center overflow-auto bg-slate-100 p-2 sm:p-4">
                    <img id="refinitiv-preview-image" src="" alt="" class="hidden max-h-[78vh] max-w-full rounded-md object-contain shadow-sm">
                    <iframe id="refinitiv-preview-pdf" title="Pratinjau dokumen PDF Refinitiv" src="" class="hidden h-[78vh] w-full rounded-md border border-slate-300 bg-white" loading="lazy"></iframe>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const modal = document.getElementById('refinitiv-file-modal');
            const dialog = modal?.querySelector('[role="dialog"]');
            const closeButton = document.getElementById('refinitiv-preview-close');
            const image = document.getElementById('refinitiv-preview-image');
            const pdf = document.getElementById('refinitiv-preview-pdf');
            const title = document.getElementById('refinitiv-preview-title');
            const openInNewTab = document.getElementById('refinitiv-preview-new-tab');
            let activeTrigger = null;
            let previousBodyOverflow = '';
            let closeTimer;

            if (!modal || !dialog || !closeButton || !image || !pdf || !title || !openInNewTab) return;

            const focusableElements = () => [...dialog.querySelectorAll('button:not([disabled]), a[href]:not([href="#"]), iframe:not([tabindex="-1"])')]
                .filter((element) => {
                    const style = window.getComputedStyle(element);
                    return style.display !== 'none' && style.visibility !== 'hidden';
                });

            const close = () => {
                if (modal.classList.contains('hidden') || modal.classList.contains('is-closing')) return;

                modal.classList.remove('is-open');
                modal.classList.add('is-closing');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = previousBodyOverflow;
                activeTrigger?.focus();

                closeTimer = window.setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex', 'is-closing');
                    image.removeAttribute('src');
                    pdf.removeAttribute('src');
                    openInNewTab.href = '#';
                    activeTrigger = null;
                }, 180);
            };

            document.querySelectorAll('[data-file-preview]').forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    window.clearTimeout(closeTimer);
                    activeTrigger = trigger;
                    previousBodyOverflow = document.body.style.overflow;
                    title.textContent = trigger.dataset.previewTitle || 'Dokumen Refinitiv';
                    openInNewTab.href = trigger.href;
                    image.classList.add('hidden');
                    pdf.classList.add('hidden');
                    image.removeAttribute('src');
                    pdf.removeAttribute('src');

                    if (trigger.dataset.previewType === 'pdf') {
                        pdf.src = trigger.href;
                        pdf.classList.remove('hidden');
                    } else {
                        image.src = trigger.href;
                        image.alt = `Pratinjau ${title.textContent}`;
                        image.classList.remove('hidden');
                    }

                    modal.classList.remove('hidden', 'is-closing');
                    modal.classList.add('flex');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    window.requestAnimationFrame(() => modal.classList.add('is-open'));
                    closeButton.focus();
                });
            });

            closeButton.addEventListener('click', close);
            modal.addEventListener('click', (event) => {
                if (event.target === modal) close();
            });

            document.addEventListener('focusin', (event) => {
                if (!modal.classList.contains('hidden') && !modal.classList.contains('is-closing') && !dialog.contains(event.target)) {
                    closeButton.focus();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (modal.classList.contains('hidden') || modal.classList.contains('is-closing')) return;

                if (event.key === 'Escape') {
                    event.preventDefault();
                    close();
                    return;
                }

                if (event.key === 'Tab') {
                    const focusable = focusableElements();
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];

                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last?.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first?.focus();
                    }
                }
            });
        })();
    </script>
@endpush
