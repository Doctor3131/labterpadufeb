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
            <p class="text-gray-500 mb-4">
                Saat ini form kunjungan langsung Bloomberg tidak tersedia. 
                Silakan hubungi admin untuk mengisi form kunjungan langsung atau gunakan form reservasi.
            </p>
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6 text-center">
                <p class="text-sm font-semibold text-gray-700 mb-1">Hubungi Admin Bloomberg</p>
                <a href="https://wa.me/6285155266697" target="_blank" class="inline-flex items-center gap-2 text-green-600 hover:text-green-800 font-semibold underline">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.136.558 4.142 1.535 5.886L.06 23.69l5.93-1.458A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.96 0-3.83-.508-5.47-1.475l-.393-.234-3.515.864.892-3.428-.256-.407A9.95 9.95 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/>
                    </svg>
                    085155266697
                </a>
            </div>

            <div class="space-y-3">
                <a href="{{ route('bloomberg.index') }}" 
                   class="block w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                    Buat Reservasi
                </a>
                <a href="{{ route('landing') }}" 
                   class="block w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-colors">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</body>
</html>
