@extends('layouts.admin')

@section('title', 'Detail Peminjaman Laboratorium')

@push('styles')
<style>
    .booking-detail-page { max-width: 1120px; margin: 0 auto; }
    .booking-card { border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; box-shadow: 0 1px 2px rgb(15 23 42 / .04); }
    .booking-label { color: #64748b; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .booking-action { display: inline-flex; align-items: center; justify-content: center; border-radius: .625rem; padding: .6rem .9rem; font-size: .875rem; font-weight: 700; transition: background-color .15s, border-color .15s, color .15s; }
    .booking-action:focus-visible { outline: 3px solid #fde68a; outline-offset: 2px; }
    .booking-modal[aria-hidden="true"] { display: none; }
    .booking-modal[aria-hidden="false"] { display: flex; }
</style>
@endpush

@section('content')
@php
    $statusMeta = [
        'pending' => ['label' => 'Menunggu keputusan', 'class' => 'border-amber-200 bg-amber-50 text-amber-900'],
        'approved' => ['label' => 'Disetujui', 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-800'],
        'rejected' => ['label' => 'Ditolak', 'class' => 'border-red-200 bg-red-50 text-red-800'],
    ][$booking->status] ?? ['label' => ucfirst($booking->status), 'class' => 'border-slate-200 bg-slate-50 text-slate-800'];
    $typeLabels = [
        'perkuliahan_tetap' => 'Perkuliahan tetap',
        'perkuliahan_tidak_tetap' => 'Perkuliahan tidak tetap',
        'non_perkuliahan' => 'Non-perkuliahan',
        'pribadi' => 'Pribadi',
    ];
    $title = $booking->booking_type === 'non_perkuliahan' ? $booking->activity_name : $booking->course_name;
    $recurrenceDays = $booking->recurrence_days ?: ($booking->day ? [$booking->day] : []);
    $documentUrl = $booking->document_path ? route('admin.secure-file', ['path' => $booking->document_path]) : null;
@endphp

<main class="booking-detail-page px-4 py-6 md:px-6 md:py-8">
    <a href="{{ route('admin.lab.bookings', ['status' => $booking->status]) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-yellow-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        Kembali ke peminjaman laboratorium
    </a>

    <header class="mt-5 border-b border-slate-200 pb-6 md:flex md:items-start md:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-xs font-bold uppercase tracking-[.16em] text-yellow-700">Pengajuan #{{ $booking->id }}</p>
                <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">{{ $title ?: 'Detail peminjaman' }}</h1>
            <p class="mt-2 text-sm text-slate-600">Diajukan {{ $booking->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB oleh {{ $booking->pic_name }}.</p>
        </div>
        @if($booking->status === 'pending')
            <div class="mt-5 flex flex-wrap gap-2 md:mt-0">
                <button type="button" class="booking-action border border-red-200 bg-white text-red-700 hover:bg-red-50" data-open-reject>Tolak</button>
                <button type="button" class="booking-action bg-yellow-500 text-white hover:bg-yellow-600" data-open-approve>Setujui</button>
            </div>
        @endif
    </header>

    @if(session('success'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">{{ session('error') }}</div>
    @endif
    @if($booking->status === 'rejected' && $booking->rejection_reason)
        <section class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-4" aria-labelledby="rejection-heading">
            <p id="rejection-heading" class="text-sm font-bold text-red-900">Alasan penolakan</p>
            <p class="mt-1 text-sm leading-6 text-red-800">{{ $booking->rejection_reason }}</p>
        </section>
    @endif

    <div class="mt-6 grid gap-5 lg:grid-cols-3">
        <section class="booking-card p-5 lg:col-span-2" aria-labelledby="schedule-heading">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.14em] text-yellow-700">Ringkasan jadwal</p>
                    <h2 id="schedule-heading" class="mt-1 text-lg font-bold text-slate-900">Waktu dan laboratorium</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $typeLabels[$booking->booking_type] ?? $booking->booking_type }}</span>
            </div>
            @if($booking->booking_type === 'pribadi')
                <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-700">Pengajuan pribadi tidak menggunakan alokasi jadwal laboratorium.</div>
            @else
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="booking-label">Tanggal mulai</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $booking->booking_date?->locale('id')->isoFormat('dddd, D MMMM Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="booking-label">Waktu</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }} WIB</dd>
                    </div>
                    <div>
                        <dt class="booking-label">Laboratorium</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $booking->lab?->name ?? 'Laboratorium tidak tersedia' }}</dd>
                    </div>
                    <div>
                        <dt class="booking-label">Peserta</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $booking->participant_count }} orang</dd>
                    </div>
                </dl>
                @if($booking->is_recurring)
                    <div class="mt-5 rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                        <p class="text-sm font-bold text-yellow-900">Jadwal berulang</p>
                        <p class="mt-1 text-sm leading-6 text-yellow-800">
                            Setiap minggu pada {{ implode(', ', $recurrenceDays) ?: 'hari yang dipilih' }}
                            @if($booking->end_date) hingga {{ $booking->end_date->locale('id')->isoFormat('D MMMM Y') }}@endif.
                        </p>
                    </div>
                @endif
            @endif
        </section>

        <aside class="booking-card p-5" aria-labelledby="applicant-heading">
            <p class="text-xs font-bold uppercase tracking-[.14em] text-yellow-700">Pengaju</p>
            <h2 id="applicant-heading" class="mt-1 text-lg font-bold text-slate-900">Kontak peminjam</h2>
            <div class="mt-5 flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-yellow-100 font-bold text-yellow-900">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($booking->pic_name, 0, 1)) }}</div>
                <div class="min-w-0">
                    <p class="truncate font-bold text-slate-900">{{ $booking->pic_name }}</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $booking->nim ?: ($booking->nip ?: 'Identitas tidak diisi') }}</p>
                </div>
            </div>
            <dl class="mt-5 space-y-4 text-sm">
                @if($booking->study_program)
                    <div><dt class="booking-label">Program studi</dt><dd class="mt-1 text-slate-800">{{ $booking->study_program }}</dd></div>
                @endif
                <div><dt class="booking-label">Nomor telepon</dt><dd class="mt-1 text-slate-800">{{ $booking->phone_number ?: '—' }}</dd></div>
                @if($booking->applicant_status)
                    <div><dt class="booking-label">Status pengaju</dt><dd class="mt-1 text-slate-800">{{ $booking->applicant_status }}{{ $booking->custom_status ? ' — '.$booking->custom_status : '' }}</dd></div>
                @endif
            </dl>
        </aside>

        <section class="booking-card p-5 lg:col-span-2" aria-labelledby="activity-heading">
            <p class="text-xs font-bold uppercase tracking-[.14em] text-yellow-700">Kegiatan</p>
            <h2 id="activity-heading" class="mt-1 text-lg font-bold text-slate-900">Rincian peminjaman</h2>
            <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                @if($booking->booking_type === 'non_perkuliahan')
                    <div><dt class="booking-label">Nama kegiatan</dt><dd class="mt-1 font-semibold text-slate-900">{{ $booking->activity_name ?: '—' }}</dd></div>
                    @if($booking->isBimbinganDosen())
                        <div><dt class="booking-label">Kategori</dt><dd class="mt-1 text-slate-800">Bimbingan dosen</dd></div>
                        <div><dt class="booking-label">Dosen pembimbing</dt><dd class="mt-1 text-slate-800">{{ $booking->lecturer_name ?: '—' }}</dd></div>
                        <div><dt class="booking-label">NIP dosen</dt><dd class="mt-1 text-slate-800">{{ $booking->lecturer_nip ?: '—' }}</dd></div>
                    @else
                        <div><dt class="booking-label">Jenis kegiatan</dt><dd class="mt-1 text-slate-800">{{ $booking->activity_type ?: '—' }}</dd></div>
                        @if($booking->position)<div><dt class="booking-label">Posisi pengaju</dt><dd class="mt-1 text-slate-800">{{ $booking->position }}</dd></div>@endif
                    @endif
                    @if($booking->equipment_needs)<div class="sm:col-span-2"><dt class="booking-label">Kebutuhan peralatan</dt><dd class="mt-1 rounded-lg bg-slate-50 px-3 py-2 text-sm leading-6 text-slate-800">{{ $booking->equipment_needs }}</dd></div>@endif
                @elseif($booking->booking_type === 'pribadi')
                    <div><dt class="booking-label">Kategori</dt><dd class="mt-1 font-semibold text-slate-900">{{ $booking->pribadi_sub_type === 'mahasiswa' ? 'Mahasiswa' : 'Non-mahasiswa' }}</dd></div>
                    @if($booking->purpose)<div><dt class="booking-label">Keperluan</dt><dd class="mt-1 text-slate-800">{{ $booking->purpose }}</dd></div>@endif
                    @if($booking->class_year)<div><dt class="booking-label">Tahun angkatan</dt><dd class="mt-1 text-slate-800">{{ $booking->class_year }}</dd></div>@endif
                @else
                    <div class="sm:col-span-2"><dt class="booking-label">Mata kuliah</dt><dd class="mt-1 font-semibold text-slate-900">{{ $booking->course_name ?: '—' }}</dd></div>
                    <div><dt class="booking-label">Dosen pengampu</dt><dd class="mt-1 text-slate-800">{{ $booking->lecturer_name ?: '—' }}</dd></div>
                    <div><dt class="booking-label">NIP dosen</dt><dd class="mt-1 text-slate-800">{{ $booking->lecturer_nip ?: '—' }}</dd></div>
                    @if($booking->software_needs)<div class="sm:col-span-2"><dt class="booking-label">Software yang digunakan</dt><dd class="mt-1 rounded-lg bg-slate-50 px-3 py-2 text-sm leading-6 text-slate-800">{{ $booking->software_needs }}</dd></div>@endif
                @endif
            </dl>
        </section>

        <aside class="booking-card p-5" aria-labelledby="process-heading">
            <p class="text-xs font-bold uppercase tracking-[.14em] text-yellow-700">Proses</p>
            <h2 id="process-heading" class="mt-1 text-lg font-bold text-slate-900">Riwayat keputusan</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div><dt class="booking-label">Status</dt><dd class="mt-1 font-semibold text-slate-800">{{ $statusMeta['label'] }}</dd></div>
                <div><dt class="booking-label">Ditangani oleh</dt><dd class="mt-1 text-slate-800">{{ $booking->handler?->name ?: 'Belum diproses' }}</dd></div>
                @if($booking->handled_at)<div><dt class="booking-label">Waktu keputusan</dt><dd class="mt-1 text-slate-800">{{ $booking->handled_at->locale('id')->isoFormat('D MMM Y, HH:mm') }} WIB</dd></div>@endif
                @if($booking->admin_notes)<div><dt class="booking-label">Catatan admin</dt><dd class="mt-1 rounded-lg bg-slate-50 px-3 py-2 leading-6 text-slate-800">{{ $booking->admin_notes }}</dd></div>@endif
            </dl>
        </aside>

        @if($documentUrl)
            <section class="booking-card overflow-hidden lg:col-span-3" aria-labelledby="document-heading">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[.14em] text-yellow-700">Dokumen pendukung</p>
                        <h2 id="document-heading" class="mt-1 text-lg font-bold text-slate-900">Pratinjau dokumen PDF</h2>
                    </div>
                    <a href="{{ $documentUrl }}" target="_blank" rel="noopener" class="booking-action border border-slate-300 bg-white text-slate-700 hover:border-yellow-400 hover:text-yellow-800">Buka di tab baru</a>
                </div>
                <div class="bg-slate-100 p-3 md:p-5">
                    <iframe src="{{ $documentUrl }}" title="Pratinjau dokumen pendukung peminjaman #{{ $booking->id }}" class="h-[32rem] w-full rounded-lg border border-slate-300 bg-white" loading="lazy">
                        Dokumen tidak dapat ditampilkan di peramban ini. Gunakan tautan “Buka di tab baru”.
                    </iframe>
                </div>
            </section>
        @endif
    </div>
</main>

@if($booking->status === 'pending')
    <div id="approve-modal" class="booking-modal fixed inset-0 z-[60] items-center justify-center bg-slate-950/45 p-4" role="dialog" aria-modal="true" aria-labelledby="approve-title" aria-hidden="true">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <p class="text-xs font-bold uppercase tracking-[.14em] text-yellow-700">Konfirmasi keputusan</p>
            <h2 id="approve-title" class="mt-2 text-xl font-bold text-slate-900">Setujui peminjaman?</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Sistem akan membuat jadwal setelah konflik diperiksa kembali.</p>
            <form action="{{ route('admin.booking.approve', $booking->id) }}" method="POST" class="mt-6 flex justify-end gap-3">
                @csrf
                <input type="hidden" name="return_status" value="pending">
                <button type="button" class="booking-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" data-close-modal="approve-modal">Batal</button>
                <button type="submit" class="booking-action bg-yellow-500 text-white hover:bg-yellow-600">Setujui peminjaman</button>
            </form>
        </div>
    </div>

    <div id="reject-modal" class="booking-modal fixed inset-0 z-[60] items-center justify-center bg-slate-950/45 p-4" role="dialog" aria-modal="true" aria-labelledby="reject-title" aria-hidden="true">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <p class="text-xs font-bold uppercase tracking-[.14em] text-red-700">Keputusan peminjaman</p>
            <h2 id="reject-title" class="mt-2 text-xl font-bold text-slate-900">Tolak peminjaman</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Alasan disimpan dalam riwayat dan ditampilkan kepada pengaju.</p>
            <form action="{{ route('admin.booking.reject', $booking->id) }}" method="POST" class="mt-5">
                @csrf
                <input type="hidden" name="return_status" value="pending">
                <label for="rejection-reason" class="text-sm font-bold text-slate-800">Alasan penolakan <span class="text-red-600">*</span></label>
                <textarea id="rejection-reason" name="rejection_reason" required maxlength="500" rows="4" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100" placeholder="Contoh: waktu yang diminta bertabrakan dengan kegiatan prioritas."></textarea>
                <div class="mt-1 flex justify-end text-xs text-slate-500"><span id="rejection-count">0/500</span></div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="booking-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" data-close-modal="reject-modal">Batal</button>
                    <button type="submit" class="booking-action bg-red-600 text-white hover:bg-red-700">Tolak peminjaman</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    (() => {
        const modals = [...document.querySelectorAll('.booking-modal')];
        let lastTrigger = null;
        const closeModal = (modal) => {
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            lastTrigger?.focus();
        };
        const openModal = (id, trigger) => {
            const modal = document.getElementById(id);
            if (!modal) return;
            lastTrigger = trigger;
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            window.setTimeout(() => modal.querySelector('button, textarea')?.focus(), 20);
        };
        document.querySelector('[data-open-approve]')?.addEventListener('click', (event) => openModal('approve-modal', event.currentTarget));
        document.querySelector('[data-open-reject]')?.addEventListener('click', (event) => openModal('reject-modal', event.currentTarget));
        document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', () => closeModal(document.getElementById(button.dataset.closeModal))));
        modals.forEach((modal) => modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(modal); }));
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') modals.filter((modal) => modal.getAttribute('aria-hidden') === 'false').forEach(closeModal); });
        const reason = document.getElementById('rejection-reason');
        const count = document.getElementById('rejection-count');
        reason?.addEventListener('input', () => { count.textContent = `${reason.value.length}/500`; });
    })();
</script>
@endpush
