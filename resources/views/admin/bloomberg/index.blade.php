@extends('layouts.admin')

@section('title', 'Kelola Reservasi Bloomberg - Admin')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl p-4 md:p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Reservasi Bloomberg</h1>
                    <p class="text-sm text-indigo-100">Kelola reservasi dan kunjungan Bloomberg Terminal</p>
                </div>
                <div class="flex items-center gap-2 md:gap-3">
                    <a href="{{ route('admin.bloomberg.blocked-dates') }}" 
                       class="inline-flex items-center p-2.5 md:px-4 md:py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-semibold rounded-xl transition-all"
                       title="Tanggal Blokir">
                        <svg class="w-4 h-4 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="hidden md:inline">Tanggal Blokir</span>
                    </a>
                    <a href="{{ route('admin.bloomberg.settings') }}" 
                       class="inline-flex items-center p-2.5 md:px-4 md:py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-semibold rounded-xl transition-all"
                       title="Pengaturan">
                        <svg class="w-4 h-4 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="hidden md:inline">Pengaturan</span>
                    </a>
                    <div class="hidden md:block bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
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

    <!-- Tabs + Request List -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
        <div class="border-b border-gray-100">
            <nav class="flex">
                <a href="{{ route('admin.bloomberg.index', ['type' => 'reservasi']) }}" 
                   class="flex-1 flex flex-col items-center px-4 py-4 text-sm font-semibold border-b-3 {{ $type === 'reservasi' ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-indigo-600' }}">
                    <div class="bg-indigo-100 p-2 rounded-lg mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="hidden md:inline">Reservasi</span>
                    <span class="mt-1 px-2 py-0.5 bg-indigo-500 text-white rounded-full text-xs font-bold">{{ $counts['reservasi'] }}</span>
                </a>
                <a href="{{ route('admin.bloomberg.index', ['type' => 'walk_in']) }}" 
                   class="flex-1 flex flex-col items-center px-4 py-4 text-sm font-semibold border-b-3 {{ $type === 'walk_in' ? 'border-orange-500 text-orange-700 bg-orange-50' : 'border-transparent text-gray-500 hover:text-orange-600' }}">
                    <div class="bg-orange-100 p-2 rounded-lg mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="hidden md:inline">Kunjungan Langsung</span>
                    <span class="mt-1 px-2 py-0.5 bg-orange-500 text-white rounded-full text-xs font-bold">{{ $counts['walk_in'] }}</span>
                </a>
                <a href="{{ route('admin.bloomberg.index', ['type' => 'all']) }}" 
                   class="flex-1 flex flex-col items-center px-4 py-4 text-sm font-semibold border-b-3 {{ $type === 'all' ? 'border-gray-500 text-gray-700 bg-gray-50' : 'border-transparent text-gray-500 hover:text-gray-600' }}">
                    <div class="bg-gray-100 p-2 rounded-lg mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <span class="hidden md:inline">Semua</span>
                    <span class="mt-1 px-2 py-0.5 bg-gray-500 text-white rounded-full text-xs font-bold">{{ $counts['all'] }}</span>
                </a>
            </nav>
        </div>

        <!-- Request List -->
        <div class="p-4 md:p-6">
            @forelse($requests as $req)
                <div class="bg-gradient-to-br from-white to-indigo-50 rounded-xl shadow-md hover:shadow-lg mb-4 p-4 border-l-4 
                    {{ $req->type === 'walk_in' ? 'border-orange-500' : 'border-indigo-500' }} transition-all">
                    
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 {{ $req->type === 'walk_in' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800' }} text-xs font-semibold rounded-lg">
                                {{ $req->type_label }}
                            </span>
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-lg">
                                {{ $req->applicant_type_label }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $req->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-800">
                                {{ $req->usage_date->locale('id')->isoFormat('D MMM Y') }}
                            </span>
                            <span class="block text-xs text-indigo-600 font-medium">
                                {{ $req->session_label }}
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
                            <span class="text-gray-400">HP:</span>
                            <span class="font-medium">{{ $req->phone }}</span>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.bloomberg.show', $req) }}" 
                           class="inline-flex px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-gray-50 rounded-xl p-8 md:p-16 text-center">
                    <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Tidak Ada Data</h3>
                    <p class="text-sm text-gray-600">
                        @if($type === 'reservasi')
                            Belum ada reservasi Bloomberg.
                        @elseif($type === 'walk_in')
                            Belum ada kunjungan langsung Bloomberg.
                        @else
                            Belum ada data Bloomberg.
                        @endif
                    </p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="mt-4">
                    {{ $requests->appends(['type' => $type])->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
