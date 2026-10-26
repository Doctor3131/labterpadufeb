@extends('layouts.admin')

@section('title', 'Pengaturan Bloomberg - Admin')

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
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengaturan Bloomberg</h1>
        <p class="mt-1 text-sm text-slate-600">Atur kapasitas sesi dan ketersediaan kunjungan langsung.</p>
    </header>

    <form action="{{ route('admin.bloomberg.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="max-w-2xl">
            <!-- Capacity Setting -->
            <div class="mb-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-gray-800 mb-1 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Kapasitas per Sesi
                </h2>
                <p class="text-sm text-gray-500 mb-4">Jumlah maksimal reservasi yang bisa dilakukan per sesi per hari.</p>

                <div class="flex items-center gap-4">
                    <input type="number" name="capacity_per_session" id="capacity_per_session"
                           value="{{ old('capacity_per_session', $capacity) }}"
                           min="1" max="100" required
                           class="w-32 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-center text-lg font-bold">
                    <span class="text-gray-500 text-sm">orang per sesi</span>
                </div>
                @error('capacity_per_session')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Walk-in Toggle -->
            <div class="mb-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-gray-800 mb-1 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Form Kunjungan Langsung
                </h2>
                <p class="text-sm text-gray-500 mb-4">Buka/tutup form kunjungan langsung (walk-in) untuk pengunjung di lab.</p>

                <div class="flex items-center gap-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="walk_in_enabled" value="0">
                        <input type="checkbox" name="walk_in_enabled" value="1" 
                               class="sr-only peer" {{ $walkInEnabled ? 'checked' : '' }}>
                        <div class="w-14 h-7 bg-gray-300 peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                    <div>
                        <span class="font-semibold text-gray-700" id="walk-in-status">
                            {{ $walkInEnabled ? 'Dibuka' : 'Ditutup' }}
                        </span>
                        <p class="text-xs text-gray-400">
                            {{ $walkInEnabled ? 'Pengunjung bisa mengisi form kunjungan langsung' : 'Form kunjungan langsung tidak dapat diakses' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Pengaturan
            </button>
        </div>
    </form>

    <script>
        // Update walk-in status text when toggled
        const checkbox = document.querySelector('input[type="checkbox"][name="walk_in_enabled"]');
        const statusText = document.getElementById('walk-in-status');
        if (checkbox && statusText) {
            checkbox.addEventListener('change', function() {
                statusText.textContent = this.checked ? 'Dibuka' : 'Ditutup';
                statusText.parentElement.querySelector('p').textContent = this.checked 
                    ? 'Pengunjung bisa mengisi form kunjungan langsung' 
                    : 'Form kunjungan langsung tidak dapat diakses';
            });
        }
    </script>
@endsection
