@extends('layouts.admin')

@section('title', 'Pengaturan Bloomberg - Admin')

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
                    <h1 class="text-xl md:text-2xl font-bold text-white mb-1">Pengaturan Bloomberg</h1>
                    <p class="text-sm text-indigo-100">Atur kapasitas dan form kunjungan langsung</p>
                </div>
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
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

    <form action="{{ route('admin.bloomberg.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="max-w-2xl">
            <!-- Capacity Setting -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
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
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
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
            <button type="submit" class="w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors flex items-center justify-center">
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
