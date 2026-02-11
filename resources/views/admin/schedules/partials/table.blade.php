{{-- Schedule Table Partial - For AJAX filtering --}}
@php
    $typeColors = [
        'perkuliahan_tetap' => 'bg-yellow-100 text-yellow-800',
        'perkuliahan_tidak_tetap' => 'bg-indigo-100 text-indigo-800',
        'non_perkuliahan' => 'bg-emerald-100 text-emerald-800',
        'pribadi' => 'bg-orange-100 text-orange-800',
        'booking_recurring' => 'bg-yellow-100 text-yellow-800',
        'booking_onetime' => 'bg-gray-100 text-gray-800',
    ];
    $typeLabels = [
        'perkuliahan_tetap' => 'Tetap',
        'perkuliahan_tidak_tetap' => 'Tidak Tetap',
        'non_perkuliahan' => 'Non Kuliah',
        'pribadi' => 'Pribadi',
        'booking_recurring' => 'Tetap (Lama)',
        'booking_onetime' => 'Sekali (Lama)',
    ];
@endphp

{{-- Desktop Table View --}}
<div class="hidden md:block overflow-x-auto" id="desktop-table">
    <table class="w-full">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Lab</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Hari</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Waktu</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase min-w-[200px]">Mata Kuliah / Kegiatan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase min-w-[150px]">Dosen / PIC</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Tipe</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($schedules as $schedule)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-medium text-gray-800">{{ $schedule->lab->name }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $schedule->day }}</td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $schedule->course }}</div>
                        @if($schedule->komting && in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']))
                            <div class="text-sm text-gray-500">Komting: {{ $schedule->komting }}</div>
                        @elseif($schedule->komting && in_array($schedule->type, ['non_perkuliahan', 'pribadi']))
                            <div class="text-sm text-gray-500">Peminjam: {{ $schedule->komting }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $schedule->lecturer ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $typeLabels[$schedule->type] ?? $schedule->type }}
                        </span>
                        @if($schedule->booking)
                            <span class="ml-1 text-xs text-gray-400" title="Dari Booking #{{ $schedule->booking_id }}">
                                (B)
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.schedules.print', $schedule->id) }}" target="_blank"
                               class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium">
                                Cetak
                            </a>
                            <a href="{{ route('admin.schedules.edit', $schedule->id) }}" 
                               class="px-3 py-1 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-sm font-medium">
                                Edit
                            </a>
                            <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus jadwal ini?{{ $schedule->booking ? ' Booking terkait akan ditandai sebagai Deleted.' : '' }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tidak ada jadwal ditemukan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Mobile Card View --}}
<div class="md:hidden divide-y divide-gray-200" id="mobile-cards">
    @forelse($schedules as $schedule)
        <div class="p-4 hover:bg-gray-50">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 text-base mb-1">{{ $schedule->course }}</h3>
                    <div class="flex flex-wrap gap-2 items-center text-sm text-gray-600 mb-2">
                        <span class="font-medium">{{ $schedule->lab->name }}</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ $schedule->day }}</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</span>
                    </div>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap ml-2 {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $typeLabels[$schedule->type] ?? $schedule->type }}
                </span>
            </div>
            
            @if($schedule->lecturer)
                <div class="text-sm text-gray-600 mb-2">
                    <span class="font-medium">Dosen/PIC:</span> {{ $schedule->lecturer }}
                </div>
            @endif
            
            @if($schedule->komting && in_array($schedule->type, ['perkuliahan_tetap', 'perkuliahan_tidak_tetap']))
                <div class="text-sm text-gray-600 mb-3">
                    <span class="font-medium">Komting:</span> {{ $schedule->komting }}
                </div>
            @elseif($schedule->komting && in_array($schedule->type, ['non_perkuliahan', 'pribadi']))
                <div class="text-sm text-gray-600 mb-3">
                    <span class="font-medium">Peminjam:</span> {{ $schedule->komting }}
                </div>
            @endif
            
            <div class="flex gap-2 mt-3">
                <a href="{{ route('admin.schedules.print', $schedule->id) }}" target="_blank"
                   class="flex-1 px-4 py-2.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium text-center">
                    Cetak
                </a>
                <a href="{{ route('admin.schedules.edit', $schedule->id) }}" 
                   class="flex-1 px-4 py-2.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-sm font-medium text-center">
                    Edit
                </a>
                <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST" class="flex-1"
                      onsubmit="return confirm('Yakin ingin menghapus jadwal ini?{{ $schedule->booking ? ' Booking terkait akan ditandai sebagai Deleted.' : '' }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Tidak ada jadwal ditemukan
        </div>
    @endforelse
</div>

{{-- Load More Button --}}
@if($schedules->hasMorePages())
    <div class="p-4 text-center bg-white border-t border-gray-100" id="load-more-container">
        <button type="button" 
                id="btn-load-more" 
                data-next-url="{{ $schedules->nextPageUrl() }}"
                class="px-6 py-2.5 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 font-semibold rounded-full border border-yellow-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 flex items-center justify-center mx-auto space-x-2">
            <span>Tampilkan Lebih Banyak</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>
@endif

{{-- Total Count --}}
<div class="px-4 py-3 bg-gray-50 border-t text-sm text-gray-500 flex justify-between items-center" id="schedule-count">
    <span>
        Menampilkan <span class="font-medium text-gray-700">{{ $schedules->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-700">{{ $schedules->lastItem() ?? 0 }}</span> 
        dari <span class="font-medium text-gray-700">{{ $schedules->total() }}</span> jadwal
    </span>
    <span class="text-xs text-gray-400">
        Halaman {{ $schedules->currentPage() }} dari {{ $schedules->lastPage() }}
    </span>
</div>
