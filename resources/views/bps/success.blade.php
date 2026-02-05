<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Berhasil - Lab Digital FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <!-- Success Icon -->
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3">Permohonan Berhasil!</h1>
            <p class="text-gray-600 mb-6">Permohonan data BPS Anda telah berhasil diajukan dan sedang dalam proses verifikasi.</p>

            <!-- Request Info -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                <h3 class="font-semibold text-gray-800 mb-3">Detail Permohonan:</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><span class="font-medium">Nama:</span> {{ $bpsRequest->name }}</p>
                    <p><span class="font-medium">Email:</span> {{ $bpsRequest->email }}</p>
                    <p><span class="font-medium">{{ $bpsRequest->applicant_type === 'mahasiswa' ? 'NIM' : 'NIP' }}:</span> {{ $bpsRequest->identifier }}</p>
                    <p><span class="font-medium">Keperluan:</span> {{ $bpsRequest->display_purpose }}</p>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 text-left">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">Langkah Selanjutnya:</p>
                        <ul class="list-disc list-inside space-y-1 text-yellow-700">
                            <li>Data akan dikirimkan ke email <strong>{{ $bpsRequest->email }}</strong> dalam 1-3 hari kerja</li>
                            <li>Data akan dikirim dalam bentuk link Google Drive</li>
                            <li>Segera download data, karena link akan expired dalam 3 hari</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <a href="{{ route('landing') }}" class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
