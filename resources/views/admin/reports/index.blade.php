@extends('layouts.admin')

@section('title', 'Laporan - Lab Digital FEB UNDIP')

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-indigo-600 rounded-2xl p-4 md:p-6 shadow-none">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Laporan</h1>
                    <p class="text-xs md:text-sm text-indigo-100">Export data Lab, BPS, dan Refinitiv</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-2 md:p-3 rounded-xl">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Type Tabs -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="flex border-b border-gray-200">
            <a href="{{ route('admin.reports.index', ['report_type' => 'lab']) }}" 
               class="flex-1 px-4 py-4 text-center font-semibold transition-all {{ $reportType === 'lab' ? 'text-yellow-600 border-b-2 border-yellow-500 bg-yellow-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="hidden sm:inline">Peminjaman Lab</span>
                    <span class="sm:hidden">Lab</span>
                </div>
            </a>
            <a href="{{ route('admin.reports.index', ['report_type' => 'bps']) }}" 
               class="flex-1 px-4 py-4 text-center font-semibold transition-all {{ $reportType === 'bps' ? 'text-teal-600 border-b-2 border-teal-500 bg-teal-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Data BPS</span>
                    <span class="sm:hidden">BPS</span>
                </div>
            </a>
            <a href="{{ route('admin.reports.index', ['report_type' => 'refinitiv']) }}" 
               class="flex-1 px-4 py-4 text-center font-semibold transition-all {{ $reportType === 'refinitiv' ? 'text-blue-600 border-b-2 border-blue-500 bg-blue-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    <span class="hidden sm:inline">Data Refinitiv</span>
                    <span class="sm:hidden">Refinitiv</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h2 class="font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter Laporan {{ $reportTypes[$reportType] ?? '' }}
            </h2>
        </div>
        
        <form method="GET" action="{{ route('admin.reports.index') }}" class="p-4 md:p-6">
            <input type="hidden" name="report_type" value="{{ $reportType }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 {{ $reportType === 'lab' ? 'lg:grid-cols-4' : 'lg:grid-cols-2' }} gap-4 mb-4">
                <!-- Start Month -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan Mulai</label>
                    <input type="month" name="start_month" value="{{ request('start_month') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- End Month -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan Akhir</label>
                    <input type="month" name="end_month" value="{{ request('end_month') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                @if($reportType === 'lab')
                    <!-- Lab Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Laboratorium</label>
                        <select name="lab_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Lab</option>
                            @foreach($labs as $lab)
                                <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Peminjaman</label>
                        <select name="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua Tipe</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Tampilkan
                </button>
                <a href="{{ route('admin.reports.index', ['report_type' => $reportType]) }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-all flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['report_type' => $reportType])) }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('admin.reports.export-word', array_merge(request()->query(), ['report_type' => $reportType])) }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Word
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">
            Menampilkan <span class="font-bold text-gray-800">{{ $data->total() }}</span> data {{ $reportTypes[$reportType] ?? '' }}
            @if(request('start_month') || request('end_month'))
                <span class="text-indigo-600">
                    @if(request('start_month') && request('end_month'))
                        ({{ \Carbon\Carbon::parse(request('start_month'))->locale('id')->isoFormat('MMMM Y') }} - {{ \Carbon\Carbon::parse(request('end_month'))->locale('id')->isoFormat('MMMM Y') }})
                    @elseif(request('start_month'))
                        (dari {{ \Carbon\Carbon::parse(request('start_month'))->locale('id')->isoFormat('MMMM Y') }})
                    @else
                        (sampai {{ \Carbon\Carbon::parse(request('end_month'))->locale('id')->isoFormat('MMMM Y') }})
                    @endif
                </span>
            @endif
        </p>
    </div>

    <!-- Data Table -->
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
                                    {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
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
            @endif
        </div>

        <!-- Pagination -->
        @if($data->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $data->links() }}
            </div>
        @endif
    </div>
@endsection
