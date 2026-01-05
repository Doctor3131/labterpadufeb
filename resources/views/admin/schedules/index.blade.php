<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kelola Jadwal - Lab Terpadu FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 border-b-4 border-yellow-500">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-gray-800">Kelola Jadwal</span>
                        <p class="text-xs text-gray-500">Lab Terpadu FEB UNDIP</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg font-medium text-sm md:text-base">
                        ← <span class="hidden sm:inline">Kembali ke </span>Dashboard
                    </a>
                </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Jadwal</h1>
                <p class="text-gray-600">Kelola semua jadwal laboratorium</p>
            </div>
            <a href="{{ route('admin.schedules.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all text-sm md:text-base whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Jadwal
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lab</label>
                    <select name="lab_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                        <option value="">Semua Lab</option>
                        @foreach($labs as $lab)
                            <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hari</label>
                    <select name="day" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                        <option value="">Semua Hari</option>
                        @foreach($days as $day)
                            <option value="{{ $day }}" {{ request('day') == $day ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                        <option value="">Semua Tipe</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg">
                        Filter
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Schedule Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
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
                                    {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $schedule->course }}</div>
                                    @if($schedule->komting)
                                        <div class="text-sm text-gray-500">Komting: {{ $schedule->komting }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $schedule->lecturer ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $typeColors = [
                                            'regular' => 'bg-gray-100 text-gray-800',
                                            'perkuliahan_tetap' => 'bg-yellow-100 text-yellow-800',
                                            'perkuliahan_tidak_tetap' => 'bg-indigo-100 text-indigo-800',
                                            'non_perkuliahan' => 'bg-emerald-100 text-emerald-800',
                                            'pribadi' => 'bg-orange-100 text-orange-800',
                                            // Legacy types (for old data)
                                            'booking_recurring' => 'bg-yellow-100 text-yellow-800',
                                            'booking_onetime' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $typeLabels = [
                                            'regular' => 'Regular',
                                            'perkuliahan_tetap' => 'Tetap',
                                            'perkuliahan_tidak_tetap' => 'Tidak Tetap',
                                            'non_perkuliahan' => 'Non Kuliah',
                                            'pribadi' => 'Pribadi',
                                            // Legacy types (for old data)
                                            'booking_recurring' => 'Tetap (Lama)',
                                            'booking_onetime' => 'Sekali (Lama)',
                                        ];
                                    @endphp
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
        </div>

        <!-- Summary -->
        <div class="mt-6 text-sm text-gray-500">
            Total: {{ $schedules->count() }} jadwal
        </div>
    </div>
</body>
</html>
