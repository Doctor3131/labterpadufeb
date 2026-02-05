@extends('layouts.admin')

@section('title', 'Detail Permintaan Refinitiv - Admin')

@section('content')
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.refinitiv.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Detail Permintaan Refinitiv</h1>
                    <p class="text-blue-100">ID: #{{ $request->id }}</p>
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

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Data Pemohon -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Dokumen
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($request->ktm_file)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <label class="text-sm text-gray-500 block mb-2">KTM</label>
                        <a href="{{ Storage::url($request->ktm_file) }}" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat KTM
                        </a>
                    </div>
                    @endif
                    <div class="border border-gray-200 rounded-lg p-4">
                        <label class="text-sm text-gray-500 block mb-2">Surat Pernyataan</label>
                        <a href="{{ Storage::url($request->statement_file) }}" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat Surat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Action Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi</h2>
                
                @if($request->attendance_status === 'pending')
                    <div class="space-y-3">
                        <form action="{{ route('admin.refinitiv.hadir', $request) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Tandai Hadir
                            </button>
                        </form>
                        <form action="{{ route('admin.refinitiv.tidak-hadir', $request) }}" method="POST">
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
                    <form action="{{ route('admin.refinitiv.reset', $request) }}" method="POST">
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

            <!-- Notes Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Catatan Admin</h2>
                <form action="{{ route('admin.refinitiv.notes', $request) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <textarea name="admin_notes" rows="4" 
                        placeholder="Tambahkan catatan..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">{{ $request->admin_notes }}</textarea>
                    <button type="submit" class="mt-3 w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                        Simpan Catatan
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
