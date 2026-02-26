<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kunjungan Langsung Ditutup - Bloomberg</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50 to-slate-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto px-6 text-center">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <!-- Icon -->
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-2xl mb-6">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-3">Form Kunjungan Langsung Ditutup</h1>
            <p class="text-gray-500 mb-6">
                Saat ini form kunjungan langsung Bloomberg tidak tersedia. 
                Silakan hubungi petugas lab atau gunakan form reservasi untuk melakukan peminjaman.
            </p>

            <div class="space-y-3">
                <a href="{{ route('bloomberg.create') }}" 
                   class="block w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                    Buat Reservasi
                </a>
                <a href="{{ route('data.index') }}" 
                   class="block w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</body>
</html>
