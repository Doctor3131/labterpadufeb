<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Berhasil - Lab Terpadu FEB UNDIP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl font-bold text-yellow-600">Lab Terpadu</span>
                    <span class="text-xl text-gray-700">FEB UNDIP</span>
                </div>
                <a href="{{ route('landing') }}" class="text-gray-600 hover:text-gray-800 font-medium">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-12 max-w-3xl">
        <!-- Success Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Success Icon -->
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-3">Peminjaman Berhasil Diajukan!</h1>
                <p class="text-gray-600 mb-2">Dokumen peminjaman Anda sudah siap.</p>
                <p class="text-sm text-gray-500">Silakan download dan bawa ke kantor Lab Terpadu FEB untuk konfirmasi.</p>
            </div>

            <!-- Download PDF Button - Prominent -->
            <div class="mb-8">
                <a href="{{ route('booking.print', $booking->tracking_token) }}" 
                   target="_blank"
                   class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-8 py-4 rounded-xl font-bold text-lg transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Dokumen PDF
                </a>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
                <h3 class="font-bold text-blue-900 text-lg mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Langkah Selanjutnya
                </h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-blue-900">
                    <li><strong>Download</strong> dokumen PDF di atas</li>
                    <li><strong>Bawa</strong> dokumen ke <strong>Kantor Lab Terpadu FEB</strong></li>
                    <li><strong>Minta tanda tangan</strong> untuk konfirmasi peminjaman</li>
                </ol>
            </div>

            <!-- Booking Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Detail Peminjaman</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">ID Booking:</span>
                        <span class="font-semibold text-gray-800">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Tipe:</span>
                        <span class="font-semibold text-gray-800">
                            @if($booking->booking_type === 'perkuliahan_tetap')
                                Perkuliahan Tetap
                            @elseif($booking->booking_type === 'perkuliahan_tidak_tetap')
                                Perkuliahan Tidak Tetap
                            @elseif($booking->booking_type === 'pribadi')
                                Pribadi
                            @else
                                Non-Perkuliahan
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Nama Peminjam:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->pic_name }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Laboratorium:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->lab->name }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->booking_date->format('d F Y') }} ({{ $booking->day }})</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Waktu:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->start_time }} - {{ $booking->end_time }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Jumlah Peserta:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->participant_count }} orang</span>
                    </div>

                    @if($booking->isPerkuliahan())
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Mata Kuliah:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->course_name }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Dosen:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->lecturer_name }}</span>
                        </div>
                    @endif

                    @if($booking->isNonPerkuliahan())
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Jenis Kegiatan:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->activity_type }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Nama Kegiatan:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->activity_name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Save Token -->
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                <p class="text-sm text-yellow-800 mb-2">
                    <strong>💾 Simpan kode ini</strong> untuk download ulang dokumen kapan saja:
                </p>
                <div class="bg-white rounded border-2 border-yellow-400 p-3 flex items-center justify-between">
                    <code class="text-lg font-mono font-bold text-gray-800">{{ $booking->tracking_token }}</code>
                    <button onclick="copyToken()" 
                            id="copyTokenBtn"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-semibold text-sm transition-colors">
                        Salin
                    </button>
                </div>
                <p class="text-xs text-yellow-700 mt-2">
                    Akses kembali melalui beranda → masukkan kode ini
                </p>
            </div>
        </div>
    </div>

    <script>
        function copyToken() {
            const token = '{{ $booking->tracking_token }}';
            navigator.clipboard.writeText(token).then(() => {
                const btn = document.getElementById('copyTokenBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '✓ Tersalin!';
                btn.classList.add('bg-green-500');
                btn.classList.remove('bg-yellow-500');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('bg-green-500');
                    btn.classList.add('bg-yellow-500');
                }, 2000);
            });
        }
    </script>
</body>
</html>
