@extends('layouts.admin')

@section('title', 'Kelola Tanggal Blokir Bloomberg - Admin')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.bloomberg.index') }}" class="inline-flex items-center gap-2 rounded-md text-sm font-medium text-slate-600 transition hover:text-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Reservasi Bloomberg
        </a>
    </div>

    <header class="mb-6">
        <p class="mb-1 text-sm font-semibold uppercase tracking-wide text-indigo-700">Layanan data</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tanggal blokir Bloomberg</h1>
        <p class="mt-1 text-sm text-slate-600">Atur hari atau sesi yang tidak tersedia untuk reservasi Bloomberg Terminal.</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Add Form -->
        <div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
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
                        <label for="blocked_session" class="block text-sm font-semibold text-gray-700 mb-2">Sesi yang Diblokir</label>
                        <select id="blocked_session" name="blocked_session"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <option value="" {{ old('blocked_session') === null ? 'selected' : '' }}>Semua Sesi (Blokir 1 hari penuh)</option>
                            <option value="sesi_1" {{ old('blocked_session') === 'sesi_1' ? 'selected' : '' }}>{{ $sessions['sesi_1'] }}</option>
                            <option value="sesi_2" {{ old('blocked_session') === 'sesi_2' ? 'selected' : '' }}>{{ $sessions['sesi_2'] }}</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih "Semua Sesi" untuk memblokir satu hari penuh, atau pilih sesi tertentu.</p>
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">Alasan <span class="text-red-500">*</span></label>
                        <input type="text" id="reason" name="reason" required
                               value="{{ old('reason') }}"
                               placeholder="Contoh: Pelatihan Bloomberg, Libur Natal"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        @error('reason')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Alasan ini akan ditampilkan kepada peminjam saat memilih tanggal.</p>
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
            <div class="mt-4 rounded-xl border border-indigo-200 bg-indigo-50 p-5">
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
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5">
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
                                        @if($blocked->blocked_session)
                                            <span class="inline-block px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-bold rounded mt-1">
                                                {{ ($blocked->blocked_date->isFriday() ? $sessionsFriday : $sessions)[$blocked->blocked_session] ?? $blocked->blocked_session }}
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded mt-1">
                                                Semua Sesi
                                            </span>
                                        @endif
                                        @if($blocked->reason)
                                            <p class="text-sm text-gray-500 mt-1">{{ $blocked->reason }}</p>
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
                    <div class="p-8 text-center">
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
