@if ($requests->total() > 0)
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2 text-sm">
        <p class="text-slate-600">
            Menampilkan <span class="font-semibold text-slate-900">{{ $requests->firstItem() }}–{{ $requests->lastItem() }}</span>
            dari <span class="font-semibold text-slate-900">{{ $requests->total() }}</span> permohonan
        </p>
    </div>

    @foreach ($requests as $req)
        <article class="mb-4 rounded-lg border border-slate-200 bg-white p-4 transition hover:border-indigo-200 hover:shadow-sm">
            <div class="mb-3 flex items-start justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $req->type === 'walk_in' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }}">
                        {{ $req->type_label }}
                    </span>
                    <span class="rounded-lg bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ $req->applicant_type_label }}</span>
                    <span class="text-xs text-gray-500">{{ $req->created_at->diffForHumans() }}</span>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-gray-800">{{ $req->usage_date->locale('id')->isoFormat('D MMM Y') }}</span>
                    <span class="block text-xs font-medium text-indigo-600">{{ $req->session_label }}</span>
                </div>
            </div>

            <h3 class="mb-2 text-lg font-bold text-gray-800">{{ $req->name }}</h3>
            <div class="mb-4 grid grid-cols-2 gap-2 text-sm text-gray-600 md:grid-cols-4">
                <div>
                    <span class="text-gray-400">{{ $req->isLecturer() ? 'NIP' : 'NIM' }}:</span>
                    <span class="font-medium">{{ $req->nim_nip }}</span>
                </div>
                @if ($req->university)
                    <div>
                        <span class="text-gray-400">Universitas:</span>
                        <span class="block truncate font-medium">{{ $req->university }}</span>
                    </div>
                @endif
                @if ($req->study_program)
                    <div>
                        <span class="text-gray-400">Prodi:</span>
                        <span class="block truncate font-medium">{{ $req->study_program }}</span>
                    </div>
                @endif
                <div>
                    <span class="text-gray-400">Keperluan:</span>
                    <span class="font-medium">{{ $req->purpose_label }}</span>
                </div>
                @if ($req->variables)
                    <div class="col-span-2 md:col-span-4">
                        <span class="text-gray-400">Variabel/data:</span>
                        <span class="font-medium">{{ \Illuminate\Support\Str::limit($req->variables, 140) }}</span>
                    </div>
                @endif
                <div>
                    <span class="text-gray-400">HP:</span>
                    <span class="font-medium">{{ $req->phone }}</span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <a href="{{ route('admin.bloomberg.show', $req) }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Detail
                </a>
            </div>
        </article>
    @endforeach

    @if ($requests->hasPages())
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
@else
    <div class="rounded-lg bg-slate-50 p-8 text-center">
        <div class="mb-4 inline-block rounded-full bg-gray-100 p-4">
            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        @if ($hasActiveFilters)
            <h3 class="mb-2 text-lg font-bold text-gray-800">Tidak ada hasil yang cocok</h3>
            <p class="text-sm text-gray-600">Coba ubah kata kunci atau filter yang dipilih.</p>
            <a href="{{ route('admin.bloomberg.index', ['type' => $type]) }}" data-bloomberg-reset
               class="mt-4 inline-flex min-h-10 items-center justify-center rounded-lg border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">
                Hapus filter
            </a>
        @else
            <h3 class="mb-2 text-lg font-bold text-gray-800">Tidak Ada Data</h3>
            <p class="text-sm text-gray-600">
                @if ($type === 'reservasi')
                    Belum ada reservasi Bloomberg.
                @elseif ($type === 'walk_in')
                    Belum ada kunjungan langsung Bloomberg.
                @else
                    Belum ada data Bloomberg.
                @endif
            </p>
        @endif
    </div>
@endif
