@extends('layouts.admin')

@section('title', 'Kelola Ruang Lab - Lab Digital FEB UNDIP')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-600 hover:text-yellow-600 font-medium transition-all group">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Ruang Lab</h1>
            <p class="text-sm text-gray-600">Manajemen ruang laboratorium dan statusnya</p>
        </div>
        <a href="{{ route('admin.labs.create') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Ruang Lab
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-r-lg shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-r-lg shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Labs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($labs as $lab)
            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Lab Info -->
                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $lab->code }}</span>
                            <h3 class="font-bold text-gray-800 text-lg mt-1">{{ $lab->name }}</h3>
                        </div>
                        @php
                            $statusConfig = match($lab->status) {
                                'available' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Tersedia'],
                                'occupied' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Terpakai'],
                                'maintenance' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Nonaktif'],
                                default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Unknown'],
                            };
                        @endphp
                        <span class="{{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $lab->description ?: 'Tidak ada deskripsi' }}</p>

                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Kapasitas: {{ $lab->capacity }} orang</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span>Jadwal: {{ $lab->schedules_count }} | Booking: {{ $lab->bookings_count }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 pt-3 border-t border-gray-100">
                        <a href="{{ route('admin.labs.edit', $lab->id) }}" class="flex-1 inline-flex justify-center items-center px-3 py-2 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        
                        <form action="{{ route('admin.labs.toggle-status', $lab->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 {{ $lab->status === 'available' ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }} text-sm font-semibold rounded-lg transition-colors">
                                @if($lab->status === 'available')
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Nonaktifkan
                                @else
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Aktifkan
                                @endif
                            </button>
                        </form>

                        <form action="{{ route('admin.labs.destroy', $lab->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lab ini? Lab dengan jadwal/booking aktif tidak dapat dihapus.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex justify-center items-center px-3 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Belum ada ruang lab</h3>
                <p class="text-gray-500 mb-4">Mulai dengan menambahkan ruang lab baru</p>
                <a href="{{ route('admin.labs.create') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Ruang Lab
                </a>
            </div>
        @endforelse
    </div>
@endsection
