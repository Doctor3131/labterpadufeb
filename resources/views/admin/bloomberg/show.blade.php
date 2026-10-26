@extends('layouts.admin')

@section('title', 'Detail Reservasi Bloomberg - Admin')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.bloomberg.index') }}" class="inline-flex items-center gap-2 rounded-md text-sm font-medium text-slate-600 transition hover:text-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke daftar
        </a>
    </div>

    <header class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-1 text-sm font-semibold uppercase tracking-wide text-indigo-700">Layanan data · Bloomberg</p>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Detail {{ $request->isWalkIn() ? 'kunjungan langsung' : 'reservasi' }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $request->name }} · {{ $request->usage_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <span class="inline-flex w-fit items-center rounded-lg px-3 py-2 text-sm font-semibold {{ $request->isWalkIn() ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }}">
            {{ $request->type_label }}
        </span>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Request Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Applicant Data -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
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
                    @if($request->university)
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Universitas</p>
                        <p class="font-semibold text-gray-800">{{ $request->university }}</p>
                    </div>
                    @endif
                    @if($request->study_program)
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Program Studi</p>
                        <p class="font-semibold text-gray-800">{{ $request->study_program }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Schedule & Purpose -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
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
                    @if($request->variables)
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500">Variabel/data yang dicari</p>
                        <p class="font-semibold text-gray-800 whitespace-pre-wrap break-words">{{ $request->variables }}</p>
                    </div>
                    @endif
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
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dokumen
                </h2>
                <div>
                    <p class="text-sm text-gray-500 mb-2">Surat Pengantar Kaprodi/Dosen</p>
                    @if($request->statement_file)
                        <a href="{{ route('admin.secure-file', ['path' => $request->statement_file]) }}" target="_blank"
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
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
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
