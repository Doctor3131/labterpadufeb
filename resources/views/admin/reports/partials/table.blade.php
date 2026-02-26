{{-- Results Summary --}}
<div class="mb-4 flex items-center justify-between" id="results-summary">
    <p class="text-sm text-gray-600">
        Menampilkan <span class="font-bold text-gray-800" id="total-count">{{ $data->total() }}</span> data {{ $reportTypes[$reportType] ?? '' }}
    </p>
</div>

{{-- Data Table --}}
<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        @if($reportType === 'lab')
            {{-- Lab Bookings Table --}}
            <table class="w-full">
                <thead class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Tanggal Dibuat</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama Peminjam</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Hari</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Peserta</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $booking)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($booking->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900">{{ $booking->pic_name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <x-booking-badge :type="$booking->booking_type" class="text-xs" />
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-800">{{ $booking->lab?->name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $booking->day }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $booking->participant_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($booking->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Disetujui
                                    </span>
                                @elseif($booking->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Menunggu
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Tidak ada data peminjaman</p>
                                    <p class="text-gray-400 text-sm mt-1">Coba ubah filter untuk melihat data lainnya</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($reportType === 'bps')
            {{-- BPS Requests Table --}}
            <table class="w-full">
                <thead class="bg-gradient-to-r from-teal-500 to-teal-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Timestamp</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">NIM/NIP</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Program Studi</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Keperluan Penggunaan Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $index => $request)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600 text-center">
                                {{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900">{{ $request->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->applicant_type === 'mahasiswa' ? $request->nim : $request->nip }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->study_program ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->purpose === 'Lainnya' ? $request->purpose_other : $request->purpose }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Tidak ada data permohonan BPS</p>
                                    <p class="text-gray-400 text-sm mt-1">Coba ubah filter untuk melihat data lainnya</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($reportType === 'refinitiv')
            {{-- Refinitiv Requests Table --}}
            <table class="w-full">
                <thead class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">NIM/NIP</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Program Studi</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Keperluan Penggunaan Data</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Tanggal Pemakaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $index => $request)
                        @php
                            $purposes = \App\Models\RefinitivRequest::PURPOSES;
                            $sessions = \App\Models\RefinitivRequest::SESSIONS;
                            $purposeLabel = $request->purpose === 'lainnya' ? $request->purpose_other : ($purposes[$request->purpose] ?? $request->purpose);
                            $sessionLabel = $sessions[$request->session] ?? $request->session;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600 text-center">
                                {{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900">{{ $request->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->nim_nip ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->study_program ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $purposeLabel }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($request->usage_date)->format('d/m/Y') }}
                                <span class="text-xs text-gray-400">({{ $sessionLabel }})</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Tidak ada data permohonan Refinitiv</p>
                                    <p class="text-gray-400 text-sm mt-1">Coba ubah filter untuk melihat data lainnya</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($reportType === 'bloomberg')
            {{-- Bloomberg Requests Table --}}
            <table class="w-full">
                <thead class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Timestamp</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">NIM/NIP</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Prodi</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Tanggal & Sesi</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Keperluan</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $index => $request)
                        @php
                            $purposes = \App\Models\BloombergRequest::PURPOSES;
                            $purposeLabel = $purposes[$request->purpose] ?? $request->purpose;
                            $detail = $request->research_title ?: ($request->subject_name ?: ($request->lecturer_name ?: '-'));
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600 text-center">
                                {{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($request->type === 'walk_in')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Walk-in</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Reservasi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900">{{ $request->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->applicant_type_label }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->nim_nip }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $request->study_program ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($request->usage_date)->format('d/m/Y') }}
                                <span class="text-xs text-gray-400">({{ $request->session_label }})</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $purposeLabel }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $detail }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">Tidak ada data reservasi Bloomberg</p>
                                    <p class="text-gray-400 text-sm mt-1">Coba ubah filter untuk melihat data lainnya</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if($data->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $data->links() }}
        </div>
    @endif
</div>
