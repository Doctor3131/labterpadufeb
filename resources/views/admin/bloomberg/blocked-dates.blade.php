@extends('layouts.admin')

@section('title', 'Kelola Tanggal Blokir Bloomberg - Admin')

@section('content')
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.bloomberg.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Reservasi Bloomberg
        </a>
    </div>

    <!-- Header -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-2xl p-4 md:p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Tanggal Blokir Bloomberg</h1>
                    <p class="text-sm text-indigo-100">Kelola tanggal yang tidak tersedia untuk reservasi Bloomberg Terminal</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
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

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Add Form -->
        <div>
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Tanggal Blokir
                </h2>
                
                <form action="{{ route('admin.bloomberg.blocked-dates.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="blocked_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" id="blocked_date" name="blocked_date" required
                               min="{{ date('Y-m-d') }}"
                               value="{{ old('blocked_date') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        @error('blocked_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">Alasan <span class="text-gray-400">(opsional)</span></label>
                        <input type="text" id="reason" name="reason"
                               value="{{ old('reason') }}"
                               placeholder="Contoh: Libur Natal, Cuti Bersama"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Blokir Tanggal
                    </button>
                </form>
            </div>

            <!-- Info Card -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 mt-6">
                <h3 class="font-bold text-indigo-900 mb-2 flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Informasi
                </h3>
                <div class="text-xs text-indigo-700 space-y-1">
                    <p>Tanggal yang diblokir <strong>tidak akan bisa dipilih</strong> oleh user pada form reservasi Bloomberg.</p>
                    <p>Gunakan fitur ini untuk memblokir hari libur nasional atau tanggal-tanggal tertentu yang tidak tersedia.</p>
                </div>
            </div>
        </div>

        <!-- Right: List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Daftar Tanggal Blokir
                        <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">{{ $blockedDates->count() }}</span>
                    </h2>
                </div>

                @if($blockedDates->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($blockedDates as $blocked)
                            <div class="p-4 md:px-6 flex items-center justify-between hover:bg-gray-50 transition-colors {{ $blocked->blocked_date->isPast() ? 'opacity-50' : '' }}">
                                <div class="flex items-center gap-4">
                                    <!-- Date Badge -->
                                    <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center min-w-[60px]">
                                        <span class="block text-xs text-red-500 font-medium">{{ $blocked->blocked_date->locale('id')->isoFormat('MMM') }}</span>
                                        <span class="block text-xl font-bold text-red-700">{{ $blocked->blocked_date->format('d') }}</span>
                                        <span class="block text-xs text-red-500">{{ $blocked->blocked_date->format('Y') }}</span>
                                    </div>
                                    
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ $blocked->blocked_date->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                        </p>
                                        @if($blocked->reason)
                                            <p class="text-sm text-gray-500">{{ $blocked->reason }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-1">
                                            Ditambahkan oleh {{ $blocked->creator->name ?? 'System' }} · {{ $blocked->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('admin.bloomberg.blocked-dates.destroy', $blocked) }}" method="POST" 
                                      onsubmit="return confirm('Hapus blokir tanggal {{ $blocked->blocked_date->locale('id')->isoFormat('D MMMM Y') }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 bg-red-50 text-red-600 text-sm font-semibold rounded-lg hover:bg-red-100 transition-colors flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 md:p-16 text-center">
                        <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Tanggal Blokir</h3>
                        <p class="text-sm text-gray-600">Semua tanggal tersedia untuk reservasi. Gunakan form di samping untuk memblokir tanggal tertentu.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
