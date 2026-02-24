@extends('layouts.admin')

@section('title', 'Pencatatan Peminjaman Pribadi - Laboratorium dan Fasilitas Digital FEB UNDIP')

@push('styles')
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .borrowing-card {
            animation: fadeInUp 0.4s ease-out;
        }
        .borrowing-card:nth-child(1) { animation-delay: 0.05s; }
        .borrowing-card:nth-child(2) { animation-delay: 0.1s; }
        .borrowing-card:nth-child(3) { animation-delay: 0.15s; }
        .borrowing-card:nth-child(4) { animation-delay: 0.2s; }
        .borrowing-card:nth-child(5) { animation-delay: 0.25s; }
    </style>
@endpush

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-600 hover:text-orange-600 mb-4 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Dashboard
        </a>
        <div class="bg-orange-500 rounded-2xl p-4 md:p-6 shadow-none">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Pencatatan Peminjaman Pribadi</h1>
                    <p class="text-xs md:text-sm text-orange-50">Catatan semua peminjaman pribadi yang tercatat otomatis</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-2 md:p-3 rounded-xl">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrowings List -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="p-4 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">Semua Peminjaman Pribadi</h2>
                <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-bold">{{ $borrowings->total() }} total</span>
            </div>

            @forelse($borrowings as $borrowing)
                <div class="borrowing-card bg-white border-2 border-gray-100 rounded-xl p-4 mb-4 hover:border-orange-200 transition-all">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            {{-- Sub-type badge --}}
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ ($borrowing->pribadi_sub_type === 'mahasiswa') ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ($borrowing->pribadi_sub_type === 'mahasiswa') ? 'Mahasiswa' : 'Non-Mahasiswa' }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">{{ $borrowing->pic_name ?? '-' }}</h3>
                        <div class="text-sm text-gray-600 mt-1 space-y-0.5">
                            @if($borrowing->pribadi_sub_type === 'mahasiswa')
                                <p><span class="font-medium">NIM:</span> {{ $borrowing->nim }}</p>
                                <p><span class="font-medium">Prodi:</span> {{ $borrowing->study_program ?? '-' }}</p>
                            @else
                                <p><span class="font-medium">NIP:</span> {{ $borrowing->nip ?? '-' }}</p>
                                <p><span class="font-medium">No. Telp:</span> {{ $borrowing->phone_number ?? '-' }}</p>
                            @endif
                            <p class="text-xs text-gray-400">Tercatat: {{ $borrowing->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 font-medium">Belum ada peminjaman pribadi</p>
                </div>
            @endforelse
            <div class="mt-4">{{ $borrowings->links() }}</div>
        </div>
    </div>
@endsection
