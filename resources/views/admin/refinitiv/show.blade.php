@extends('layouts.admin')

@section('title', 'Detail Permintaan Refinitiv - Admin')

@section('content')
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.refinitiv.index', ['status' => request()->query('status', 'pending'), 'q' => request()->query('q'), 'sort' => request()->query('sort', 'schedule_asc')]) }}" class="mb-4 inline-flex items-center gap-2 rounded-md text-sm font-medium text-slate-600 transition hover:text-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        
        <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-100 p-5 shadow-sm md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="mb-1 text-xl font-bold text-slate-900 md:text-2xl">Detail Permintaan Refinitiv</h1>
                    <p class="text-sm text-slate-600">ID permohonan: #{{ $request->id }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($request->attendance_status === 'pending')
                        <span class="px-4 py-2 bg-yellow-500 text-white rounded-lg font-semibold">Menunggu</span>
                    @elseif($request->attendance_status === 'hadir')
                        <span class="px-4 py-2 bg-green-500 text-white rounded-lg font-semibold">Hadir</span>
                    @else
                        <span class="px-4 py-2 bg-red-500 text-white rounded-lg font-semibold">Tidak Hadir</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Data Pemohon -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Data Pemohon
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Nama</label>
                        <p class="font-semibold text-gray-800">{{ $request->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">{{ $request->isLecturer() ? 'NIP' : 'NIM' }}</label>
                        <p class="font-semibold text-gray-800">{{ $request->nim_nip }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Status</label>
                        <p class="font-semibold text-gray-800">{{ $request->isLecturer() ? 'Dosen' : 'Mahasiswa' }}</p>
                    </div>
                    @if($request->study_program)
                    <div>
                        <label class="text-sm text-gray-500">Program Studi</label>
                        <p class="font-semibold text-gray-800">{{ $request->study_program }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="text-sm text-gray-500">Keterangan</label>
                        <p class="font-semibold text-gray-800">{{ $request->affiliation_label }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">No. WhatsApp</label>
                        <p class="font-semibold text-gray-800">
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $request->whatsapp) }}" 
                               target="_blank" class="text-green-600 hover:text-green-700">
                                {{ $request->whatsapp }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Keperluan & Jadwal -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Keperluan & Jadwal
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Keperluan</label>
                        <p class="font-semibold text-gray-800">{{ $request->purpose_label }}</p>
                    </div>
                    @if($request->lecturer_name)
                    <div>
                        <label class="text-sm text-gray-500">Nama Dosen</label>
                        <p class="font-semibold text-gray-800">{{ $request->lecturer_name }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="text-sm text-gray-500">Tanggal Pemakaian</label>
                        <p class="font-semibold text-gray-800">{{ $request->usage_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Sesi</label>
                        <p class="font-semibold text-gray-800">{{ $request->session_label }}</p>
                    </div>
                </div>
            </div>

            <!-- Variabel -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Variabel yang Dibutuhkan
                </h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $request->variables }}</p>
                </div>
            </div>

            <!-- Dokumen -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dokumen Pemohon
                </h2>
                
                @if($request->ktm_file || $request->statement_file)
                <div class="space-y-4">
                    {{-- KTM (Mahasiswa only) --}}
                    @if($request->ktm_file)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-medium text-gray-700">KTM (Kartu Tanda Mahasiswa)</label>
                            <span class="text-xs text-gray-500">{{ strtoupper(pathinfo($request->ktm_file, PATHINFO_EXTENSION)) }}</span>
                        </div>
                        @php
                            $ktmExt = strtolower(pathinfo($request->ktm_file, PATHINFO_EXTENSION));
                            $isKtmImage = in_array($ktmExt, ['jpg', 'jpeg', 'png']);
                        @endphp
                        @if($isKtmImage)
                        <div class="mb-3 overflow-hidden rounded-lg border bg-slate-50">
                            <button type="button" data-image-preview data-preview-src="{{ route('admin.secure-file', ['path' => $request->ktm_file]) }}" data-preview-title="KTM" aria-label="Perbesar pratinjau KTM" class="block w-full cursor-zoom-in focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500">
                                <img src="{{ route('admin.secure-file', ['path' => $request->ktm_file]) }}" alt="Pratinjau KTM; pilih untuk memperbesar" class="max-h-64 w-full object-contain transition-opacity hover:opacity-90">
                            </button>
                        </div>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('admin.secure-file', ['path' => $request->ktm_file]) }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center rounded-lg bg-amber-100 px-4 py-2 text-sm text-amber-900 transition-colors hover:bg-amber-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Lihat
                            </a>
                            <a href="{{ route('admin.secure-file', ['path' => $request->ktm_file]) }}" download
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                    @elseif($request->isStudent())
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <label class="text-sm font-medium text-gray-700 block mb-2">KTM (Kartu Tanda Mahasiswa)</label>
                        <p class="text-gray-500 text-sm italic">Belum diupload</p>
                    </div>
                    @endif

                    {{-- Surat Pernyataan --}}
                    @if($request->statement_file)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-medium text-gray-700">Surat Pernyataan Kesanggupan</label>
                            <span class="text-xs text-gray-500">{{ strtoupper(pathinfo($request->statement_file, PATHINFO_EXTENSION)) }}</span>
                        </div>
                        @php
                            $statementExt = strtolower(pathinfo($request->statement_file, PATHINFO_EXTENSION));
                            $isStatementImage = in_array($statementExt, ['jpg', 'jpeg', 'png']);
                        @endphp
                        @if($isStatementImage)
                        <div class="mb-3 overflow-hidden rounded-lg border bg-slate-50">
                            <button type="button" data-image-preview data-preview-src="{{ route('admin.secure-file', ['path' => $request->statement_file]) }}" data-preview-title="Surat Pernyataan" aria-label="Perbesar pratinjau surat pernyataan" class="block w-full cursor-zoom-in focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500">
                                <img src="{{ route('admin.secure-file', ['path' => $request->statement_file]) }}" alt="Pratinjau surat pernyataan; pilih untuk memperbesar" class="max-h-64 w-full object-contain transition-opacity hover:opacity-90">
                            </button>
                        </div>
                        @endif
                        <div class="flex gap-2">
                            <a href="{{ route('admin.secure-file', ['path' => $request->statement_file]) }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center rounded-lg bg-amber-100 px-4 py-2 text-sm text-amber-900 transition-colors hover:bg-amber-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Lihat
                            </a>
                            <a href="{{ route('admin.secure-file', ['path' => $request->statement_file]) }}" download
                               class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Surat Pernyataan Kesanggupan</label>
                        <p class="text-gray-500 text-sm italic">Belum diupload</p>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-6 bg-gray-50 rounded-lg">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500">Tidak ada dokumen yang diupload</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Action Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi</h2>
                
                @if($request->attendance_status === 'pending')
                    <div class="space-y-3">
                        <form action="{{ route('admin.refinitiv.hadir', $request) }}" method="POST" onsubmit="return window.confirm('Tandai pemohon ini hadir?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Tandai Hadir
                            </button>
                        </form>
                        <form action="{{ route('admin.refinitiv.tidak-hadir', $request) }}" method="POST" onsubmit="return window.confirm('Tandai pemohon ini tidak hadir?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Tandai Tidak Hadir
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600">
                            Status saat ini: 
                            <span class="font-bold {{ $request->attendance_status === 'hadir' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $request->attendance_status_label }}
                            </span>
                        </p>
                        @if($request->handler)
                            <p class="text-xs text-gray-500 mt-1">
                                Oleh: {{ $request->handler->name }}<br>
                                {{ $request->attendance_marked_at->locale('id')->isoFormat('D MMM Y HH:mm') }}
                            </p>
                        @endif
                    </div>
                    <form action="{{ route('admin.refinitiv.reset', $request) }}" method="POST" onsubmit="return window.confirm('Reset status kehadiran pemohon ini ke Menunggu?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="w-full px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Status
                        </button>
                    </form>
                @endif
            </div>

            <!-- Info Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <label class="text-gray-500">Dibuat</label>
                        <p class="text-gray-800">{{ $request->created_at->locale('id')->isoFormat('D MMMM Y HH:mm') }}</p>
                    </div>
                    <div>
                        <label class="text-gray-500">Terakhir diupdate</label>
                        <p class="text-gray-800">{{ $request->updated_at->locale('id')->isoFormat('D MMMM Y HH:mm') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/85 p-4" aria-hidden="true">
        <div class="relative max-h-[90vh] max-w-5xl rounded-xl bg-slate-900 p-3 shadow-2xl sm:p-5" role="dialog" aria-modal="true" aria-labelledby="modalTitle" tabindex="-1">
            <button id="closeImageModal" type="button" aria-label="Tutup pratinjau gambar" class="absolute -right-2 -top-12 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img id="modalImage" src="" alt="" class="max-h-[78vh] max-w-full rounded-lg object-contain">
            <p id="modalTitle" class="mt-3 text-center text-sm font-medium text-white"></p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const modal = document.getElementById('imageModal');
        const dialog = modal?.querySelector('[role="dialog"]');
        const closeButton = document.getElementById('closeImageModal');
        const image = document.getElementById('modalImage');
        const title = document.getElementById('modalTitle');
        let activeTrigger = null;
        let previousBodyOverflow = '';

        if (!modal || !dialog || !closeButton || !image || !title) return;

        const close = () => {
            if (modal.classList.contains('hidden')) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            image.removeAttribute('src');
            document.body.style.overflow = previousBodyOverflow;
            activeTrigger?.focus();
            activeTrigger = null;
        };

        document.querySelectorAll('[data-image-preview]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                activeTrigger = trigger;
                previousBodyOverflow = document.body.style.overflow;
                image.src = trigger.dataset.previewSrc;
                image.alt = `Pratinjau ${trigger.dataset.previewTitle}`;
                title.textContent = trigger.dataset.previewTitle;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                closeButton.focus();
            });
        });

        closeButton.addEventListener('click', close);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });

        document.addEventListener('keydown', (event) => {
            if (modal.classList.contains('hidden')) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                close();
                return;
            }

            if (event.key === 'Tab') {
                event.preventDefault();
                closeButton.focus();
            }
        });
    })();
</script>
@endpush
