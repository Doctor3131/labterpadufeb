@extends('layouts.admin')

@section('title', 'Kelola Permintaan Refinitiv - Admin')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-4 md:p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Permintaan Data Refinitiv</h1>
                    <p class="text-sm text-blue-100">Kelola kehadiran pemohon data Refinitiv</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="border-b border-gray-100">
            <nav class="flex">
                <a href="{{ route('admin.refinitiv.index', ['status' => 'pending']) }}" 
                   class="flex-1 flex flex-col items-center px-4 py-4 text-sm font-semibold border-b-3 {{ $status === 'pending' ? 'border-yellow-500 text-yellow-700 bg-yellow-50' : 'border-transparent text-gray-500 hover:text-yellow-600' }}">
                    <div class="bg-yellow-100 p-2 rounded-lg mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="hidden md:inline">Menunggu</span>
                    <span class="mt-1 px-2 py-0.5 bg-yellow-500 text-white rounded-full text-xs font-bold">{{ $counts['pending'] }}</span>
                </a>
                <a href="{{ route('admin.refinitiv.index', ['status' => 'hadir']) }}" 
                   class="flex-1 flex flex-col items-center px-4 py-4 text-sm font-semibold border-b-3 {{ $status === 'hadir' ? 'border-green-500 text-green-700 bg-green-50' : 'border-transparent text-gray-500 hover:text-green-600' }}">
                    <div class="bg-green-100 p-2 rounded-lg mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="hidden md:inline">Hadir</span>
                    <span class="mt-1 px-2 py-0.5 bg-green-500 text-white rounded-full text-xs font-bold">{{ $counts['hadir'] }}</span>
                </a>
                <a href="{{ route('admin.refinitiv.index', ['status' => 'tidak_hadir']) }}" 
                   class="flex-1 flex flex-col items-center px-4 py-4 text-sm font-semibold border-b-3 {{ $status === 'tidak_hadir' ? 'border-red-500 text-red-700 bg-red-50' : 'border-transparent text-gray-500 hover:text-red-600' }}">
                    <div class="bg-red-100 p-2 rounded-lg mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="hidden md:inline">Tidak Hadir</span>
                    <span class="mt-1 px-2 py-0.5 bg-red-500 text-white rounded-full text-xs font-bold">{{ $counts['tidak_hadir'] }}</span>
                </a>
            </nav>
        </div>

        <!-- Request List -->
        <div class="p-4 md:p-6">
            @forelse($requests as $req)
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-md hover:shadow-lg mb-4 p-4 border-l-4 
                    {{ $req->attendance_status === 'pending' ? 'border-yellow-500' : ($req->attendance_status === 'hadir' ? 'border-green-500' : 'border-red-500') }} transition-all">
                    
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg">
                                {{ $req->isLecturer() ? 'Dosen' : 'Mahasiswa' }}
                            </span>
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-lg">
                                {{ $req->affiliation_label }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $req->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-800">
                                {{ $req->usage_date->locale('id')->isoFormat('D MMM Y') }}
                            </span>
                            <span class="block text-xs text-blue-600 font-medium">
                                {{ \App\Models\RefinitivRequest::SESSIONS[$req->session] ?? $req->session }}
                            </span>
                        </div>
                    </div>

                    <!-- Name & Info -->
                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $req->name }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-gray-600 mb-4">
                        <div>
                            <span class="text-gray-400">{{ $req->isLecturer() ? 'NIP' : 'NIM' }}:</span>
                            <span class="font-medium">{{ $req->nim_nip }}</span>
                        </div>
                        @if($req->study_program)
                        <div>
                            <span class="text-gray-400">Prodi:</span>
                            <span class="font-medium">{{ $req->study_program }}</span>
                        </div>
                        @endif
                        <div>
                            <span class="text-gray-400">Keperluan:</span>
                            <span class="font-medium">{{ $req->purpose_label }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">WA:</span>
                            <span class="font-medium">{{ $req->whatsapp }}</span>
                        </div>
                    </div>

                    <!-- Variabel Preview -->
                    <div class="bg-white rounded-lg p-3 mb-4 text-sm">
                        <span class="text-gray-500">Variabel:</span>
                        <span class="text-gray-700">{{ Str::limit($req->variables, 100) }}</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.refinitiv.show', $req) }}" 
                           class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-all flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Detail
                        </a>
                        
                        @if($req->attendance_status === 'pending')
                            <form action="{{ route('admin.refinitiv.hadir', $req) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Hadir
                                </button>
                            </form>
                            <form action="{{ route('admin.refinitiv.tidak-hadir', $req) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tidak Hadir
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.refinitiv.reset', $req) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="w-full px-4 py-2.5 bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg transition-all flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reset Status
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-gray-50 rounded-xl p-8 md:p-16 text-center">
                    <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Tidak Ada Permintaan</h3>
                    <p class="text-sm text-gray-600">
                        @if($status === 'pending')
                            Tidak ada permintaan yang menunggu konfirmasi kehadiran.
                        @elseif($status === 'hadir')
                            Tidak ada pemohon yang tercatat hadir.
                        @else
                            Tidak ada pemohon yang tercatat tidak hadir.
                        @endif
                    </p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="mt-4">
                    {{ $requests->appends(['status' => $status])->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
