@extends('layouts.admin')

@section('title', 'Detail Permintaan BPS - Laboratorium dan Fasilitas Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.bps.requests.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Permintaan
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $bpsRequest->name }}</h1>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 bg-{{ $bpsRequest->applicant_type === 'mahasiswa' ? 'purple' : 'indigo' }}-100 text-{{ $bpsRequest->applicant_type === 'mahasiswa' ? 'purple' : 'indigo' }}-800 text-sm font-semibold rounded-lg">
                        {{ ucfirst($bpsRequest->applicant_type) }}
                    </span>
                    <span class="px-3 py-1 bg-{{ $bpsRequest->status === 'completed' ? 'green' : 'blue' }}-100 text-{{ $bpsRequest->status === 'completed' ? 'green' : 'blue' }}-800 text-sm font-semibold rounded-lg">
                        {{ $bpsRequest->status === 'completed' ? '✓ Selesai' : 'Menunggu' }}
                    </span>
                </div>
            </div>
            <div class="text-right text-sm text-gray-500">
                <p>Dibuat: {{ $bpsRequest->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if($bpsRequest->status === 'pending')
            <form action="{{ route('admin.bps.requests.complete', $bpsRequest) }}" method="POST" class="mt-4" onsubmit="return confirm('Tandai permintaan ini sebagai selesai?')">
                @csrf
                @method('PUT')
                <button type="submit" class="px-6 py-3 bg-green-500 text-white font-bold rounded-lg hover:bg-green-600 transition-colors">
                    ✓ Tandai Selesai (Data Sudah Dikirim)
                </button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Applicant Info -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Data Pemohon
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Nama</span>
                    <span class="font-medium text-gray-800">{{ $bpsRequest->name }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Email</span>
                    <span class="font-medium text-gray-800">{{ $bpsRequest->email }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">{{ $bpsRequest->applicant_type === 'mahasiswa' ? 'NIM' : 'NIP' }}</span>
                    <span class="font-medium text-gray-800">{{ $bpsRequest->identifier }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">WhatsApp</span>
                    <span class="font-medium text-gray-800">{{ $bpsRequest->phone }}</span>
                </div>
                @if($bpsRequest->study_program)
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Program Studi</span>
                    <span class="font-medium text-gray-800">{{ $bpsRequest->study_program }}</span>
                </div>
                @endif
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Keperluan</span>
                    <span class="font-medium text-gray-800">{{ $bpsRequest->display_purpose }}</span>
                </div>
                @if($bpsRequest->has_lecturer_collaboration)
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Kolaborasi Dosen</span>
                    <span class="font-medium text-gray-800">{{ $bpsRequest->collaborating_lecturer_name }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Documents -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Dokumen
            </h2>
            <div class="space-y-3">
                @if($bpsRequest->ktm_path)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                        </svg>
                        <span class="font-medium text-gray-700">KTM</span>
                    </div>
                    <a href="{{ Storage::url($bpsRequest->ktm_path) }}" target="_blank" class="px-3 py-1 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600">
                        Lihat
                    </a>
                </div>
                @endif
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="font-medium text-gray-700">Surat Pernyataan</span>
                    </div>
                    <a href="{{ Storage::url($bpsRequest->statement_letter_path) }}" target="_blank" class="px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600">
                        Lihat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Selected Datasets -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mt-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
            </svg>
            Dataset yang Diminta
        </h2>
        
        <div class="space-y-4">
            {{-- Sub-data based datasets --}}
            @foreach($bpsRequest->subData as $subData)
            <div class="border rounded-xl p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wide">{{ $subData->master->name }}</span>
                        <h4 class="font-semibold text-gray-800">{{ $subData->name }}</h4>
                    </div>
                </div>
                
                @php
                    $variable = $bpsRequest->variables->where('sub_data_id', $subData->id)->first();
                @endphp
                
                @if($variable && $variable->variables)
                <div class="mt-3 bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1">Kode Variabel:</p>
                    <p class="font-mono text-sm text-gray-800">{{ $variable->variables }}</p>
                </div>
                @endif
            </div>
            @endforeach

            {{-- Single-level master data (has_sub_data = false) --}}
            @foreach($bpsRequest->variables->whereNotNull('master_id') as $variable)
            <div class="border rounded-xl p-4 border-indigo-200 bg-indigo-50/30">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-xs text-indigo-600 uppercase tracking-wide font-semibold">Data Langsung</span>
                        <h4 class="font-semibold text-gray-800">{{ $variable->masterData->name ?? 'Unknown' }}</h4>
                    </div>
                </div>
                
                @if($variable->variables)
                <div class="mt-3 bg-white rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-1">Kode Variabel:</p>
                    <p class="font-mono text-sm text-gray-800">{{ $variable->variables }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    @if($bpsRequest->status === 'completed' && $bpsRequest->handler)
    <!-- Completion Info -->
    <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mt-6">
        <h2 class="text-lg font-bold text-green-800 mb-2">Informasi Penyelesaian</h2>
        <div class="text-sm text-green-700 space-y-1">
            <p><strong>Diselesaikan oleh:</strong> {{ $bpsRequest->handler->name }}</p>
            <p><strong>Tanggal:</strong> {{ $bpsRequest->completed_at->format('d F Y H:i') }}</p>
        </div>
    </div>
    @endif
@endsection
