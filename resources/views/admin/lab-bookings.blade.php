@extends('layouts.admin')

@section('title', 'Peminjaman Laboratorium')

@push('styles')
<style>
    .lab-bookings-page { max-width: 1120px; margin: 0 auto; }
    .lab-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 1px 2px rgb(15 23 42 / 0.04); }
    .lab-stat { border: 1px solid #e2e8f0; border-radius: .875rem; background: #fff; padding: 1rem; }
    .lab-stat[data-active="true"] { border-color: #facc15; box-shadow: 0 0 0 3px rgb(254 249 195 / 0.9); }
    .lab-tab { border-bottom: 2px solid transparent; color: #64748b; font-weight: 700; padding: .875rem 1rem; white-space: nowrap; }
    .lab-tab[aria-selected="true"] { border-color: #eab308; color: #854d0e; }
    .lab-tab:focus-visible, .lab-action:focus-visible { outline: 3px solid #fde68a; outline-offset: 2px; }
    .lab-booking-card { display: grid; grid-template-columns: 7.5rem minmax(0, 1fr); overflow: hidden; border: 1px solid #e2e8f0; border-radius: .875rem; background: #fff; }
    .lab-booking-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 14px rgb(15 23 42 / 0.06); }
    .lab-booking-date { border-right: 1px solid #e2e8f0; padding: 1rem; }
    .lab-chip { display: inline-flex; align-items: center; border-radius: 9999px; padding: .25rem .625rem; font-size: .72rem; font-weight: 700; }
    .lab-action { display: inline-flex; align-items: center; justify-content: center; border-radius: .625rem; padding: .55rem .8rem; font-size: .875rem; font-weight: 700; transition: background-color .15s, border-color .15s, color .15s; }
    .lab-modal[aria-hidden="true"] { display: none; }
    .lab-modal[aria-hidden="false"] { display: flex; }
    @media (max-width: 640px) {
        .lab-booking-card { grid-template-columns: 1fr; }
        .lab-booking-date { display: grid; grid-template-columns: 1fr 1fr; gap: .2rem 1rem; border-right: 0; border-bottom: 1px solid #e2e8f0; padding: .875rem 1rem; }
        .lab-booking-date p { margin-top: 0 !important; }
    }
</style>
@endpush

@section('content')
@php
    $statusMeta = [
        'pending' => ['label' => 'Menunggu keputusan', 'description' => 'Perlu ditinjau', 'chip' => 'bg-amber-100 text-amber-900'],
        'approved' => ['label' => 'Disetujui', 'description' => 'Siap dijalankan', 'chip' => 'bg-emerald-100 text-emerald-800'],
        'rejected' => ['label' => 'Ditolak', 'description' => 'Arsip keputusan', 'chip' => 'bg-red-100 text-red-800'],
    ];
    $typeLabels = [
        'perkuliahan_tetap' => 'Perkuliahan tetap',
        'perkuliahan_tidak_tetap' => 'Perkuliahan tidak tetap',
        'non_perkuliahan' => 'Non-perkuliahan',
        'pribadi' => 'Pribadi',
    ];
    $bookingGroups = [
        'pending' => $pendingBookings,
        'approved' => $approvedBookings,
        'rejected' => $rejectedBookings,
    ];
    $activeStatus = in_array(request('status'), array_keys($bookingGroups), true) ? request('status') : 'pending';
@endphp

<main class="lab-bookings-page px-4 py-6 md:px-6 md:py-8">
    <div class="mb-6 flex flex-col gap-4 md:mb-8 md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-yellow-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Dashboard
            </a>
            <p class="mt-4 text-xs font-bold uppercase tracking-[.16em] text-yellow-700">Operasional laboratorium</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">Peminjaman laboratorium</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Tinjau pengajuan, ambil keputusan, dan buka detail jadwal tanpa bercampur dengan peminjaman aset.</p>
        </div>
        <a href="{{ route('admin.schedules.index') }}" class="lab-action shrink-0 border border-yellow-300 bg-yellow-50 text-yellow-900 hover:bg-yellow-100">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Buka kalender jadwal
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">{{ session('error') }}</div>
    @endif

    <section class="mb-5 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan status pengajuan">
        @foreach($bookingGroups as $status => $bookings)
            <button type="button" class="lab-stat text-left" data-status-summary="{{ $status }}" data-active="{{ $activeStatus === $status ? 'true' : 'false' }}">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $statusMeta[$status]['description'] }}</p>
                <div class="mt-2 flex items-end justify-between gap-3">
                    <span class="text-2xl font-bold text-slate-900">{{ $bookings->total() }}</span>
                    <span class="text-sm font-bold {{ $status === 'pending' ? 'text-yellow-700' : ($status === 'approved' ? 'text-emerald-700' : 'text-red-700') }}">{{ $statusMeta[$status]['label'] }}</span>
                </div>
            </button>
        @endforeach
    </section>

    <section class="lab-panel overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 md:flex-row md:items-center md:justify-between md:px-5">
            <div class="-mb-4 flex max-w-full overflow-x-auto" role="tablist" aria-label="Status peminjaman">
                @foreach($bookingGroups as $status => $bookings)
                    <button id="{{ $status }}-tab" type="button" class="lab-tab" role="tab" aria-controls="{{ $status }}-panel" aria-selected="{{ $activeStatus === $status ? 'true' : 'false' }}" tabindex="{{ $activeStatus === $status ? '0' : '-1' }}" data-booking-tab="{{ $status }}">
                        {{ $statusMeta[$status]['label'] }} <span class="ml-1 text-xs">{{ $bookings->total() }}</span>
                    </button>
                @endforeach
            </div>
            <label class="relative block md:w-72">
                <span class="sr-only">Cari peminjaman pada tab aktif</span>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input id="booking-search" type="search" class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-100" placeholder="Cari pengaju, kegiatan, atau lab">
            </label>
        </div>

        @foreach($bookingGroups as $status => $bookings)
            <div id="{{ $status }}-panel" class="p-4 md:p-5" role="tabpanel" aria-labelledby="{{ $status }}-tab" data-booking-panel="{{ $status }}" @if($activeStatus !== $status) hidden @endif>
                <div class="space-y-3" data-booking-list>
                    @forelse($bookings as $booking)
                        @include('admin.lab-bookings.booking-card', compact('booking', 'status', 'statusMeta', 'typeLabels'))
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-5 py-12 text-center">
                            <p class="font-semibold text-slate-700">Belum ada pengajuan {{ strtolower($statusMeta[$status]['label']) }}.</p>
                            <p class="mt-1 text-sm text-slate-500">Data yang masuk akan tampil pada tab ini.</p>
                        </div>
                    @endforelse
                    <p class="hidden rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600" data-search-empty>Tidak ada hasil yang cocok pada tab ini.</p>
                </div>
                @if($bookings->hasPages())
                    <div class="mt-5 border-t border-slate-100 pt-4">{{ $bookings->appends(['status' => $status])->links() }}</div>
                @endif
            </div>
        @endforeach
    </section>
</main>

<div id="approve-modal" class="lab-modal fixed inset-0 z-[60] items-center justify-center bg-slate-950/45 p-4" role="dialog" aria-modal="true" aria-labelledby="approve-modal-title" aria-hidden="true">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <p class="text-xs font-bold uppercase tracking-[.14em] text-yellow-700">Konfirmasi keputusan</p>
        <h2 id="approve-modal-title" class="mt-2 text-xl font-bold text-slate-900">Setujui peminjaman?</h2>
        <p id="approve-modal-description" class="mt-2 text-sm leading-6 text-slate-600">Jadwal akan dibuat dan tidak dapat disetujui ulang.</p>
        <form id="approve-form" method="POST" class="mt-6 flex justify-end gap-3">
            @csrf
            <input type="hidden" name="return_status" value="pending">
            <button type="button" class="lab-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" data-close-modal="approve-modal">Batal</button>
            <button type="submit" class="lab-action bg-yellow-500 text-white hover:bg-yellow-600">Setujui peminjaman</button>
        </form>
    </div>
</div>

<div id="reject-modal" class="lab-modal fixed inset-0 z-[60] items-center justify-center bg-slate-950/45 p-4" role="dialog" aria-modal="true" aria-labelledby="reject-modal-title" aria-hidden="true">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <p class="text-xs font-bold uppercase tracking-[.14em] text-red-700">Keputusan peminjaman</p>
        <h2 id="reject-modal-title" class="mt-2 text-xl font-bold text-slate-900">Tolak peminjaman</h2>
        <p id="reject-modal-description" class="mt-2 text-sm leading-6 text-slate-600">Berikan alasan yang jelas agar pengaju memahami keputusan ini.</p>
        <form id="reject-form" method="POST" class="mt-5">
            @csrf
            <input type="hidden" name="return_status" value="pending">
            <label for="rejection_reason" class="text-sm font-bold text-slate-800">Alasan penolakan <span class="text-red-600">*</span></label>
            <textarea id="rejection_reason" name="rejection_reason" required maxlength="500" rows="4" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100" placeholder="Contoh: waktu yang diminta bertabrakan dengan kegiatan prioritas."></textarea>
            <div class="mt-1 flex justify-between text-xs text-slate-500"><span>Alasan akan tersimpan pada riwayat.</span><span id="rejection-count">0/500</span></div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="lab-action border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" data-close-modal="reject-modal">Batal</button>
                <button type="submit" class="lab-action bg-red-600 text-white hover:bg-red-700">Tolak peminjaman</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const statusNames = ['pending', 'approved', 'rejected'];
        const search = document.getElementById('booking-search');
        const approveModal = document.getElementById('approve-modal');
        const rejectModal = document.getElementById('reject-modal');
        const approveForm = document.getElementById('approve-form');
        const rejectForm = document.getElementById('reject-form');
        const rejectionReason = document.getElementById('rejection_reason');
        const rejectionCount = document.getElementById('rejection-count');
        let lastTrigger = null;

        const setActiveTab = (status, updateUrl = true) => {
            statusNames.forEach((name) => {
                const selected = name === status;
                document.querySelector(`[data-booking-tab="${name}"]`).setAttribute('aria-selected', selected ? 'true' : 'false');
                document.querySelector(`[data-booking-tab="${name}"]`).setAttribute('tabindex', selected ? '0' : '-1');
                document.querySelector(`[data-booking-panel="${name}"]`).hidden = !selected;
                document.querySelector(`[data-status-summary="${name}"]`).dataset.active = selected ? 'true' : 'false';
            });
            search.value = '';
            filterCards();
            if (updateUrl) {
                const url = new URL(window.location.href);
                url.searchParams.set('status', status);
                window.history.replaceState({}, '', url);
            }
        };

        const filterCards = () => {
            const activePanel = document.querySelector('[data-booking-panel]:not([hidden])');
            if (!activePanel) return;
            const query = search.value.trim().toLocaleLowerCase('id');
            let shown = 0;
            activePanel.querySelectorAll('[data-booking-card]').forEach((card) => {
                const matches = !query || card.textContent.toLocaleLowerCase('id').includes(query);
                card.hidden = !matches;
                if (matches) shown += 1;
            });
            const empty = activePanel.querySelector('[data-search-empty]');
            if (empty) empty.hidden = shown !== 0 || !query;
        };

        document.querySelectorAll('[data-booking-tab], [data-status-summary]').forEach((trigger) => {
            trigger.addEventListener('click', () => setActiveTab(trigger.dataset.bookingTab || trigger.dataset.statusSummary));
        });
        search.addEventListener('input', filterCards);

        const closeModal = (modal) => {
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            lastTrigger?.focus();
        };
        const openModal = (modal, trigger) => {
            lastTrigger = trigger;
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            window.setTimeout(() => modal.querySelector('button, textarea')?.focus(), 20);
        };

        document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', () => closeModal(document.getElementById(button.dataset.closeModal))));
        [approveModal, rejectModal].forEach((modal) => modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(modal); }));
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') [approveModal, rejectModal].forEach((modal) => { if (modal.getAttribute('aria-hidden') === 'false') closeModal(modal); }); });

        document.querySelectorAll('[data-approve-booking]').forEach((button) => button.addEventListener('click', () => {
            approveForm.action = `{{ url('/admin/bookings') }}/${button.dataset.approveBooking}/approve`;
            document.getElementById('approve-modal-description').textContent = `Setujui “${button.dataset.bookingTitle}”? Jadwal akan dibuat setelah persetujuan.`;
            openModal(approveModal, button);
        }));
        document.querySelectorAll('[data-reject-booking]').forEach((button) => button.addEventListener('click', () => {
            rejectForm.action = `{{ url('/admin/bookings') }}/${button.dataset.rejectBooking}/reject`;
            document.getElementById('reject-modal-description').textContent = `Tulis alasan penolakan untuk “${button.dataset.bookingTitle}”.`;
            rejectionReason.value = '';
            rejectionCount.textContent = '0/500';
            openModal(rejectModal, button);
        }));
        rejectionReason.addEventListener('input', () => { rejectionCount.textContent = `${rejectionReason.value.length}/500`; });
    })();
</script>
@endpush
