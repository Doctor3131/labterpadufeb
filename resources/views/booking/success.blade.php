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
                <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                    Login Asisten Lab
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-12 max-w-3xl">
        <!-- Success Card -->
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <!-- Success Icon -->
            <div class="mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-3">Peminjaman Berhasil Diajukan!</h1>
            <p class="text-gray-600 mb-8">Permintaan peminjaman Anda telah diterima dan menunggu persetujuan dari Asisten Lab</p>

            <!-- Booking Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
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
                            @else
                                Non-Perkuliahan
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Nama Peminjam:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->nama_peminjam }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Laboratorium:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->lab->name }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->tanggal->format('d F Y') }} ({{ $booking->day }})</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Waktu:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->start_time }} - {{ $booking->end_time }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Jumlah Peserta:</span>
                        <span class="font-semibold text-gray-800">{{ $booking->jumlah_peserta }} orang</span>
                    </div>

                    @if($booking->isPerkuliahan())
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Mata Kuliah:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->mata_kuliah }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Dosen:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->dosen_pengampu }}</span>
                        </div>
                    @endif

                    @if($booking->isNonPerkuliahan())
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Jenis Kegiatan:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->jenis_kegiatan }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Nama Kegiatan:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->nama_kegiatan }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            ⏱ Menunggu Persetujuan
                        </span>
                    </div>
                </div>
            </div>

            <!-- Information Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 text-left">
                <h4 class="font-bold text-blue-800 mb-2">ℹ️ Informasi Penting</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Permintaan Anda akan diproses oleh Asisten Lab</li>
                    <li>• Anda akan dihubungi melalui nomor telepon yang terdaftar</li>
                    <li>• Mohon menunggu konfirmasi sebelum menggunakan laboratorium</li>
                    <li>• Jika ada pertanyaan, hubungi administrasi Lab Terpadu FEB UNDIP</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing') }}" 
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                    Kembali ke Beranda
                </a>
                <a href="{{ route('booking.create') }}" 
                    class="bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 px-8 py-3 rounded-lg font-semibold transition-all">
                    Ajukan Peminjaman Lagi
                </a>
            </div>
        </div>
    </div>
</body>
</html>
