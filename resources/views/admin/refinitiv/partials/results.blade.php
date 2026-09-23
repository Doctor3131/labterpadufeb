@php
    $clearFiltersUrl = route('admin.refinitiv.index', ['status' => $status]);
@endphp

<div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
    <p data-refinitiv-results-summary data-refinitiv-result-item tabindex="-1">
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
                    <tr class="align-top transition-colors hover:bg-blue-50/40" data-refinitiv-result-item>
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
                            <div data-refinitiv-result-item>
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-700">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h8m-8 4h8m-8 4h5m-8 5h14a2 2 0 002-2V7l-5-5H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                @if($search !== '')
                                    <h2 class="text-base font-semibold text-slate-900">Tidak ada hasil yang cocok</h2>
                                    <p class="mt-1 text-sm text-slate-600">Coba periksa ejaan atau cari dengan nama, NIM/NIP, nomor WhatsApp, atau ID.</p>
                                    <a href="{{ $clearFiltersUrl }}" data-refinitiv-reset-link class="mt-4 inline-flex min-h-[40px] items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">Hapus pencarian</a>
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
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="border-t border-slate-100 px-4 py-2 text-xs text-slate-500 xl:hidden">Geser tabel ke samping untuk melihat kolom lainnya.</p>
</section>

@if($requests->hasPages())
    <div class="mt-5" data-refinitiv-pagination>
        {{ $requests->appends(['status' => $status, 'q' => $search, 'sort' => $sort])->links() }}
    </div>
@endif
