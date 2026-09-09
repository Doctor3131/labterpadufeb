@php
    $title = $booking->booking_type === 'non_perkuliahan'
        ? $booking->activity_name
        : $booking->course_name;
    $recurrenceDays = $booking->recurrence_days ?: ($booking->day ? [$booking->day] : []);
    $dateTone = $status === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-900' : ($status === 'approved' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800');
@endphp

<article class="lab-booking-card" data-booking-card>
    <div class="lab-booking-date {{ $dateTone }}">
        <p class="text-xs font-bold uppercase tracking-wide">{{ optional($booking->booking_date)->locale('id')->isoFormat('dddd') }}</p>
        <p class="mt-1 text-xl font-bold leading-none">{{ optional($booking->booking_date)->locale('id')->isoFormat('D MMM') }}</p>
        <p class="mt-1 text-xs font-semibold">{{ optional($booking->booking_date)->format('Y') }}</p>
        <p class="mt-4 text-sm font-bold">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</p>
    </div>

    <div class="min-w-0 p-4 md:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap gap-2">
                    <span class="lab-chip {{ $statusMeta[$status]['chip'] }}">{{ $statusMeta[$status]['label'] }}</span>
                    <span class="lab-chip bg-slate-100 text-slate-700">{{ $typeLabels[$booking->booking_type] }}</span>
                    @if($booking->is_recurring)
                        <span class="lab-chip bg-amber-50 text-amber-800">Berulang</span>
                    @endif
                    @if($booking->document_path)
                        <span class="lab-chip bg-sky-50 text-sky-800">Dokumen tersedia</span>
                    @endif
                </div>
                <h3 class="truncate text-base font-bold text-slate-900 md:text-lg" title="{{ $title }}">{{ $title ?: 'Tanpa judul kegiatan' }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $booking->lab?->name ?? 'Laboratorium tidak tersedia' }} <span class="px-1 text-slate-300">•</span> {{ $booking->participant_count }} peserta</p>
            </div>
            <p class="shrink-0 text-xs text-slate-500">Diajukan {{ $booking->created_at->diffForHumans() }}</p>
        </div>

        <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2 xl:grid-cols-3">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pengaju</dt>
                <dd class="mt-0.5 font-semibold text-slate-800">{{ $booking->pic_name }}</dd>
                <dd class="text-slate-500">{{ $booking->study_program ?: ($booking->nip ? 'Dosen / tenaga kependidikan' : '—') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $booking->booking_type === 'non_perkuliahan' ? 'Jenis kegiatan' : 'Dosen pengampu' }}</dt>
                <dd class="mt-0.5 text-slate-700">{{ $booking->booking_type === 'non_perkuliahan' ? ($booking->activity_type ?: '—') : ($booking->lecturer_name ?: '—') }}</dd>
            </div>
            @if($booking->is_recurring)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pengulangan</dt>
                    <dd class="mt-0.5 text-slate-700">{{ implode(', ', $recurrenceDays) ?: 'Mingguan' }}@if($booking->end_date) sampai {{ $booking->end_date->locale('id')->isoFormat('D MMM Y') }}@endif</dd>
                </div>
            @endif
            @if($status === 'rejected' && $booking->rejection_reason)
                <div class="sm:col-span-2 xl:col-span-3">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-red-500">Alasan penolakan</dt>
                    <dd class="mt-1 rounded-lg bg-red-50 px-3 py-2 text-red-800">{{ $booking->rejection_reason }}</dd>
                </div>
            @endif
        </dl>

        <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">{{ $booking->handler ? 'Diproses oleh '.$booking->handler->name : 'Belum diproses' }}</p>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('admin.booking.show', $booking->id) }}" class="lab-action border border-slate-300 bg-white text-slate-700 hover:border-yellow-500 hover:text-yellow-800">Lihat detail</a>
                @if($status === 'pending')
                    <button type="button" class="lab-action border border-red-200 bg-white text-red-700 hover:bg-red-50" data-reject-booking="{{ $booking->id }}" data-booking-title="{{ $title }}">Tolak</button>
                    <button type="button" class="lab-action bg-yellow-500 text-white hover:bg-yellow-600" data-approve-booking="{{ $booking->id }}" data-booking-title="{{ $title }}">Setujui</button>
                @endif
            </div>
        </div>
    </div>
</article>
