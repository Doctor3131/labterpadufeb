<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Berhasil - Refinitiv FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-2xl w-full">
        <!-- Success Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Permohonan Berhasil Dikirim!</h1>
                <p class="text-green-100">Permohonan akses data Refinitiv Anda telah kami terima</p>
            </div>

            <!-- Content -->
            <div class="p-6 lg:p-8">
                <!-- Request Summary -->
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
                    <h2 class="font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Ringkasan Permohonan
                    </h2>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Nama</span>
                            <span class="font-medium text-gray-800">{{ $request->name }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">{{ $request->isLecturer() ? 'NIP' : 'NIM' }}</span>
                            <span class="font-medium text-gray-800">{{ $request->nim_nip }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Status</span>
                            <span class="font-medium text-gray-800">{{ $request->isLecturer() ? 'Dosen' : 'Mahasiswa' }}</span>
                        </div>
                        @if($request->study_program)
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Program Studi</span>
                            <span class="font-medium text-gray-800">{{ $request->study_program }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Keperluan</span>
                            <span class="font-medium text-gray-800">{{ $request->purpose_label }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Tanggal Pemakaian</span>
                            <span class="font-medium text-gray-800">{{ $request->usage_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">Sesi</span>
                            <span class="font-medium text-gray-800">{{ $request->session_label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                    <h3 class="font-bold text-yellow-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Langkah Selanjutnya
                    </h3>
                    <div class="text-sm text-yellow-700 space-y-2">
                        <p>Silahkan langsung menuju ke lokasi berikut pada <strong>tanggal dan sesi yang telah Anda pilih</strong>:</p>
                        <div class="bg-white rounded-lg p-4 mt-3">
                            <p class="font-bold text-gray-800">📍 Gedung Laboratorium FEB-UNDIP</p>
                            <p class="text-gray-600">Lantai 3, <strong>Ruangan Digilib</strong></p>
                        </div>
                        <p class="mt-3">Lakukan konfirmasi kepada asisten lab untuk diarahkan lebih lanjut.</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('landing') }}" 
                       class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-center flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Kembali ke Beranda
                    </a>
                    <a href="{{ route('data.index') }}" 
                       class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors text-center flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajukan Permohonan Lain
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-6">
            &copy; {{ date('Y') }} Lab Digital FEB UNDIP
        </p>
    </div>

</body>
</html>
