@extends('layouts.admin')

@section('title', 'Detail Reservasi Bloomberg - Admin')

@section('content')
    <!-- Back Button & Header -->
    <div class="mb-6">
        <a href="{{ route('admin.bloomberg.index') }}" class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition-colors mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali ke Daftar</span>
        </a>

        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Detail {{ $request->isWalkIn() ? 'Kunjungan Langsung' : 'Reservasi' }} Bloomberg</h1>
                    <p class="text-indigo-100">{{ $request->name }} — {{ $request->usage_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
                <div>
                    <span class="px-4 py-2 {{ $request->isWalkIn() ? 'bg-orange-400 text-orange-900' : 'bg-indigo-300 text-indigo-900' }} font-bold rounded-xl text-sm">
                        {{ $request->type_label }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Request Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Applicant Data -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Data Pemohon
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Nama Lengkap</p>
                        <p class="font-semibold text-gray-800">{{ $request->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ $request->isLecturer() ? 'NIP' : 'NIM' }}</p>
                        <p class="font-semibold text-gray-800">{{ $request->nim_nip }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-semibold text-gray-800">{{ $request->applicant_type_label }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nomor HP</p>
                        <p class="font-semibold text-gray-800">{{ $request->phone }}</p>
                    </div>
                    @if($request->study_program)
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Program Studi</p>
                        <p class="font-semibold text-gray-800">{{ $request->study_program }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Schedule & Purpose -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Jadwal & Keperluan
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Penggunaan</p>
                        <p class="font-semibold text-gray-800">{{ $request->usage_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Sesi</p>
                        <p class="font-semibold text-gray-800">{{ $request->session_label }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Keperluan</p>
                        <p class="font-semibold text-gray-800">{{ $request->purpose_label }}</p>
                    </div>
                    @if($request->research_title)
                    <div>
                        <p class="text-sm text-gray-500">Judul Penelitian / Nama Lomba</p>
                        <p class="font-semibold text-gray-800">{{ $request->research_title }}</p>
                    </div>
                    @endif
                    @if($request->subject_name)
                    <div>
                        <p class="text-sm text-gray-500">Mata Kuliah</p>
                        <p class="font-semibold text-gray-800">{{ $request->subject_name }}</p>
                    </div>
                    @endif
                    @if($request->lecturer_name)
                    <div>
                        <p class="text-sm text-gray-500">Nama Dosen</p>
                        <p class="font-semibold text-gray-800">{{ $request->lecturer_name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dokumen
                </h2>
                <div>
                    <p class="text-sm text-gray-500 mb-2">Surat Pengantar Kaprodi/Dosen</p>
                    @if($request->statement_file)
                        <a href="{{ asset('storage/' . $request->statement_file) }}" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Lihat/Unduh PDF
                        </a>
                    @else
                        <span class="text-gray-400 text-sm">Tidak ada dokumen</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Info Card -->
        <div class="space-y-6">
            <!-- Type & Timestamp Info -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi
                </h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-gray-500">Tipe</p>
                        <span class="inline-block mt-1 px-3 py-1 {{ $request->isWalkIn() ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }} font-semibold rounded-lg text-xs">
                            {{ $request->type_label }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500">Waktu Submit</p>
                        <p class="font-semibold text-gray-800">{{ $request->created_at->locale('id')->isoFormat('dddd, D MMMM Y, HH:mm') }} WIB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
